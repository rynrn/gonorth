"""Guarded restoration of the exact pre-#84 plugin files. Run from repo root."""
from pathlib import Path
import hashlib
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[2]
SNAPSHOT = ROOT / "memory/data/2026-10-06-issue-84-prechange"
REMOTE_ROOT = "/var/www/gonorth/wp-content/plugins/gonorth-place-data"
FILES = {
    "assets/place-analytics.js": (
        "b22c00a2e3f320d3ded70f3745ced981d773fc3730248eed5b3976d5d0e26c4c",
        "d492049f0451b63f9f5296b34d60c933bd8f0de4ade72d6cf94b71b5332cb24a",
    ),
    "includes/class-gonorth-place-analytics.php": (
        "d6000e236350dafb094bdf699836427cb937c1b7c9d697160d665daabcc6da6e",
        "f51633c9c5d1ce2ba4bf69c7383e30cc44361cdfe0239db220fff9cd386a0b57",
    ),
    "gonorth-place-data.php": (
        "494fe9ae5f15d1488ded1a43e17b37f6b69f17dc59049053269f85a0e7cad16f",
        "db0b91abf09fa0f7b82cfa5b073910e210ab94e812da9b1ccffc4b22d7d40b0a",
    ),
}
TEMPDIR = "/tmp/gonorth-issue84-rollback"

def run(*args):
    return subprocess.run(args, check=True, text=True, capture_output=True).stdout.strip()

def sha256(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()

def main():
    if len(sys.argv) != 2 or sys.argv[1] != "--restore":
        raise SystemExit("Usage: python memory/scripts/issue84-rollback.py --restore")
    for rel, (old, new) in FILES.items():
        source = SNAPSHOT / Path(rel).name
        if sha256(source) != old:
            raise SystemExit(f"Rollback source hash mismatch: {rel}")
        remote = f"{REMOTE_ROOT}/{rel}"
        actual = run("ssh", "gonorth", "sha256sum", remote).split()[0]
        if actual != new:
            raise SystemExit(f"Production hash changed since #84 deployment; refusing rollback: {rel}")
    run("ssh", "gonorth", "mkdir", "-p", TEMPDIR)
    for rel, (old, _) in FILES.items():
        source = SNAPSHOT / Path(rel).name
        staged = f"{TEMPDIR}/{source.name}"
        run("scp", str(source), f"gonorth:{staged}")
        if run("ssh", "gonorth", "sha256sum", staged).split()[0] != old:
            raise SystemExit(f"Transfer hash mismatch: {rel}")
        if rel.endswith(".php"):
            run("ssh", "gonorth", "php", "-l", staged)
    for rel, (old, _) in FILES.items():
        remote = f"{REMOTE_ROOT}/{rel}"
        staged = f"{TEMPDIR}/{Path(rel).name}"
        run("ssh", "gonorth", "cp", staged, remote)
        if run("ssh", "gonorth", "sha256sum", remote).split()[0] != old:
            raise SystemExit(f"Restored hash mismatch: {rel}")
    run("ssh", "gonorth", "wp", "cache", "flush", "--path=/var/www/gonorth", "--allow-root")
    print("Restored exact pre-#84 files and verified all hashes.")

if __name__ == "__main__":
    main()
