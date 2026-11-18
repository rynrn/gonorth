#!/usr/bin/env bash
set -eu
here=$(CDPATH= cd -- "${0%/*}" && pwd)
node --test "$here/guard.test.js"
# Exercise the actual stdin wrapper too, when jq is available.
if command -v jq >/dev/null 2>&1; then
  printf '{"cwd":"%s","tool_input":{"command":"echo hello"}}' "$here" | bash "$here/guard.sh"
  set +e
  printf '{"cwd":"%s","tool_input":{"command":"gh pr merge 1"}}' "$here" | bash "$here/guard.sh"
  code=$?
  set -e
  test "$code" = 2
else
  echo 'Wrapper integration requires jq; JavaScript guard tests completed.' >&2
fi
