import importlib.util, subprocess
from pathlib import Path
r=Path.cwd()
s=importlib.util.spec_from_file_location('scanner',r/'tools/scan-secrets.py')
m=importlib.util.module_from_spec(s);s.loader.exec_module(m)
def git(*args):return subprocess.run(['git','-c',f'safe.directory={r.as_posix()}',*args],cwd=r,capture_output=True,check=True).stdout
for line in git('ls-tree','-r','--full-tree','HEAD').splitlines():
 parts=line.split(b'\t',1)
 if len(parts)!=2:continue
 oid=parts[0].split()[2].decode()
 path=parts[1].decode('utf-8','replace')
 try:content=git('cat-file','-p',oid).decode('utf-8')
 except UnicodeDecodeError:continue
 names=[name for name,pat in m.RULES.items() if pat.search(content)]
 if names:print(path+'\t'+','.join(names))
