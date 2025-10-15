#!/usr/bin/env bash
# setup.sh — prepare Python env for ask.py (and optionally pull Ollama models)
# Usage:
#   bash setup.sh
#   bash setup.sh --with-models
#   bash setup.sh --with-models --model codellama:34b --embed-model nomic-embed-text
#
# Notes:
# - Creates a `.venv` in the current directory.
# - Installs Python deps: requests
# - If --with-models is passed, pulls LLM + embedding models via Ollama.
# - Adds `.venv` and `rag_index.json` to .gitignore (if not present).

set -euo pipefail

# -------- Defaults --------
PYTHON_BIN="${PYTHON_BIN:-python3}"
VENV_DIR="${VENV_DIR:-.venv}"
MODEL="codellama:34b"
EMBED_MODEL="nomic-embed-text"
WITH_MODELS=false
OLLAMA_HOST="${OLLAMA_HOST:-http://localhost:11434}"

# -------- Parse args --------
while [[ $# -gt 0 ]]; do
  case "$1" in
    --with-models) WITH_MODELS=true; shift ;;
    --model) MODEL="$2"; shift 2 ;;
    --embed-model) EMBED_MODEL="$2"; shift 2 ;;
    --python) PYTHON_BIN="$2"; shift 2 ;;
    -h|--help)
      echo "setup.sh options:"
      echo "  --with-models                  Pull Ollama models (${MODEL}, ${EMBED_MODEL})"
      echo "  --model <name>                 LLM name (default: ${MODEL})"
      echo "  --embed-model <name>           Embedding model (default: ${EMBED_MODEL})"
      echo "  --python <python executable>   Use a specific Python (default: ${PYTHON_BIN})"
      echo "Env:"
      echo "  OLLAMA_HOST (default: ${OLLAMA_HOST})"
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2; exit 1 ;;
  esac
done

# -------- Helpers --------
need_cmd() {
  command -v "$1" >/dev/null 2>&1 || { echo "Error: '$1' not found in PATH." >&2; exit 1; }
}

version_ge() {
  # Compare semver-ish versions: version_ge A B  => A >= B
  # Only reliable for simple x.y.z numeric versions.
  [ "$(printf '%s\n' "$2" "$1" | sort -V | head -n1)" = "$2" ]
}

# -------- Checks --------
need_cmd "$PYTHON_BIN"

PY_VER="$($PYTHON_BIN -c 'import sys; print(".".join(map(str, sys.version_info[:3])))')"
REQ_VER="3.9.0"
if ! version_ge "$PY_VER" "$REQ_VER"; then
  echo "Error: Python >= ${REQ_VER} required (found ${PY_VER})." >&2
  echo "Hint: On macOS: 'brew install python@3.11' then re-run: PYTHON_BIN=python3.11 bash setup.sh"
  exit 1
fi

# -------- Create venv --------
if [[ -d "$VENV_DIR" ]]; then
  echo "✓ Virtual env already exists at '$VENV_DIR'"
else
  echo "→ Creating virtual env at '$VENV_DIR' using $PYTHON_BIN ..."
  "$PYTHON_BIN" -m venv "$VENV_DIR"
  echo "✓ Virtual env created"
fi

# shellcheck disable=SC1090
source "${VENV_DIR}/bin/activate"

# -------- Upgrade pip & install deps --------
echo "→ Upgrading pip/setuptools/wheel ..."
python -m pip install --upgrade pip setuptools wheel >/dev/null

echo "→ Installing Python dependencies ..."
# ask.py only needs requests; keeps the env lean.
python -m pip install requests >/dev/null
echo "✓ Dependencies installed"

# -------- Optional: Ollama models --------
if $WITH_MODELS; then
  if command -v ollama >/dev/null 2>&1; then
    echo "→ Checking if Ollama is reachable at ${OLLAMA_HOST} ..."
    if curl -fsS "${OLLAMA_HOST}/api/tags" >/dev/null; then
      echo "→ Pulling models via Ollama ..."
      set +e
      ollama pull "${MODEL}"
      OLLAMA_PULL_1=$?
      ollama pull "${EMBED_MODEL}"
      OLLAMA_PULL_2=$?
      set -e
      if [[ $OLLAMA_PULL_1 -ne 0 || $OLLAMA_PULL_2 -ne 0 ]]; then
        echo "⚠️  Model pull reported errors. Ensure 'ollama serve' is running and you have disk space."
      else
        echo "✓ Models ready: ${MODEL}, ${EMBED_MODEL}"
      fi
    else
      cat <<EOF
⚠️  Ollama API not reachable at ${OLLAMA_HOST}.
    Make sure Ollama is installed and running:
      - Install: https://ollama.com/
      - Start:   ollama serve
    You can re-run: bash setup.sh --with-models
EOF
    fi
  else
    cat <<'EOF'
⚠️  'ollama' command not found.
    Install from https://ollama.com/ and then run:
      ollama serve
    Afterwards, re-run:
      bash setup.sh --with-models
EOF
  fi
fi

# -------- .gitignore convenience --------
if [[ -f ".gitignore" ]]; then
  if ! grep -qE '^\s*\.venv\s*$' .gitignore; then echo ".venv" >> .gitignore; fi
  if ! grep -qE '^\s*rag_index\.json\s*$' .gitignore; then echo "rag_index.json" >> .gitignore; fi
else
  printf ".venv\nrag_index.json\n" > .gitignore
fi

# -------- Done --------
cat <<EOF

✅ Setup complete.

Activate your environment:
  source ${VENV_DIR}/bin/activate

Build the index:
  ./ask.py index

Ask a question:
  ./ask.py "Where is the DB connection created?"

(If you skipped models, pull them later:)
  ollama pull ${MODEL}
  ollama pull ${EMBED_MODEL}

Tip: set OLLAMA_HOST if you're not on the default:
  export OLLAMA_HOST=http://localhost:11434
EOF
