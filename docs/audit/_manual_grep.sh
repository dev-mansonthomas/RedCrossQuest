#!/usr/bin/env bash
# Manual grep for PHP 8.5 breaking-change patterns that PHPCompatibility 9.3.5
# does not yet detect. Run from anywhere; paths are resolved relative to the
# script location.
set -uo pipefail
here="$(cd "$(dirname "$0")" && pwd)"
cd "$here/../../server"

out="$here/manual_grep.txt"
: > "$out"

check() {
  local label="$1"; shift
  {
    echo "=== $label ==="
    local result
    result=$(eval "$*" 2>/dev/null || true)
    if [ -z "$result" ]; then
      echo "  (aucun)"
    else
      echo "$result"
    fi
    echo ""
  } >> "$out"
}

check "Casts non-canoniques (integer|boolean|double|binary)" \
  "grep -rn -E '=[[:space:]]*\((integer|boolean|double|binary)\)' src public"
check "__sleep / __wakeup" \
  "grep -rn -E 'function __(sleep|wakeup)\(' src"
check "DATE_RFC7231 / DateTimeInterface::RFC7231" \
  "grep -rn 'RFC7231' src public"
check "disable_classes INI" \
  "grep -rn 'disable_classes' src public php.ini"
check "get_defined_functions" \
  "grep -rn 'get_defined_functions' src public"
check "Slim setArgument / setArguments" \
  "grep -rn -E 'setArgument[s]?\(' src public"
check "null comme array offset (approx)" \
  "grep -rn -E '\[null\]' src public"
check "Backtick shell_exec alias" \
  "grep -rn -E '^[^\"/*#]*\`' src public | grep -v '//' || true"

cat "$out"
