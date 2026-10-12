#!/usr/bin/env python3
"""Collect a private live WordPress application snapshot for transfer to the owner."""
import glob
import hashlib
import json
import os
from pathlib import Path
import shutil
import subprocess
from datetime import datetime, timezone
import monthly_backup as backup

SITE=Path("/var/www/gonorth")
WORK=Path("/home/claude/.local/share/gonorth-snapshot-transfer")
def now(): return datetime.now(timezone.utc).isoformat()
def wp(*args):
    result=subprocess.run(["wp",*args,"--path="+str(SITE),"--allow-root"],capture_output=True)
    if result.returncode:
        raise RuntimeError("WP-CLI failed; private output withheld")
    return result.stdout.decode("utf-8").strip()
def copy_checked(src,dest):
    if src.is_symlink(): raise RuntimeError("Unexpected source symlink")
    if src.is_dir():
        dest.mkdir(parents=True,exist_ok=True)
        return
    before=src.stat()
    dest.parent.mkdir(parents=True,exist_ok=True)
    shutil.copyfile(src,dest)
    with src.open("rb") as f: original=backup.digest(f)
    with dest.open("rb") as f: copied=backup.digest(f)
    after=src.stat()
    if original!=copied or (before.st_size,before.st_mtime_ns)!=(after.st_size,after.st_mtime_ns):
        raise RuntimeError("Source changed during copy; no backup promoted")
def main():
    os.umask(0o077)
    started=now()
    if WORK.exists(): raise RuntimeError("Transfer workspace already exists; inspect first")
    WORK.mkdir(parents=True,mode=0o700)
    source=WORK/"snapshot"
    source.mkdir(mode=0o700)
    version=wp("core","version")
    engines=wp("db","query","SELECT ENGINE,COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE' GROUP BY ENGINE;","--skip-column-names")
    if not engines or any(line.split()[0].lower()!="innodb" for line in engines.splitlines()):
        raise RuntimeError("Nontransactional or unknown table engine; consistent export needs review")
    wp("db","export",str(source/"database.sql"),"--single-transaction","--quick","--skip-lock-tables")
    print(json.dumps({"stage":"database_exported","bytes":(source/"database.sql").stat().st_size}),flush=True)
    paths=sorted((SITE/"wp-content").rglob("*"))
    # Capture every content file; no silent cache/media exclusions.
    for index,src in enumerate(paths):
        copy_checked(src,source/src.relative_to(SITE))
        if index and index%5000==0:
            print(json.dumps({"stage":"copying","entries":index}),flush=True)
    copy_checked(SITE/"wp-config.php",source/"wp-config.php")
    configs=[]
    for name in ["/etc/nginx/nginx.conf","/etc/nginx/mime.types","/etc/nginx/fastcgi_params","/etc/nginx/fastcgi.conf",
                 "/etc/nginx/sites-available/gonorth",*glob.glob("/etc/nginx/snippets/*.conf"),*glob.glob("/etc/nginx/conf.d/*.conf"),
                 *glob.glob("/etc/php/*/fpm/php.ini"),*glob.glob("/etc/php/*/fpm/pool.d/*.conf")]:
        src=Path(name)
        if src.is_file() and not src.is_symlink():
            rel="server-config/"+str(src).lstrip("/")
            copy_checked(src,source/rel); configs.append(rel)
    if "server-config/etc/nginx/sites-available/gonorth" not in configs:
        raise RuntimeError("Required Nginx site configuration missing")
    for name in (".htaccess",".user.ini"):
        if (SITE/name).is_file(): copy_checked(SITE/name,source/name)
    (source/"snapshot.json").write_text(json.dumps({
        "collected_at":started,"collection_finished_at":now(),"wordpress_version":version,
        "database_export_exit_code":0,"table_engines":engines,"server_config":configs,
        "consistency":"Transactional DB dump and per-file copy verification; not atomic whole-server snapshot",
        "restore_note":"Install recorded WordPress version; reissue TLS certificates. OS, TLS private keys and WordPress core are not included."
    },ensure_ascii=False,indent=2),encoding="utf-8")
    print(json.dumps({"stage":"packaging","source_entries":len(paths)}),flush=True)
    result=backup.run(source,WORK/"package")
    print(json.dumps(result),flush=True)
if __name__=="__main__":
    try: main()
    except Exception as e:
        print(json.dumps({"status":"failed","error_type":type(e).__name__,"message":str(e) if isinstance(e,RuntimeError) else "Inspect private staging; do not remove existing backup"}),flush=True)
        raise SystemExit(1)

