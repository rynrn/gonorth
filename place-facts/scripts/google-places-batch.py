#!/usr/bin/env python3
"""Retired entry point for the GoNorth Google verification flow.

The previous script read credentials from unrelated files and updated SQLite
directly. Use place-facts.py instead: it reads a key only from an environment
variable, emits reviewable JSON, and requires submit plus explicit approval.
"""

from __future__ import annotations

import sys


def main() -> int:
    sys.stderr.write(
        "This script is retired. Use place-facts/scripts/place-facts.py "
        "find-google-place-ids or fetch-google, then submit and review results.\n"
    )
    return 2


if __name__ == "__main__":
    raise SystemExit(main())
