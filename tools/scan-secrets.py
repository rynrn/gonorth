#!/usr/bin/env python3
"""Fail when tracked files contain common credential formats.

Only file names, rule names and counts are printed. Matched values are never
included in output.
"""

from __future__ import annotations

import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
RULES = {
    "private_key": re.compile(r"-----BEGIN (?:RSA |OPENSSH |EC |DSA )?PRIVATE KEY-----"),
    "google_api_key": re.compile(r"AIza[0-9A-Za-z_-]{30,}"),
    "github_token": re.compile(r"gh[pousr]_[0-9A-Za-z]{20,}"),
    "basic_auth_literal": re.compile(r"(?i)(?:authorization\s*[:=]\s*[^\r\n]{0,30})?basic\s+[A-Za-z0-9+/]{20,}={0,2}"),
    "wordpress_app_password": re.compile(
        r"(?i)(?:password|app(?:lication)?[_ -]?password).{0,40}"
        r"[A-Za-z0-9]{4}(?:\s+[A-Za-z0-9]{4}){5}"
    ),
    "credential_assignment": re.compile(
        r"""(?ix)
        ["']?(?:password|passwd|secret|api[_-]?key|access[_-]?token)["']?
        \s*[:=]\s*
        ["'](?!\$\{[A-Z_][A-Z0-9_]*\}["'])[^"'\r\n]{6,}["']
        """
    ),
}


def tracked_files() -> list[Path]:
    result = subprocess.run(
        ["git", "-c", f"safe.directory={ROOT.as_posix()}", "ls-files", "-z"],
        cwd=ROOT,
        check=True,
        capture_output=True,
    )
    return [ROOT / item.decode("utf-8") for item in result.stdout.split(b"\0") if item]


def main() -> int:
    findings: list[tuple[str, str, int]] = []
    for path in tracked_files():
        if not path.is_file():
            continue
        try:
            content = path.read_text(encoding="utf-8")
        except (UnicodeDecodeError, OSError):
            continue
        for name, pattern in RULES.items():
            count = len(pattern.findall(content))
            if count:
                findings.append((path.relative_to(ROOT).as_posix(), name, count))

    if findings:
        print("Tracked credential patterns detected:")
        for file_name, rule, count in findings:
            print(f"{file_name}\t{rule}\t{count}")
        return 1

    print("No tracked credential patterns detected.")
    return 0


if __name__ == "__main__":
    sys.exit(main())
