#!/usr/bin/env python3
"""Scan reachable Git blob history for credential patterns without printing values.

This reads all local refs. It does not rewrite history, contact remotes, or prove
that a matched credential is valid. Results name only each rule, affected blob count and match count.
"""

from __future__ import annotations

import subprocess
import sys
from collections import defaultdict
from pathlib import Path

import runpy

BASE = runpy.run_path(str(Path(__file__).with_name('scan-secrets.py')))
ROOT = BASE['ROOT']
RULES = BASE['RULES']


def git(*args: str) -> bytes:
    return subprocess.run(
        ["git", "-c", f"safe.directory={ROOT.as_posix()}", *args],
        cwd=ROOT,
        check=True,
        capture_output=True,
    ).stdout


def main() -> int:
    objects = git("rev-list", "--objects", "--all").splitlines()
    seen: set[str] = set()
    findings: dict[str, int] = defaultdict(int)
    affected: dict[str, int] = defaultdict(int)
    blobs_scanned = 0
    for row in objects:
        oid_bytes = row.partition(b" ")[0]
        oid = oid_bytes.decode("ascii")
        if oid in seen:
            continue
        seen.add(oid)
        if git("cat-file", "-t", oid).strip() != b"blob":
            continue
        blobs_scanned += 1
        data = git("cat-file", "blob", oid)
        try:
            content = data.decode("utf-8")
        except UnicodeDecodeError:
            continue

        for name, pattern in RULES.items():
            count = len(pattern.findall(content))
            if count:
                findings[name] += count
                affected[name] += 1

    print(f"Scanned {blobs_scanned} unique blobs from all local Git refs. Matched values are never printed.")
    if findings:
        print("Historical credential patterns detected (rule, affected blobs, matches):")
        for rule, count in sorted(findings.items()):
            print(f"{rule}\t{affected[rule]}\t{count}")
        return 1
    print("No historical credential patterns detected in reachable local refs.")
    return 0


if __name__ == "__main__":
    sys.exit(main())