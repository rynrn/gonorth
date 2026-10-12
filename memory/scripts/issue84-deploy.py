"""Deploy #84 staged plugin files with hash checks and automatic file restoration."""
from pathlib import Path
import hashlib
import subprocess

root = Path(__file__).resolve().parents[2]
plugin = root / "wordpress/plugins/gonorth-place-data"
snapshot = root / "memory/data/2026-10-06-issue-84-prechange"
remote_root = "/var/www/gonorth/wp-content/plugins/gonorth-place-data"
stage = "/tmp/gonorth-issue84-stage"
files = {
    "assets/place-analytics.js": ("b22c00a2e3f320d3ded70f3745ced981d773fc3730248eed5b3976d5d0e26c4c", "d492049f0451b63f9f5296b34d60c933bd8f0de4ade72d6cf94b71b5332cb24a"),
    "includes/class-gonorth-place-analytics.php": ("d6000e236350dafb094bdf699836427cb937c1b7c9d697160d665daabcc6da6e", "f51633c9c5d1ce2ba4bf69c7383e30cc44361cdfe0239db220fff9cd386a0b57"),
    "gonorth-place-data.php": ("494fe9ae5f15d1488ded1a43e17b37f6b69f17dc59049053269f85a0e7cad16f", "db0b91abf09fa0f7b82cfa5b073910e210ab94e812da9b1ccffc4b22d7d40b0a"),
}
def run(*args):
    return subprocess.run(args, check=True, text=True, capture_output=True).stdout.strip()
def local_hash(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()
def remote_hash(path):
    return run("ssh", "gonorth", "sha256sum", path).split()[0]
def main():
    for rel, (old, new) in files.items():
        if local_hash(plugin / rel) != new or local_hash(snapshot / Path(rel).name) != old:
            raise RuntimeError("Local release or snapshot changed: " + rel)
        if remote_hash(f"{remote_root}/{rel}") != old:
            raise RuntimeError("Production changed since snapshot; stop: " + rel)
        if remote_hash(f"{stage}/{Path(rel).name}") != new:
            raise RuntimeError("Staging changed; stop: " + rel)
    for rel in files:
        if rel.endswith(".php"):
            run("ssh", "gonorth", "php", "-l", f"{stage}/{Path(rel).name}")
    try:
        for rel, (_, new) in files.items():
            run("ssh", "gonorth", "cp", f"{stage}/{Path(rel).name}", f"{remote_root}/{rel}")
            if remote_hash(f"{remote_root}/{rel}") != new:
                raise RuntimeError("Production hash mismatch after copy: " + rel)
    except Exception:
        for rel, (old, _) in files.items():
            source = snapshot / Path(rel).name
            temp = f"/tmp/gonorth-issue84-restore-{source.name}"
            run("scp", str(source), f"gonorth:{temp}")
            if remote_hash(temp) != old:
                raise RuntimeError("Emergency rollback transfer failed: " + rel)
            run("ssh", "gonorth", "cp", temp, f"{remote_root}/{rel}")
        raise
    run("ssh", "gonorth", "wp", "cache", "flush", "--path=/var/www/gonorth", "--allow-root")
    print("Deployed #84; all three production hashes verified; WordPress cache flushed.")
if __name__ == "__main__":
    main()
