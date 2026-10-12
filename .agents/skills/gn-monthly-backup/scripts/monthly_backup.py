#!/usr/bin/env python3
"""Package an already collected WordPress snapshot; verify before rotating one copy."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import shutil
import stat
import sys
import zipfile
from datetime import datetime, timezone

MARKER = "gonorth-monthly-v1"

def timestamp():
    return datetime.now(timezone.utc).isoformat()

def linked(p):
    return p.is_symlink() or (hasattr(p, "is_junction") and p.is_junction())

def clean_path(p):
    p = Path(p).absolute()
    if any(linked(x) for x in (p, *p.parents)):
        raise ValueError("Symbolic links or junctions are not permitted")
    return p.resolve()

def digest(stream):
    h = hashlib.sha256()
    for chunk in iter(lambda: stream.read(1024 * 1024), b""):
        h.update(chunk)
    return h.hexdigest()

def inventory(source):
    result = {}
    for p in sorted(source.rglob("*")):
        if linked(p):
            raise ValueError("Source contains a link; account for its target explicitly")
        if p.is_dir():
            result[p.relative_to(source).as_posix() + "/"] = None
            continue
        if not p.is_file():
            raise ValueError("Source contains a non-regular file")
        with p.open("rb") as f:
            result[p.relative_to(source).as_posix()] = digest(f)
    for name in ("database.sql", "wp-config.php", "snapshot.json"):
        if name not in result or (source / name).stat().st_size == 0:
            raise ValueError("Required snapshot file missing or empty: " + name)
    for name in ("themes", "plugins", "uploads"):
        if not (source / "wp-content" / name).is_dir():
            raise ValueError("Required wp-content directory missing: " + name)
    snapshot = json.loads((source / "snapshot.json").read_text(encoding="utf-8"))
    if snapshot.get("database_export_exit_code") != 0:
        raise ValueError("Successful database export receipt required")
    if not snapshot.get("wordpress_version") or not isinstance(snapshot.get("server_config"), list):
        raise ValueError("WordPress version and explicit server config inventory required")
    for name in snapshot["server_config"]:
        if not name.startswith("server-config/") or name not in result:
            raise ValueError("Declared server configuration missing from snapshot")
    return result

def verify(folder):
    folder = clean_path(folder)
    for name in ("manifest.json", "site.zip"):
        if linked(folder / name) or not (folder / name).is_file():
            raise ValueError("Missing or linked backup artifact")
    m = json.loads((folder / "manifest.json").read_text(encoding="utf-8"))
    if m.get("format") != MARKER or not m.get("files"):
        raise ValueError("Unrecognized backup manifest")
    with (folder / "site.zip").open("rb") as f:
        if digest(f) != m["archive_sha256"]:
            raise ValueError("Archive checksum mismatch")
    with zipfile.ZipFile(folder / "site.zip") as z:
        if len(z.namelist()) != len(set(z.namelist())) or set(z.namelist()) != set(m["files"]):
            raise ValueError("Archive inventory mismatch")
        for name, expected in m["files"].items():
            if name.startswith("/") or "\\" in name or ".." in name.split("/") or ":" in name:
                raise ValueError("Unsafe archive path")
            if expected is None:
                if not z.getinfo(name).is_dir():
                    raise ValueError("Directory entry mismatch")
                continue
            with z.open(name) as f:
                if digest(f) != expected:
                    raise ValueError("File checksum mismatch")
    return m

def remove_previous(root):
    # Called only after current has been verified. Never delete outside this exact child.
    p = root / "previous"
    if clean_path(p).parent != root or not p.is_dir():
        raise ValueError("Unexpected previous backup location")
    if set(x.name for x in p.iterdir()) != {"manifest.json", "site.zip"}:
        raise ValueError("Unexpected files in previous; preserve for review")
    verify(p)
    shutil.rmtree(p)

def run(source, root):
    source, root = clean_path(source), clean_path(root)
    if root == source or root in source.parents or source in root.parents:
        raise ValueError("Source and destination must be separate trees")
    root.mkdir(mode=0o700, parents=True, exist_ok=True)
    if os.name == "posix" and stat.S_IMODE(root.stat().st_mode) & 0o077:
        raise ValueError("Backup root must have mode 0700")
    if linked(root / ".lock"):
        raise ValueError("Linked lock file")
    fd = os.open(root / ".lock", os.O_CREAT | os.O_EXCL | os.O_WRONLY, 0o600)
    os.close(fd)
    try:
        current, new, previous = (root / n for n in ("current", "new", "previous"))
        if any(p.exists() or linked(p) for p in (new, previous)):
            raise ValueError("Interrupted run detected; preserve and inspect new/previous first")
        now = timestamp()
        if current.exists() or linked(current):
            old = verify(current)
            if old["created_at"][:7] == now[:7]:
                return {"status": "skipped", "reason": "valid backup exists for this UTC month"}
        files = inventory(source)
        new.mkdir(mode=0o700)
        archive = new / "site.zip"
        with zipfile.ZipFile(archive, "w", zipfile.ZIP_DEFLATED, allowZip64=True) as z:
            for name in files:
                z.write(source / name, name)
        if inventory(source) != files:
            raise ValueError("Snapshot changed while packaging; previous retained")
        with archive.open("rb") as f:
            archive_hash = digest(f)
        manifest = {"format": MARKER, "created_at": now, "files": files,
                    "archive_sha256": archive_hash, "database_restore_tested": False}
        (new / "manifest.json").write_text(json.dumps(manifest, indent=2), encoding="utf-8")
        for p in new.iterdir():
            p.chmod(0o600)
        verify(new)
        had_previous = current.exists()
        if had_previous:
            current.rename(previous)
        try:
            new.rename(current)
            verify(current)
        except BaseException:
            if had_previous and not current.exists():
                previous.rename(current)
            raise
        if had_previous:
            remove_previous(root)
        return {"status": "complete", "files": sum(v is not None for v in files.values()),
                "directories": sum(v is None for v in files.values()),
                "bytes": archive.stat().st_size if archive.exists() else (current / "site.zip").stat().st_size,
                "current": str(current), "database_restore_tested": False}
    finally:
        (root / ".lock").unlink()

def main():
    p = argparse.ArgumentParser(description=__doc__)
    sub = p.add_subparsers(dest="command", required=True)
    create = sub.add_parser("create")
    create.add_argument("--source", required=True, type=Path)
    create.add_argument("--root", required=True, type=Path)
    check = sub.add_parser("verify")
    check.add_argument("folder", type=Path)
    a = p.parse_args()
    if a.command == "create":
        result = run(a.source, a.root)
    else:
        m = verify(a.folder)
        result = {"status": "verified", "files": len(m["files"]),
                  "database_restore_tested": m["database_restore_tested"]}
    print(json.dumps(result, ensure_ascii=False))

if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        print(json.dumps({"status": "failed", "error_type": type(e).__name__,
                          "message": "Backup stopped; inspect private artifacts. Do not delete the old backup."}))
        sys.exit(1)

