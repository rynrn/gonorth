import importlib.util
import json
import subprocess
from collections import Counter
from pathlib import Path
root=Path.cwd()
spec=importlib.util.spec_from_file_location("s",root/"tools"/"scan-secrets.py")
s=importlib.util.module_from_spec(spec); spec.loader.exec_module(s)
def git(*args):
    return subprocess.run(["git","-c",f"safe.directory={root.as_posix()}",*args],cwd=root,capture_output=True,check=True).stdout
raw_ids=set()
for line in git("rev-list","--objects","--all").splitlines():
    oid=line.split(b" ",1)[0].decode("ascii")
    if git("cat-file","-t",oid).strip()==b"blob": raw_ids.add(oid)
head=set()
for line in git("ls-tree","-r","--full-tree","HEAD").splitlines():
    cols=line.split(None,4)
    if len(cols)>=3 and cols[1]==b"blob":head.add(cols[2].decode("ascii"))
all_hits=Counter();head_hits=Counter();distinct={k:set() for k in s.RULES}
for oid in raw_ids:
    try: content=git("cat-file","-p",oid).decode("utf-8")
    except UnicodeDecodeError:continue
    for name,pat in s.RULES.items():
        vals=pat.findall(content)
        if vals:
            all_hits[name]+=1
            if oid in head:head_hits[name]+=1
            distinct[name].update(vals)
print(json.dumps({"reachable_blobs":len(raw_ids),"head_blobs":len(head),"history_hit_blobs":dict(all_hits),"head_hit_blobs":dict(head_hits),"distinct_pattern_values":{k:len(v) for k,v in distinct.items() if v}},ensure_ascii=False))
