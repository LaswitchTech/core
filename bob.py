#!/usr/bin/env python3
# Bob v6 — repo-aware assistant (Ollama + local RAG) with lazy, sharded embeddings
# - Fast :reindex (no embeddings): scan files only; embed on demand per query
# - Per-file shards: ./tmp/bob_index/<sha1>.json (atomic)
# - Main registry small: ./tmp/bob_index.json
# - HTTP timeouts + CLI fallback for embeddings
# - Clear progress: "embedding N files..." during Q&A, not during reindex
# - Penalize *.cfg, boost src/lib/bootstrap/db/connection/ask.py
# - Streaming answers, history, doc generation
# - Safe readline on macOS libedit

import os, re, sys, json, math, time, glob, hashlib, argparse, subprocess, unicodedata, atexit
from typing import List, Dict, Any, Tuple
import requests, readline

# ------------------ Defaults ------------------
DEFAULT_OLLAMA_HOST = os.environ.get("OLLAMA_HOST", "http://localhost:11434")
DEFAULT_MODEL = os.environ.get("BOB_MODEL", "codellama:34b")
DEFAULT_EMBED_MODEL = os.environ.get("BOB_EMBED_MODEL", "nomic-embed-text")
ENABLE_DELIBERATION = os.environ.get("BOB_THINK", "1") not in ("0","false","False")  # on by default

# ANSI / color config
USE_COLOR = os.environ.get("NO_COLOR") is None
THINK_COLOR = os.environ.get("BOB_THINK_COLOR", "\033[96m")  # light cyan
RESET_COLOR = "\033[0m"

# Timeouts (seconds)
CHAT_TIMEOUT  = float(os.environ.get("BOB_CHAT_TIMEOUT", "300"))
EMBED_TIMEOUT = float(os.environ.get("BOB_EMBED_TIMEOUT", "30"))

# Speaker label (easy to customize)
BOB_SPEAKER = "👷 Bob"

def repo_root() -> str:
    return os.path.abspath(".")

def project_name() -> str:
    return os.path.basename(repo_root())

# Index & state under ./tmp
TMP_DIR = os.path.join(repo_root(), "tmp")
os.makedirs(TMP_DIR, exist_ok=True)
INDEX_REG_PATH = os.path.join(TMP_DIR, "bob_index.json")          # small registry
SHARD_DIR      = os.path.join(TMP_DIR, "bob_index")                # vectors per file
os.makedirs(SHARD_DIR, exist_ok=True)
HIST_PATH  = os.path.join(TMP_DIR, ".bob_history.json")
MEMO_PATH  = os.path.join(TMP_DIR, "bob_memory.json")
SYMBOLS_PATH = os.path.join(TMP_DIR, "bob_symbols.json")           # NEW: symbol index
MAX_HISTORY_TURNS = 8

# File size guard (skip huge files)
MAX_FILE_MB = float(os.environ.get("BOB_MAX_FILE_MB", "1.5"))
MAX_FILE_BYTES = int(MAX_FILE_MB * 1024 * 1024)

# Files to include/exclude (includes .cfg as text)
INCLUDE_GLOBS = [
    "src/**/*", "lib/**/*", "assets/**/*",
    "**/*.php", "**/*.phtml", "**/*.twig",
    "**/*.py", "**/*.js", "**/*.ts", "**/*.tsx", "**/*.jsx",
    "**/*.md", "**/*.txt", "**/*.json", "**/*.yaml", "**/*.yml", "**/*.ini", "**/*.cfg",
    "**/*.sql", "**/*.html", "**/*.css", "**/*.scss",
    "**/*.go", "**/*.rb", "**/*.rs", "**/*.java", "**/*.c", "**/*.cpp", "**/*.h", "**/*.hpp",
]
EXCLUDE_REGEXES = [
    r"/\.", r"node_modules/", r"vendor/", r"build/", r"dist/", r"coverage/",
    r"(?:^|/)public/", r"__pycache__/",
    r"(?:^|/)tmp/(?!bob_index(?:\.json)?$)",
    r"(?:^|/)cache/", r"assets/js/.*\.min\.js$", r"\.min\.",
    r"\.(png|jpg|jpeg|gif|ico|pdf|zip|tar|gz|7z|bin|wasm|ico)$",
]
DISALLOWED_WHEN_NOT_IN_SOURCES = [
    "ask.com", "Ask.com", "search engine", "search results", "api key",
    "Google", "Bing", "DuckDuckGo", "Stack Overflow", "stackoverflow.com",
    "Wikipedia", "Twitter", "Reddit", "Hugging Face", "OpenAI API"
]

# Chunking
CHUNK_LINES = 120
CHUNK_OVERLAP = 30
MAX_CHUNKS_PER_FILE = int(os.environ.get("BOB_MAX_CHUNKS_PER_FILE", "64"))

# Retrieval
TOP_K_DEFAULT = 6
ALPHA_KEYWORD = 0.25
MMR_LAMBDA = 0.7
MAX_FILES_TO_EMBED_PER_QUERY = int(os.environ.get("BOB_MAX_FILES_PER_QUERY", "30"))

# Filename/dir boosts
DIR_BOOSTS = [
    (re.compile(r"^src/"), 0.15),
    (re.compile(r"^lib/"), 0.10),
]
NAME_BOOSTS = [
    (re.compile(r"(^|/)ask\.py$", re.I), 0.35),
    (re.compile(r"bootstrap", re.I), 0.15),
    (re.compile(r"(database|db|connection|pdo)", re.I), 0.45),
    (re.compile(r"(connector|drivers?)", re.I), 0.20),
    (re.compile(r"(init|initialize|bootstrap\.php$)", re.I), 0.10),
]
NAME_PENALTIES = [
    (re.compile(r"/info\.cfg$", re.I), -0.40),
    (re.compile(r"/.+\.cfg$", re.I),   -0.20),
    (re.compile(r"/assets?/|/public/|/dist/|/build/", re.I), -0.25),
]

# Persona / guardrails (friendlier tone; still strict about sources)
def SYSTEM_INSTRUCTIONS(repo: str, thinking_on: bool) -> str:
    extra = "" if thinking_on else (
        "\nChain-of-thought policy: Do NOT include <think> sections or hidden reasoning. "
        "Provide only helpful, grounded answers with citations."
    )
    return f"""You are Bob, a senior software engineering assistant embedded in the codebase "{repo}".
Rules:
- Be concise and technical; you can be a little friendly and lightly humorous, but never at the cost of clarity.
- Use ONLY the provided repo chunks ([S#]) for repo-specific claims and CITE them (e.g., [S1]).
- If the answer is not present, say: "I don't know based on the current context." Then suggest exact files/terms to inspect.
- Prefer step-by-step guidance and short code snippets. You may quote the user's repository freely.
- When the user says "this project/framework/core", they mean "{repo}".
- Do NOT invent files, classes, methods, or APIs that aren't in the sources.
- Never reference external websites, search engines, or public APIs unless they appear in the provided repo chunks for this answer.
- If you cannot cite at least one [S#], reply: "I don't know based on the current context." and list which files to open next.
Style:
- Direct, developer-facing. Sprinkle a tiny bit of personality when appropriate (dry humor welcomed).
- When you draw from context, reference file paths and line ranges via [S#].
- If the user asks about a specific file (e.g., ask.py), explicitly reference that file by name and cite at least one [S#].{extra}
"""

HELP = """Commands:
  :help                 Show this help
  :reindex              Fast scan (no embeddings); refresh file list & signatures
  :sources              Show sources from the last answer
  :k <num>              Set top-k retrieval (current session)
  :model <m>            Switch LLM model (current session)
  :mkdir <path>         Create a folder inside the repository
  :doc "<Title>"        Generate Documentation/<Title>.md using repo context
  :write <path>         Save Bob's last answer to <path> (inside repo)
  :think                Toggle hidden planning/self-ask and model chain-of-thought display (default: on)
  :quit                 Exit
"""

# ------------------ IO helpers ------------------
def safe_relpath(p: str) -> str:
    rp = os.path.normpath(os.path.join(repo_root(), p))
    if not rp.startswith(repo_root()):
        raise ValueError("Path escapes repository root")
    return rp

def mkparent(path: str):
    os.makedirs(os.path.dirname(path), exist_ok=True)

def slugify(title: str) -> str:
    s = unicodedata.normalize("NFKD", title).encode("ascii", "ignore").decode("ascii")
    s = re.sub(r"[^a-zA-Z0-9]+", "-", s).strip("-").lower()
    return s or "doc"

def _retry_backoff(delays=(1.0, 2.0, 4.0)):
    for d in delays:
        yield d

def _sources_text(sources: list) -> str:
    root = repo_root()
    chunks = []
    for s in sources:
        try:
            with open(os.path.join(root, s["path"]), "r", encoding="utf-8", errors="ignore") as f:
                lines = f.read().splitlines()
            snippet = "\n".join(lines[s["start"]-1:s["end"]])
            chunks.append(snippet)
        except Exception:
            continue
    return "\n".join(chunks)

def _violates_grounding(answer: str, sources_text: str, must_mention: set[str] | None = None) -> Tuple[bool, str]:
    low_ans = answer.lower()
    low_src = sources_text.lower()
    if sources_text.strip() and "[s" not in low_ans:
        return True, "missing citations"
    for term in DISALLOWED_WHEN_NOT_IN_SOURCES:
        if term.lower() in low_ans and term.lower() not in low_src:
            return True, term
    if must_mention:
        ok = any(name.lower() in low_ans for name in must_mention)
        if not ok:
            return True, f"missing file mention: {', '.join(sorted(must_mention))}"
    return False, ""

def _strict_messages(repo: str, hist: list, question: str, context_prompt: str):
    sys_strict = (
        f"You are Bob inside repo \"{repo}\". Strict mode:\n"
        "- Only describe what is explicitly present in the [S#] context.\n"
        "- If a detail is not in [S#], say: \"I don't know based on the current context.\" "
        "and suggest specific file paths to open.\n"
        "- Never mention external websites/APIs unless they appear in [S#].\n"
        "- Begin with the file(s) you rely on and cite them (e.g., ask.py [S1])."
    )
    msgs = [{"role":"system","content": sys_strict}]
    msgs.extend(hist[-(2*MAX_HISTORY_TURNS):])
    msgs.append({"role":"user","content": context_prompt})
    return msgs

# ------------------ Symbol index ------------------
def _load_symbols() -> Dict[str, List[str]]:
    try:
        with open(SYMBOLS_PATH, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        return {}

def _save_symbols(sym: Dict[str, List[str]]):
    mkparent(SYMBOLS_PATH)
    with open(SYMBOLS_PATH, "w", encoding="utf-8") as f:
        json.dump(sym, f, ensure_ascii=False, indent=2)

def _scan_file_for_symbols(abs_path: str, rel: str) -> List[str]:
    try:
        with open(abs_path, "r", encoding="utf-8", errors="ignore") as f:
            head = f.read(64 * 1024)
    except Exception:
        return []
    syms = set()
    for m in re.finditer(r'^\s*(?:final\s+)?(?:abstract\s+)?(class|interface|trait)\s+([A-Za-z_]\w*)', head, flags=re.M):
        syms.add(m.group(2))
    for m in re.finditer(r'^\s*(?:export\s+)?(?:abstract\s+)?class\s+([A-Za-z_]\w*)', head, flags=re.M):
        syms.add(m.group(1))
    for m in re.finditer(r'^\s*class\s+([A-Za-z_]\w*)\s*[\(:]', head, flags=re.M):
        syms.add(m.group(1))
    base = os.path.basename(rel)
    stem, _ = os.path.splitext(base)
    if re.match(r'^[A-Za-z_]\w*$', stem):
        syms.add(stem)
    return sorted(syms)

def build_symbol_index():
    idx = load_registry()
    root = repo_root()
    files_meta = idx.get("files", {})
    symbols: Dict[str, List[str]] = {}
    for rel in files_meta.keys():
        abs_path = os.path.join(root, rel)
        try:
            st = os.stat(abs_path)
            if st.st_size > MAX_FILE_BYTES:
                continue
        except FileNotFoundError:
            continue
        names = _scan_file_for_symbols(abs_path, rel)
        for name in names:
            symbols.setdefault(name, []).append(rel)
    for k, v in symbols.items():
        symbols[k] = sorted(set(v))
    _save_symbols(symbols)

def resolve_symbols_in_query(query: str) -> List[str]:
    symbols = _load_symbols()
    if not symbols:
        return []
    raw = set(re.findall(r"[A-Za-z][A-Za-z0-9_]+", query))
    uplift = {t.capitalize() for t in raw if t.isalpha() and t.islower()}
    candidates = raw | uplift
    forced = []
    for cand in candidates:
        if cand in symbols:
            forced.extend(symbols[cand])
    seen = set()
    out = [p for p in forced if not (p in seen or seen.add(p))]
    return out

# ------------------ Scoring helpers ------------------
def cosine_similarity(a, b) -> float:
    dot = sum(x*y for x, y in zip(a, b))
    na = math.sqrt(sum(x*x for x in a))
    nb = math.sqrt(sum(y*y for y in b))
    return 0.0 if na == 0 or nb == 0 else dot / (na * nb)

def keyword_overlap_score(query: str, text: str) -> float:
    toks = lambda s: [t for t in re.findall(r"[a-zA-Z0-9_]+", s.lower()) if len(t) > 2]
    q, t = set(toks(query)), set(toks(text))
    return 0.0 if not q or not t else len(q & t) / len(q)

def path_boost(path: str) -> float:
    norm = path.replace("\\", "/")
    b = 0.0
    for rx, w in DIR_BOOSTS:
        if rx.search(norm): b += w
    for rx, w in NAME_BOOSTS:
        if rx.search(norm): b += w
    for rx, w in NAME_PENALTIES:
        if rx.search(norm): b += w
    return b

# ------------------ Ollama ------------------
def ollama_embed_http(host: str, model: str, text: str):
    r = requests.post(f"{host}/api/embeddings", json={"model": model, "prompt": text}, timeout=EMBED_TIMEOUT)
    if r.status_code == 404:
        raise FileNotFoundError("HTTP embeddings unavailable")
    r.raise_for_status()
    j = r.json()
    if "embedding" in j: return j["embedding"]
    if "data" in j and j["data"]: return j["data"][0]["embedding"]
    raise RuntimeError("Unexpected /api/embeddings response")

def ollama_embed_cli(model: str, text: str):
    p = subprocess.run(["ollama", "embed", "-m", model, text], check=True, capture_output=True, text=True)
    lines = [ln for ln in p.stdout.splitlines() if ln.strip()]
    return json.loads(lines[-1])["embedding"]

def ollama_embed(host: str, model: str, text: str):
    try:
        return ollama_embed_http(host, model, text)
    except Exception:
        return ollama_embed_cli(model, text)

def _compose_prompt_from_messages(messages: list) -> str:
    parts = []
    for m in messages:
        role = m.get("role", "user")
        content = m.get("content", "")
        if role == "system":
            parts.append(f"[system]\n{content}\n")
        elif role == "assistant":
            parts.append(f"[assistant]\n{content}\n")
        else:
            parts.append(f"[user]\n{content}\n")
    parts.append("[assistant]\n")
    return "\n".join(parts)

def _chat_stream_http(host: str, payload: dict):
    r = requests.post(f"{host}/api/chat", json=payload, stream=True, timeout=CHAT_TIMEOUT)
    r.raise_for_status()
    for line in r.iter_lines():
        if not line:
            continue
        try:
            j = json.loads(line.decode("utf-8"))
            msg = j.get("message", {}).get("content")
            if msg:
                yield msg
        except Exception:
            continue

def _generate_stream_http(host: str, model: str, prompt: str):
    payload = {
        "model": model,
        "prompt": prompt,
        "stream": True,
        "keep_alive": "15m",
        "options": {
            "temperature": 0.0,
            "top_p": 0.10,
            "top_k": 20,
            "repeat_penalty": 1.15,
            "num_ctx": 8192
        }
    }
    r = requests.post(f"{host}/api/generate", json=payload, stream=True, timeout=CHAT_TIMEOUT)
    r.raise_for_status()
    for line in r.iter_lines():
        if not line:
            continue
        try:
            j = json.loads(line.decode("utf-8"))
            tok = j.get("response")
            if tok:
                yield tok
        except Exception:
            continue

def ollama_chat(host: str, model: str, messages: list, timeout: float = CHAT_TIMEOUT) -> str:
    try:
        r = requests.post(
            f"{host}/api/chat",
            json={
                "model": model,
                "messages": messages,
                "stream": False,
                "keep_alive": "15m",
                "options": {
                    "temperature": 0.0, "top_p": 0.10, "top_k": 20,
                    "repeat_penalty": 1.15, "num_ctx": 8192
                }
            },
            timeout=timeout
        )
        r.raise_for_status()
        return r.json().get("message", {}).get("content", "") or ""
    except Exception:
        return ""

# --- Streaming renderer that handles <think> ... </think> ---
def _print_token(s: str):
    print(s, end="", flush=True)

def _render_stream_with_thinking(tokens_iter, show_think: bool) -> str:
    """
    Render streaming tokens. Colorize or suppress <think> blocks live.
    Returns the full plain-text response (without ANSI codes).
    """
    in_think = False
    think_banner_printed = False
    out_buf = []

    def emit(text: str):
        _print_token(text)
        out_buf.append(text)

    def emit_think(text: str):
        # Visible colored think (or suppressed)
        if show_think:
            if USE_COLOR:
                _print_token(THINK_COLOR + text + RESET_COLOR)
            else:
                _print_token(text)
            out_buf.append(text)  # store plain think text (no ANSI)
        else:
            # hidden: don't print, don't store
            pass

    for raw in tokens_iter:
        chunk = raw
        while True:
            # find next tag
            open_i = chunk.find("<think>")
            close_i = chunk.find("</think>")
            if not in_think and open_i != -1 and (close_i == -1 or open_i < close_i):
                # emit text before <think>
                if open_i > 0:
                    emit(chunk[:open_i])
                in_think = True
                if show_think and not think_banner_printed:
                    _print_token("\n" + ("🧠 " if USE_COLOR else "[thinking] "))
                    think_banner_printed = True
                chunk = chunk[open_i + len("<think>"):]
                continue
            if in_think and close_i != -1:
                # inside think, emit before </think>
                if close_i > 0:
                    emit_think(chunk[:close_i])
                in_think = False
                chunk = chunk[close_i + len("</think>"):]
                continue
            # no more tags in this chunk
            if in_think:
                emit_think(chunk)
            else:
                emit(chunk)
            break
    _print_token("")  # flush
    return "".join(out_buf)

def _local_summary_from_sources(sources: list) -> str:
    text = _sources_text(sources)
    lines = text.splitlines()
    header = next((ln for ln in lines[:15] if ln.strip().startswith("# ")), "")
    shebang = next((ln for ln in lines[:2] if ln.strip().startswith("#!")), "")
    imports = sorted(set(re.findall(r'^\s*(?:import|from)\s+([a-zA-Z0-9_\.]+)', text, flags=re.M)))
    consts  = sorted(set(re.findall(r'^[A-Z_][A-Z0-9_]*\s*=\s*.+$', text, flags=re.M)))
    defs    = sorted(set(re.findall(r'^\s*def\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(', text, flags=re.M)))
    classes = sorted(set(re.findall(r'^\s*class\s+([A-Za-z_][A-Za-z0-9_]*)', text, flags=re.M)))
    commands = []
    m = re.search(r'HELP\s*=\s*"""(.*?)"""', text, flags=re.S)
    if m:
        for ln in m.group(1).splitlines():
            if ln.strip().startswith(":"):
                commands.append(ln.strip())
    files = sorted({s["path"] for s in sources})
    out = []
    out.append("Summary from provided repo sources only:")
    if files:  out.append(f"- Files summarized: {', '.join(files)}")
    if header: out.append(f"- Header: {header.strip()}")
    if shebang: out.append(f"- Shebang: {shebang.strip()}")
    if imports:
        out.append(f"- Imports ({len(imports)}): " + ", ".join(imports[:20]) + ("…" if len(imports) > 20 else ""))
    if consts:
        names = [c.split('=',1)[0].strip() for c in consts[:20]]
    if commands:
        out.append("- REPL commands found in HELP:")
        out.extend([f"  {c}" for c in commands])
    out.append("- Notes: This summary is derived exactly from the shown [S#] snippets; no external sites or APIs were considered.")
    cites = " ".join(sorted({s["tag"] for s in sources}))
    if cites:
        out.append(f"- Cited chunks: {cites}")
    return "\n".join(out)

def ollama_chat_stream(host: str, model: str, messages: list):
    payload = {
        "model": model,
        "messages": messages,
        "stream": True,
        "keep_alive": "15m",
        "options": {
            "temperature": 0.0, "top_p": 0.10, "top_k": 20,
            "repeat_penalty": 1.15, "num_ctx": 8192
        }
    }
    for delay in _retry_backoff():
        try:
            for tok in _chat_stream_http(host, payload):
                yield tok
            return
        except requests.exceptions.ReadTimeout:
            time.sleep(delay)
        except requests.exceptions.RequestException:
            time.sleep(delay)
    prompt = _compose_prompt_from_messages(messages)
    for tok in _generate_stream_http(host, model, prompt):
        yield tok

def warmup_model(model: str):
    try:
        payload = {"model": model, "messages": [{"role":"user","content":"ok"}], "stream": False, "keep_alive": "15m"}
        requests.post(f"{DEFAULT_OLLAMA_HOST}/api/chat", json=payload, timeout=5)
    except Exception:
        pass

# ------------------ Registry ------------------
def _fresh_registry():
    return {"version": 6, "created": int(time.time()), "root": repo_root(), "embed_model": DEFAULT_EMBED_MODEL, "files": {}}

def load_registry():
    if not os.path.exists(INDEX_REG_PATH):
        idx = _fresh_registry()
        save_registry(idx)
        return idx
    try:
        with open(INDEX_REG_PATH, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        ts = time.strftime("%Y%m%d-%H%M%S")
        bad = f"{INDEX_REG_PATH}.corrupt-{ts}"
        try:
            os.replace(INDEX_REG_PATH, bad)
            print(f"{BOB_SPEAKER}: detected corrupt index, backed up → {os.path.relpath(bad, repo_root())}")
        except Exception:
            pass
        idx = _fresh_registry()
        save_registry(idx)
        return idx

def save_registry(idx):
    mkparent(INDEX_REG_PATH)
    tmp = f"{INDEX_REG_PATH}.tmp"
    with open(tmp, "w", encoding="utf-8") as f:
        json.dump(idx, f, ensure_ascii=False)
        f.flush(); os.fsync(f.fileno())
    os.replace(tmp, INDEX_REG_PATH)

def shard_path_for(rel: str) -> str:
    h = hashlib.sha1(rel.encode("utf-8")).hexdigest()[:20]
    return os.path.join(SHARD_DIR, f"{h}.json")

def load_shard(path: str):
    try:
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f)
    except FileNotFoundError:
        return None
    except Exception:
        try: os.remove(path)
        except Exception: pass
        return None

def save_shard(path: str, obj: dict):
    mkparent(path)
    tmp = f"{path}.tmp"
    with open(tmp, "w", encoding="utf-8") as f:
        json.dump(obj, f, ensure_ascii=False)
        f.flush(); os.fsync(f.fileno())
    os.replace(tmp, path)

# ------------------ Reindex ------------------
def reindex_scan(embed_model: str):
    root = repo_root()
    idx = load_registry()
    idx["root"] = root
    idx["embed_model"] = embed_model
    files_meta = idx.get("files", {})

    paths = list_files(root)
    known = set(files_meta.keys())
    current = set(paths)

    for rel in list(known - current):
        shard = files_meta.get(rel, {}).get("shard")
        if shard:
            try: os.remove(shard)
            except Exception: pass
        files_meta.pop(rel, None)

    for rel in sorted(paths):
        abs_path = os.path.join(root, rel)
        try:
            st = os.stat(abs_path)
            if st.st_size > MAX_FILE_BYTES:
                continue
        except FileNotFoundError:
            continue

        sig = file_sig(abs_path)
        m = files_meta.get(rel)
        if m and m.get("sig") == sig:
            continue
        files_meta[rel] = {"sig": sig, "embedded": False, "shard": shard_path_for(rel)}

    idx["files"] = files_meta
    save_registry(idx)

    try:
        build_symbol_index()
    except Exception as e:
        print(f"{BOB_SPEAKER}: symbol index failed ({type(e).__name__}). Proceeding without symbols.")

# ------------------ File scanning ------------------
def should_exclude(path: str) -> bool:
    norm = path.replace("\\", "/")
    return any(re.search(rx, norm) for rx in EXCLUDE_REGEXES)

def list_files(root: str):
    files = set()
    for pattern in INCLUDE_GLOBS:
        for p in glob.glob(os.path.join(root, pattern), recursive=True):
            if os.path.isfile(p) and not should_exclude(p):
                files.add(os.path.relpath(p, root))
    return sorted(files)

def file_sig(abs_path: str) -> str:
    try:
        st = os.stat(abs_path)
        return f"{st.st_size}-{int(st.st_mtime)}"
    except FileNotFoundError:
        return "MISSING"

def chunk_lines_with_ranges(text: str, chunk_lines: int, overlap: int):
    lines = text.splitlines()
    n = len(lines)
    out, start = [], 0
    count = 0
    while start < n and count < MAX_CHUNKS_PER_FILE:
        end = min(start + chunk_lines, n)
        out.append(("\n".join(lines[start:end]), start + 1, end))
        count += 1
        if end == n: break
        start = max(end - overlap, start + 1)
    return out

# ------------------ Lazy embedding per-file ------------------
def ensure_embedded(rel: str, embed_model: str):
    idx = load_registry()
    meta = idx["files"].get(rel)
    if not meta: return False
    shard = meta.get("shard") or shard_path_for(rel)

    sh = load_shard(shard)
    if sh and sh.get("sig") == meta["sig"]:
        meta["embedded"] = True
        idx["files"][rel] = meta
        save_registry(idx)
        return True

    abs_path = os.path.join(repo_root(), rel)
    try:
        with open(abs_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
    except Exception:
        return False

    chunks = chunk_lines_with_ranges(content, CHUNK_LINES, CHUNK_OVERLAP)
    entries = []
    for (chunk, start, end) in chunks:
        text = chunk.strip()
        if not text: continue
        try:
            emb = ollama_embed(DEFAULT_OLLAMA_HOST, embed_model, text)
        except Exception:
            print(f"{BOB_SPEAKER}: embedding failed for {rel}; skipping this file.")
            return False
        cid = hashlib.sha1(f"{rel}:{start}-{end}".encode("utf-8")).hexdigest()[:16]
        entries.append({"id": cid, "start_line": start, "end_line": end, "embedding": emb})

    shard_obj = {"rel": rel, "sig": meta["sig"], "chunks": entries}
    save_shard(shard, shard_obj)

    meta["embedded"] = True
    meta["shard"] = shard
    idx["files"][rel] = meta
    save_registry(idx)
    return True

# ------------------ Candidate retrieval ------------------
def preliminary_score_text(rel: str, text: str, query: str) -> float:
    kw = keyword_overlap_score(query, text)
    return kw + path_boost(rel)

def pick_files_to_embed(query: str, max_files: int) -> List[str]:
    idx = load_registry()
    root = repo_root()
    files_meta = idx.get("files", {})
    all_rels = list(files_meta.keys())

    forced = []
    q_tokens = set(re.findall(r"[A-Za-z0-9_.-/]+", query))
    for t in list(q_tokens):
        if t.endswith(("'", '"', "`")) or t.startswith(("'", '"', "`")):
            q_tokens.add(t.strip("'\"`"))

    for rel in all_rels:
        base = os.path.basename(rel)
        if base in q_tokens or rel in q_tokens:
            forced.append(rel)

    seen = set()
    forced = [r for r in forced if not (r in seen or seen.add(r))]

    scored = []
    for rel in all_rels:
        if rel in forced:
            continue
        try:
            with open(os.path.join(root, rel), "r", encoding="utf-8", errors="ignore") as f:
                head = f.read(8000)
        except Exception:
            continue
        s = keyword_overlap_score(query, head) + path_boost(rel)
        scored.append((s, rel))
    scored.sort(reverse=True)
    remaining = [rel for _, rel in scored]

    if re.search(r"\b(database|db|pdo|sql)\b", query, flags=re.I):
        dbish = [r for r in remaining if re.search(r"/(db|database|pdo)/|database\.", r, flags=re.I)]
        remaining = [r for r in remaining if r not in dbish]
        remaining = dbish + remaining

    out = forced + remaining
    return out[:max_files]

def retrieve_candidates(query: str, embed_model: str, k_hint: int, max_files_this_query: int):
    idx = load_registry()
    if not idx.get("files"):
        print(f"{BOB_SPEAKER}: index is empty. Use :reindex")
        sys.exit(1)

    targets = pick_files_to_embed(query, max_files_this_query)

    to_embed = [rel for rel in targets if not idx["files"].get(rel, {}).get("embedded")]
    if to_embed:
        print(f"{BOB_SPEAKER}: embedding {len(to_embed)} files…", flush=True)
        for rel in to_embed:
            ensure_embedded(rel, embed_model)

    qvec = ollama_embed(DEFAULT_OLLAMA_HOST, embed_model, query)

    hits = []
    for rel, meta in idx["files"].items():
        if not meta.get("embedded"):
            continue
        sh = load_shard(meta.get("shard") or shard_path_for(rel))
        if not sh:
            continue
        for ch in sh.get("chunks", []):
            vec_score = cosine_similarity(qvec, ch["embedding"])
            hits.append({
                "path": rel,
                "start_line": ch["start_line"],
                "end_line": ch["end_line"],
                "vec_score": vec_score,
                "kw_score": 0.0
            })

    prelim = sorted(hits, key=lambda x: x["vec_score"], reverse=True)[:500]
    root = repo_root()
    for h in prelim:
        try:
            with open(os.path.join(root, h["path"]), "r", encoding="utf-8", errors="ignore") as f:
                lines = f.read().splitlines()
        except Exception:
            continue
        s, e = h["start_line"], h["end_line"]
        h["kw_score"] = keyword_overlap_score(query, "\n".join(lines[s-1:e]))

    for h in prelim:
        base = (1 - ALPHA_KEYWORD) * h["vec_score"] + ALPHA_KEYWORD * h["kw_score"]
        h["score"] = base + path_boost(h["path"])
    return sorted(prelim, key=lambda x: x["score"], reverse=True)

def mmr(hits: list, k: int):
    per_file_cap = max(2, k // 2)
    counts = {}
    selected, candidates = [], hits[:]
    while candidates and len(selected) < k:
        candidates = [c for c in candidates if counts.get(c["path"], 0) < per_file_cap]
        if not candidates: break
        if not selected:
            pick = candidates.pop(0)
            selected.append(pick)
            counts[pick["path"]] = counts.get(pick["path"], 0) + 1
            continue
        best_i, best_val = 0, -1e9
        for i, c in enumerate(candidates):
            relv = c["score"]
            div = 1.0
            for s in selected:
                if s["path"] == c["path"]:
                    overlap = max(0, min(s["end_line"], c["end_line"]) - max(s["start_line"], c["start_line"]))
                    span = max((c["end_line"] - c["start_line"] + 1), 1)
                    jaccard = overlap / span
                    div = min(div, 1.0 - jaccard)
            val = MMR_LAMBDA * relv + (1 - MMR_LAMBDA) * div
            if val > best_val:
                best_val, best_i = val, i
        pick = candidates.pop(best_i)
        selected.append(pick)
        counts[pick["path"]] = counts.get(pick["path"], 0) + 1
    return selected

def build_prompt_with_sources(query: str, hits: list, pinned_paths: list):
    root = repo_root()
    sources, blocks = [], []
    for i, h in enumerate(hits, start=1):
        try:
            with open(os.path.join(root, h["path"]), "r", encoding="utf-8", errors="ignore") as f:
                lines = f.read().splitlines()
        except Exception:
            continue
        s, e = h["start_line"], h["end_line"]
        excerpt = "\n".join(lines[s-1:e])
        tag = f"[S{i}] {h['path']}:{s}-{e}"
        sources.append({"tag": f"[S{i}]", "path": h["path"], "start": s, "end": e, "score": h["score"]})
        blocks.append(f"{tag}\n{excerpt}")

    context = "\n\n".join(blocks) if blocks else "NO MATCHING CONTEXT FOUND."
    cfg_hint = ""
    if any(s["path"].lower().endswith(".cfg") for s in sources):
        cfg_hint = "\nNote: Files with .cfg in this repo are configuration/metadata, not executable logic."

    pinned_line = ""
    if pinned_paths:
        pinned_line = "Focus file(s) explicitly mentioned by the user: " + ", ".join(pinned_paths)

    prompt = (
        "You are given repository context chunks labeled [S#]. "
        "Cite them in your answer like [S1], [S2] when using them.\n"
        f"{pinned_line}\n"
        "Only describe what is in these sources. Do NOT mention any websites, APIs, or services that do not appear in [S#]. "
        "If you cannot support a claim with [S#], say exactly: \"I don't know based on the current context.\" and suggest which files to open next.\n\n"
        f"{context}\n{cfg_hint}\n\n"
        f"Question: {query}\n"
        "Answer as Bob. Start by naming the file(s) you rely on and cite them, e.g. “From ask.py [S1] …”."
    )
    return prompt, sources

def extract_explicit_paths_from_query(query: str, known_paths: list) -> list:
    tokens = set(re.findall(r"[A-Za-z0-9_./\-]+", query))
    more = set()
    for t in tokens:
        tt = t.strip("`'\"")
        if tt != t:
            more.add(tt)
    tokens |= more
    basemap = {}
    for rel in known_paths:
        basemap.setdefault(os.path.basename(rel), []).append(rel)
    pinned = []
    for t in tokens:
        if t in known_paths:
            pinned.append(t); continue
        if t in basemap:
            pinned.extend(basemap[t])
    seen = set()
    out = [p for p in pinned if not (p in seen or seen.add(p))]
    return out

def collect_file_hits(rel: str, priority_score: float = 10.0) -> list:
    idx = load_registry()
    meta = idx["files"].get(rel)
    if not meta or not meta.get("embedded"): return []
    sh = load_shard(meta.get("shard") or shard_path_for(rel))
    if not sh: return []
    hits = []
    for ch in sh.get("chunks", []):
        hits.append({
            "path": rel, "start_line": ch["start_line"], "end_line": ch["end_line"],
            "vec_score": 1.0, "kw_score": 0.5, "score": priority_score,
        })
    return hits

# ------------------ Hidden planning & self-ask ------------------
def _safe_json_loads(s: str):
    try:
        return json.loads(s)
    except Exception:
        return None

def propose_more_targets(user_query: str, known_paths: List[str]) -> List[str]:
    if not ENABLE_DELIBERATION:
        return []
    repo = project_name()
    sysmsg = (
        f"You are helping select files inside repo '{repo}'. "
        "Return a JSON list of up to 6 repo basenames/paths most likely relevant to the user question. "
        "Prefer explicit filenames/classes mentioned. Only include items likely to exist."
    )
    usr = f"Question: {user_query}\nRespond with JSON array of basenames or paths, no commentary."
    raw = ollama_chat(DEFAULT_OLLAMA_HOST, DEFAULT_MODEL, [
        {"role":"system","content":sysmsg},
        {"role":"user","content":usr}
    ])
    arr = _safe_json_loads(raw.strip()) if raw else None
    if not isinstance(arr, list):
        return []

    basemap = {}
    for rel in known_paths:
        basemap.setdefault(os.path.basename(rel), []).append(rel)

    out = []
    seen = set()
    for item in arr[:12]:
        if not isinstance(item, str):
            continue
        item = item.strip().lstrip("./")
        if item in known_paths and item not in seen:
            out.append(item); seen.add(item); continue
        base = os.path.basename(item)
        if base in basemap:
            for rel in basemap[base]:
                if rel not in seen:
                    out.append(rel); seen.add(rel)
    return out

def deliberate_plan(user_query: str, sources: List[dict]) -> str:
    if not ENABLE_DELIBERATION:
        return ""
    src_labels = [f'{s["tag"]} {s["path"]}:{s["start"]}-{s["end"]}' for s in sources]
    sysmsg = (
        "You are preparing a hidden reasoning plan. "
        "Write a short bullet list of steps to answer the question strictly from [S#] context, "
        "including which chunks to cite and what to verify. Keep under 120 words. "
        "Do NOT include the final answer; this is a private scratchpad."
    )
    usr = "Question: " + user_query + "\nAvailable context chunks:\n" + "\n".join(src_labels)
    plan = ollama_chat(DEFAULT_OLLAMA_HOST, DEFAULT_MODEL, [
        {"role":"system","content":sysmsg},
        {"role":"user","content":usr}
    ])
    return (plan or "").strip()

# ------------------ History & Memory ------------------
def load_history():
    if not os.path.exists(HIST_PATH): return []
    try:
        with open(HIST_PATH, "r", encoding="utf-8") as f:
            h = json.load(f)
        return h[-(2*MAX_HISTORY_TURNS):]
    except Exception:
        return []

def save_history(hist: list):
    with open(HIST_PATH, "w", encoding="utf-8") as f:
        json.dump(hist[-(2*MAX_HISTORY_TURNS):], f, ensure_ascii=False, indent=2)

def load_memory_summary() -> str:
    try:
        with open(MEMO_PATH, "r", encoding="utf-8") as f:
            obj = json.load(f)
            return obj.get("summary", "")
    except Exception:
        return ""

def save_memory_summary(text: str):
    mkparent(MEMO_PATH)
    with open(MEMO_PATH, "w", encoding="utf-8") as f:
        json.dump({"summary": text}, f, ensure_ascii=False, indent=2)

def update_memory_summary(hist: list, model: str):
    recent = hist[-16:]
    content = "Summarize persistent facts and preferences from this dialog for future use:\n" + json.dumps(recent, ensure_ascii=False)
    msgs = [
        {"role":"system","content":"Extract only durable facts/instructions/preferences. Omit pleasantries."},
        {"role":"user","content":content}
    ]
    try:
        r = requests.post(f"{DEFAULT_OLLAMA_HOST}/api/chat", json={"model": model, "messages": msgs, "stream": False}, timeout=CHAT_TIMEOUT)
        r.raise_for_status()
        summary = r.json().get("message",{}).get("content","").strip()
        if summary:
            save_memory_summary(summary)
    except Exception:
        pass

def normalize_self_refs(q: str, repo: str) -> str:
    pats = [
        r"\bthis (project|repo|framework|core|codebase)\b",
        r"\bthe (project|repo|framework|core)\b",
        r"\bour (framework|codebase)\b",
    ]
    out = q
    for p in pats:
        out = re.sub(p, lambda m: f'"{repo}" {m.group(1)}', out, flags=re.I)
    return out

# ------------------ Ask / Chat ------------------
def ask_once(user_query: str, k: int, model: str, embed_model: str, hist: list):
    print("Bob is indexing...", flush=True)
    reindex_scan(embed_model)

    reg = load_registry()
    all_paths = list(reg.get("files", {}).keys())
    pinned_paths = extract_explicit_paths_from_query(user_query, all_paths)

    symbol_paths = resolve_symbols_in_query(user_query)
    for sp in symbol_paths:
        if sp not in pinned_paths:
            pinned_paths.append(sp)

    extra_paths = propose_more_targets(user_query, all_paths)
    if extra_paths:
        print("🧠 Expanding context…", flush=True)
    for ep in extra_paths:
        if ep not in pinned_paths:
            pinned_paths.append(ep)

    pinned_basenames = {os.path.basename(p) for p in pinned_paths}

    max_files_this_query = MAX_FILES_TO_EMBED_PER_QUERY
    if pinned_paths:
        max_files_this_query = min(8, MAX_FILES_TO_EMBED_PER_QUERY)

    for rel in pinned_paths:
        if not reg["files"].get(rel, {}).get("embedded"):
            ensure_embedded(rel, embed_model)

    candidates = retrieve_candidates(user_query, embed_model, k, max_files_this_query)

    pinned_hits = []
    for rel in pinned_paths:
        pinned_hits.extend(collect_file_hits(rel, priority_score=10.0))

    merged = pinned_hits + candidates
    dedup = []
    seen = set()
    for h in merged:
        key = (h["path"], h["start_line"], h["end_line"])
        if key in seen: continue
        seen.add(key)
        dedup.append(h)

    hits = mmr(dedup, k)
    prompt, sources = build_prompt_with_sources(user_query, hits, pinned_paths)

    hidden_plan = ""
    if ENABLE_DELIBERATION:
        print("🧠 Bob is planning…", flush=True)
        hidden_plan = deliberate_plan(user_query, sources)

    repo = project_name()
    messages = [{"role": "system", "content": SYSTEM_INSTRUCTIONS(repo, ENABLE_DELIBERATION)}]
    if hidden_plan:
        messages.append({"role":"system","content": f"(hidden plan)\n{hidden_plan}"})
    memo = load_memory_summary()
    if memo:
        messages.insert(0, {"role":"system","content": f"Persistent memory: {memo}"})
    messages.extend(hist[-(2*MAX_HISTORY_TURNS):])
    messages.append({"role": "user", "content": prompt})

    print("Bob is thinking...", flush=True)
    print(f"\n{BOB_SPEAKER}:\n", end="", flush=True)

    # Stream with think rendering
    full_text = _render_stream_with_thinking(
        ollama_chat_stream(DEFAULT_OLLAMA_HOST, model, messages),
        show_think=ENABLE_DELIBERATION
    )
    print("")  # newline after stream

    # Auto-show sources
    if sources:
        print("\n=== Sources used ===")
        for s in sources:
            print(f"{s['tag']} {s['path']}:{s['start']}-{s['end']}  (score={s['score']:.3f})")

    # Grounding check
    src_text = _sources_text(sources)
    bad, what = _violates_grounding(full_text, src_text, must_mention=pinned_basenames)
    if bad:
        print(f"\n{BOB_SPEAKER}: Detected drift ({what}). Re-answering strictly from the shown sources…\n")
        strict_msgs = _strict_messages(repo, hist, user_query, prompt)
        print(f"{BOB_SPEAKER} (correction):\n", end="", flush=True)
        corrected = _render_stream_with_thinking(
            ollama_chat_stream(DEFAULT_OLLAMA_HOST, model, strict_msgs),
            show_think=False  # never show think on correction pass
        )
        print("")
        src_text2 = _sources_text(sources)
        bad2, what2 = _violates_grounding(corrected, src_text2, must_mention=pinned_basenames)
        if bad2:
            print(f"\n{BOB_SPEAKER}: Model drifted again ({what2}). Falling back to deterministic summary from sources.\n")
            det = _local_summary_from_sources(sources)
            print(det)
            return det, sources
        return corrected.strip(), sources

    return full_text.strip(), sources

def chat_intro(model: str, embed_model: str, k: int):
    repo = project_name()
    think_state = "on" if ENABLE_DELIBERATION else "off"
    print(f"👋 Hi, I'm {BOB_SPEAKER} — your repo-aware assistant for “{repo}”.")
    print(f"Model: {model} | Embeddings: {embed_model} | k={k} | thinking: {think_state}")
    print("Ask me anything about this codebase. Commands: :help  :clean  :reindex  :sources  :model  :k  :mkdir  :doc  :write  :think  :quit")

def init_readline():
    histfile = os.path.join(TMP_DIR, "bob_repl_history.txt")
    try:
        readline.read_history_file(histfile)
    except FileNotFoundError:
        pass
    doc = (getattr(readline, "__doc__", "") or "").lower()
    try:
        if "libedit" in doc:
            readline.parse_and_bind("bind ^I rl_complete")
        else:
            readline.parse_and_bind("tab: complete")
            readline.parse_and_bind('"\\e[A": history-search-backward')
            readline.parse_and_bind('"\\e[B": history-search-forward')
    except Exception:
        pass
    atexit.register(lambda: (os.makedirs(TMP_DIR, exist_ok=True), readline.write_history_file(histfile)))

def chat_loop(model: str, embed_model: str, k: int):
    warmup_model(model)
    try:
        _ = ollama_embed(DEFAULT_OLLAMA_HOST, DEFAULT_EMBED_MODEL, "warmup")
        _ = list(ollama_chat_stream(DEFAULT_OLLAMA_HOST, model, [
            {"role":"system","content":"warmup"},
            {"role":"user","content":"ok"}
        ]))
    except Exception:
        pass

    chat_intro(model, embed_model, k)
    init_readline()

    hist = load_history()
    last_sources = []
    last_answer = ""

    while True:
        try:
            q = input("\nYou: ").strip()
        except (EOFError, KeyboardInterrupt):
            print(f"\n{BOB_SPEAKER}: Catch you later. 👋")
            break

        if not q:
            continue

        if q.startswith(":"):
            parts = q.split(maxsplit=2)
            cmd = parts[0].lower()

            if cmd in (":quit", ":q", ":exit"):
                print(f"{BOB_SPEAKER}: Bye!"); break
            if cmd == ":help":
                print(HELP); continue
            if cmd == ":clean":
                try:
                    if os.path.isdir(SHARD_DIR):
                        for name in os.listdir(SHARD_DIR):
                            p = os.path.join(SHARD_DIR, name)
                            try: os.remove(p)
                            except Exception: pass
                    try: os.remove(INDEX_REG_PATH)
                    except Exception: pass
                    print(f"{BOB_SPEAKER}: cleared cached index. Run :reindex to rescan files.")
                except Exception as e:
                    print(f"{BOB_SPEAKER}: clean failed: {e}")
                continue
            if cmd == ":reindex":
                print("Bob is indexing...", flush=True)
                reindex_scan(embed_model)
                print(f"{BOB_SPEAKER}: Scan complete. I’ll embed files on demand when you ask.")
                continue
            if cmd == ":sources":
                if not last_sources: print(f"{BOB_SPEAKER}: (no sources yet)")
                else:
                    for s in last_sources:
                        print(f"{s['tag']} {s['path']}:{s['start']}-{s['end']} (score={s['score']:.3f})")
                continue
            if cmd == ":model":
                if len(parts) >= 2:
                    model = parts[1]
                    warmup_model(model)
                    print(f"{BOB_SPEAKER}: model set to {model}")
                else:
                    print(f"{BOB_SPEAKER}: current model is {model}")
                continue
            if cmd == ":k":
                if len(parts) == 2 and parts[1].isdigit():
                    k = int(parts[1]); print(f"{BOB_SPEAKER}: k set to {k}")
                else:
                    print(f"{BOB_SPEAKER}: current k is {k}")
                continue
            if cmd == ":mkdir":
                if len(parts) >= 2:
                    path = safe_relpath(parts[1])
                    os.makedirs(path, exist_ok=True)
                    print(f"{BOB_SPEAKER}: created folder {os.path.relpath(path, repo_root())}")
                else:
                    print(f"{BOB_SPEAKER}: usage :mkdir <path>")
                continue
            if cmd == ":write":
                if len(parts) >= 2:
                    path = safe_relpath(parts[1])
                    mkparent(path)
                    with open(path, "w", encoding="utf-8") as f:
                        f.write(last_answer or "")
                    print(f"{BOB_SPEAKER}: wrote last answer to {os.path.relpath(path, repo_root())}")
                else:
                    print(f"{BOB_SPEAKER}: usage :write <path>")
                continue
            if cmd == ":doc":
                if len(parts) >= 2:
                    title = parts[1].strip()
                    if title.startswith('"') and title.endswith('"') and len(title) >= 2:
                        title = title[1:-1]
                    slug = slugify(title)
                    out_path = safe_relpath(os.path.join("Documentation", f"{slug}.md"))
                    os.makedirs(os.path.dirname(out_path), exist_ok=True)
                    topic = f'Create developer-facing documentation titled "{title}" for {project_name()}, using cited code snippets where helpful.'
                    qx = normalize_self_refs(topic, project_name())
                    ans, _src = ask_once(qx, k, model, embed_model, hist)
                    with open(out_path, "w", encoding="utf-8") as f:
                        f.write(ans.strip() + "\n")
                    print(f"{BOB_SPEAKER}: documentation saved → {os.path.relpath(out_path, repo_root())}")
                    last_answer = ans
                else:
                    print(f'{BOB_SPEAKER}: usage :doc "Title of the page"')
                continue
            if cmd == ":think":
                global ENABLE_DELIBERATION
                ENABLE_DELIBERATION = not ENABLE_DELIBERATION
                state = "on" if ENABLE_DELIBERATION else "off"
                print(f"{BOB_SPEAKER}: thinking is now {state}.")
                continue

            print(f"{BOB_SPEAKER}: Unknown command. :help for options.")
            continue

        qx = normalize_self_refs(q, project_name())
        ans, srcs = ask_once(qx, k, model, embed_model, hist)
        last_sources = srcs
        last_answer = ans
        hist.append({"role": "user", "content": q})
        hist.append({"role": "assistant", "content": ans})
        save_history(hist)

        user_turns = sum(1 for h in hist if h["role"] == "user")
        if user_turns % 4 == 0:
            update_memory_summary(hist, model)

def oneshot(q: str, model: str, embed_model: str, k: int):
    warmup_model(model)
    try:
        _ = ollama_embed(DEFAULT_OLLAMA_HOST, DEFAULT_EMBED_MODEL, "warmup")
    except Exception:
        pass
    hist = load_history()
    q = normalize_self_refs(q, project_name())
    ans, srcs = ask_once(q, k, model, embed_model, hist)
    if srcs:
        print("\n=== Sources used ===")
        for s in srcs:
            print(f"{s['tag']} {s['path']}:{s['start']}-{s['end']}  (score={s['score']:.3f})")

def main():
    ap = argparse.ArgumentParser(add_help=True)
    ap.add_argument("--model", default=DEFAULT_MODEL, help="LLM model")
    ap.add_argument("--embed-model", default=DEFAULT_EMBED_MODEL, help="Embedding model")
    ap.add_argument("--k", type=int, default=TOP_K_DEFAULT, help=f"Top-k retrieval (default {TOP_K_DEFAULT})")
    ap.add_argument("--oneshot", action="store_true", help="Run a single question passed as trailing args (streams)")
    ap.add_argument("--no-thinking", action="store_true", help="Disable hidden planning/self-ask for this run")
    ap.add_argument("question", nargs="*", help="Question (when using --oneshot)")
    args = ap.parse_args()

    if args.no_thinking:
        global ENABLE_DELIBERATION
        ENABLE_DELIBERATION = False

    if args.oneshot:
        if not args.question:
            print("Provide a question for --oneshot"); sys.exit(1)
        return oneshot(" ".join(args.question), args.model, args.embed_model, args.k)

    chat_loop(args.model, args.embed_model, args.k)

if __name__ == "__main__":
    main()
