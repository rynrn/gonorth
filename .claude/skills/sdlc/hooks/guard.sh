#!/usr/bin/env bash
# Accident guard, not a security boundary. Protect default branches on GitHub.
set -eu
if ! command -v jq >/dev/null 2>&1 || ! command -v node >/dev/null 2>&1; then
  echo 'SDLC guard requires jq and Node; refusing unchecked command.' >&2
  exit 2
fi
payload=$(cat)
if ! printf '%s' "$payload" | jq -e 'type == "object" and (.tool_input.command | type == "string") and (.cwd | type == "string")' >/dev/null; then
  echo 'SDLC guard: invalid hook input.' >&2
  exit 2
fi
here=$(CDPATH= cd -- "${0%/*}" && pwd)
printf '%s' "$payload" | node "$here/guard.js"
