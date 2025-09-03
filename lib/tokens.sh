#!/usr/bin/env bash
set -euo pipefail

###############################################################################
# Defaults
###############################################################################
run=true
dirScript=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
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
        echo "Unexpected extra argument: $1" >&2
        exit 1
  esac
done

###############################################################################
# Work
###############################################################################

# Loop through each type
if $run; then
    for type in module plugin theme; do
        echo "Processing type: ${type}s"

        # Set token based on type
        case "$type" in
            module) token="$tokenModule" ;;
            plugin) token="$tokenPlugin" ;;
            theme)  token="$tokenTheme" ;;
            *) echo "Unknown type: ${type}s" >&2; exit 1 ;;
        esac

        # Change to the appropriate directory
        cd "${dirScript}/${type}s" || exit 1

        # Loop through each base directory
        for base in */; do
            base=${base%/}

            # Skip if the base directory is not a directory
            if [[ ! -d "${dirScript}/${type}s/${base}" ]]; then
                continue
            fi

            echo "Processing base: $base"

            # Change to the appropriate directory
            cd "${dirScript}/${type}s/${base}" || { echo "Failed to enter $base"; exit 1; }

            # Check if the base directory is a git repository
            if [[ -d "${dirScript}/${type}s/${base}/.git" ]]; then
                echo "Updating repository for $base"
                git remote set-url origin "https://${token}@github.com/LaswitchTech/core-${type}-${base}.git"
                echo ${token} > TOKEN
            fi
        done
    done
fi

cd "$dirScript"
