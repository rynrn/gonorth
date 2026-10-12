from pathlib import Path
import hashlib, json, os, shutil
p=Path("/home/claude/.local/share/gonorth-snapshot-transfer")
expected="0d4a8e6628c5ae0dcb9e896efa0628d7de528e2e52d75c4e55abc9a64449d3d9"
if p.is_symlink() or p.resolve()!=p or p.stat().st_uid!=os.getuid():
    raise RuntimeError("Unsafe cleanup target")
archive=p/"package/current/site.zip"
h=hashlib.sha256()
with archive.open("rb") as f:
    for block in iter(lambda:f.read(1024*1024),b""): h.update(block)
if h.hexdigest()!=expected: raise RuntimeError("Server archive differs from verified local copy")
if not (p/"snapshot/wp-content").is_dir(): raise RuntimeError("Unexpected staging layout")
shutil.rmtree(p)
print(json.dumps({"temporary_server_cleanup":"complete","path":str(p),"verified_local_sha256":expected}))

