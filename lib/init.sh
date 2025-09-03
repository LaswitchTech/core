#!/usr/bin/env bash
set -euo pipefail

###############################################################################
# Defaults
###############################################################################
run=true
copy=true
type=""
branch="dev"
dirScript=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
today=$(date +%F)   # YYYY-MM-DD
token=$(cat $dirScript/TOKEN 2>/dev/null || echo "")
tokenModule=$token
tokenPlugin=$token
tokenTheme=$token

###############################################################################
# Option parsing
###############################################################################
while [[ $# -gt 0 ]]; do
  case "$1" in
    --dry-run)  run=false; shift ;;
    --no-copy)  copy=false; shift ;;
    --token)    shift; token=${1:?--token needs a value};  shift ;;
    --branch)   shift; branch=${1:?--branch needs a value}; shift ;;
    -h|--help)
      cat <<EOF
Usage: $0 [options] <module|plugin|theme>

Options
  --dry-run          Do everything except clone/commit
  --token <PAT>      GitHub personal-access token to use
  --branch <name>    Branch to clone (default: dev)
  --no-copy          Do not copy the skeleton files
  -h, --help         Show this help
EOF
      exit 0 ;;
    --*) echo "Unknown option: $1" >&2; exit 1 ;;
    *)
        if [[ -z $type ]]; then
            type=$1
            shift
         else
            echo "Unexpected extra argument: $1" >&2
            exit 1
         fi ;;
  esac
done

###############################################################################
# Positional parameter: type
###############################################################################
if [[ -z $type ]]; then
  echo "Error: missing type (module|plugin|theme)" >&2
  exit 1
fi

# Check if a token is set, if not use the default for the type
if [[ -z $token ]]; then
    # Set token based on type
    case "$type" in
        module) token="$tokenModule" ;;
        plugin) token="$tokenPlugin" ;;
        theme)  token="$tokenTheme" ;;
        *) echo "Unknown type: ${type}s" >&2; exit 1 ;;
    esac
fi

###############################################################################
# Prompt for remaining data
###############################################################################
read -rp "Repository (e.g. LaswitchTech/core-${type}-include): " repository
[[ -z $token ]] && read -rsp "GitHub token: " token && echo
read -rp "Extension name (e.g. Include): " name

###############################################################################
# Derived vars
###############################################################################
owner=${repository%%/*}
repo=${repository##*/}
base=${repo#core-${type}-}
url="https://${token}@github.com/${repository}.git"

###############################################################################
# Work
###############################################################################
cd "$dirScript/${type}s" || exit 1      # => lib/modules|plugins|themes

if $run; then
    if [[ -d $base/.git ]]; then
        git remote set-url origin "$url"
    else
        git clone --branch "$branch" "$url" "$base"
    fi
fi

cd "$dirScript/${type}s/$base" || { echo "Failed to enter $base"; exit 1; }

if $run; then
    echo "Copying skeleton…"
    if $copy; then
        rsync -a "$dirScript/skeleton/" ./
    fi

        echo "Replacing placeholders…"
        find . -type f -not -path "./.git/*" -print0 |
            xargs -0 perl -pi -e '
                s|%REPOSITORY%|'"$repository"'\E|g;
                s|%TYPE%|'"$type"'\E|g;
                s|%BASE%|'"$base"'\E|g;
                s|%TOKEN%|'"$token"'\E|g;
                s|%OWNER%|'"$owner"'\E|g;
                s|%NAME%|'"$name"'\E|g;
                s|%TODAY%|'"$today"'\E|g;
            '

        echo "Committing changes…"

        git add .
        git commit -m "Init: applied skeleton and variable substitution"
        git push origin "$branch"

        echo "Make sure to add GH_PAT to the repository secrets"
        echo "Here is your token for reference: ${token}"
    else
        echo "[dry-run] Would copy skeleton, substitute variables, and commit"
fi

cd "$dirScript"
