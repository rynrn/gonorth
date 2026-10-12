#!/usr/bin/env python3
"""Verify and promote a downloaded new/ package; requires user approval before invocation."""
import json
import os
from pathlib import Path
import sys
import monthly_backup as backup

ROOT=Path(__file__).resolve().parents[4]/"backups"/"monthly"

def install(root):
    root=backup.clean_path(root)
    new,current,previous=(root/n for n in ("new","current","previous"))
    fd=os.open(root/".lock",os.O_CREAT|os.O_EXCL|os.O_WRONLY,0o600)
    os.close(fd)
    try:
        if previous.exists() or backup.linked(previous):
            raise ValueError("Previous interrupted transfer requires review")
        if set(p.name for p in new.iterdir())!={"site.zip","manifest.json"}:
            raise ValueError("Unexpected downloaded package files")
        result=backup.verify(new)
        if current.exists() or backup.linked(current):
            backup.verify(current)
            current.rename(previous)
        try:
            new.rename(current)
            backup.verify(current)
        except BaseException:
            if previous.exists() and not current.exists(): previous.rename(current)
            raise
        if previous.exists(): backup.remove_previous(root)
        return result
    finally:
        (root/".lock").unlink()

if __name__=="__main__":
    try:
        if sys.argv[1:]!=["--approved-refresh"]:
            raise ValueError("Invoke only for a specifically approved initial backup or refresh")
        m=install(ROOT)
        print(json.dumps({"status":"complete","current":str(ROOT/"current"),
                          "archive_sha256":m["archive_sha256"],"created_at":m["created_at"]}))
    except Exception as e:
        print(json.dumps({"status":"failed","error_type":type(e).__name__,
                          "message":"Transfer not completed; preserve existing backup and inspect."}))
        sys.exit(1)

