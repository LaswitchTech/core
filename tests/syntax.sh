#!/usr/bin/env bash
# syntax.sh — recursive PHP lint check
#
# Usage: tests/syntax.sh
# Exit 0 on success, 1 on first syntax error.
#
# Checks all .php files in src/, lib/plugins/*/, lib/modules/*/,
# tests/, Command/, and the project root (excluding vendor/ and generated).

set -euo pipefail

TMPFILE=$(mktemp)
trap 'rm -f "$TMPFILE"' EXIT

find . -name '*.php' \
    -not -path './vendor/*' \
    -not -path './.phpunit.result.cache' \
    -not -path './lib/themes/*' \
    -not -path './node_modules/*' \
    > "$TMPFILE"

ERRORS=0
FILES=$(wc -l < "$TMPFILE" | tr -d ' ')

while IFS= read -r file; do
    if ! php -l "$file" >/dev/null 2>&1; then
        echo "ERROR: $file"
        ERRORS=$((ERRORS + 1))
    fi
done < "$TMPFILE"

if [ "$ERRORS" -gt 0 ]; then
    echo ""
    echo "== Syntax errors: $ERRORS files =="
    exit 1
fi

echo "Syntax check passed: $FILES files, 0 errors"
exit 0
