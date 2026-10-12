import importlib.util
import json
import subprocess
from collections import Counter
from pathlib import Path

root = Path.cwd()
spec = importlib.util.spec_from_file_location("scan_secrets", root / "tools" / "scan-secrets.py")
mod = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)
def git(*args):
    return subprocess.run(["git", "-c", f"safe.directory={root.as_posix()}", *args], cwd=root, capture_output=True, check=True).stdout
objects = git("rev-list", "--objects", "--all").splitlines()
totals = Counter()
affected = Counter()
scanned = 0
skipped = 0
for line in objects:
    oid = line.split(b" ", 1)[0].decode("ascii")
    if git("cat-file", "-t", oid).strip() != b"blob":
        continue
    raw = git("cat-file", "-p", oid)
    try:
        content = raw.decode("utf-8")
    except UnicodeDecodeError:
        skipped += 1
        continue
    scanned += 1
    for name, pattern in mod.RULES.items():
        count = len(pattern.findall(content))
        if count:
            totals[name] += count
            affected[name] += 1
print(json.dumps({"git_objects":len(objects),"text_blobs_scanned":scanned,"non_utf8_blobs_skipped":skipped,"rule_hits_by_blob":dict(affected),"pattern_occurrences":dict(totals)},ensure_ascii=False))
