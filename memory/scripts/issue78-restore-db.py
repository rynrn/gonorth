"""Isolated GoNorth SQL restore rehearsal. Never logs SQL or credentials."""
from __future__ import annotations
import hashlib
import json
import pathlib
import subprocess
import sys
import time
import zipfile

ROOT = pathlib.Path(r"C:\dev\gonorth")
BACKUP = ROOT / "backups/monthly/current/site.zip"
STATUS = ROOT / "memory/backup-status.json"
CLIENT = ROOT / "memory/work/issue78-restore/runtime/mariadb-10.11.14-winx64/bin/mariadb.exe"
PIPE = "GoNorthIssue78Restore"
DB = "gonorth_recovery"

def run_sql(sql: str) -> str:
    result = subprocess.run(
        [str(CLIENT), "--no-defaults", "--protocol=PIPE", f"--socket={PIPE}",
         "--user=root", "--batch", "--skip-column-names", "-e", sql],
        capture_output=True, timeout=30,
    )
    if result.returncode:
        raise RuntimeError(f"MariaDB metadata query failed, exit={result.returncode}")
    return result.stdout.decode("utf-8", "replace").strip()

def main() -> None:
    started = time.monotonic()
    meta = json.loads(STATUS.read_text(encoding="utf-8-sig"))
    expected = meta["archive_sha256"].lower()
    sha = hashlib.sha256()
    with BACKUP.open("rb") as archive:
        for chunk in iter(lambda: archive.read(1024 * 1024), b""):
            sha.update(chunk)
    if sha.hexdigest() != expected:
        raise RuntimeError("Backup archive checksum mismatch; no SQL import")
    if not CLIENT.is_file():
        raise RuntimeError("Temporary MariaDB client missing")
    if run_sql("SELECT @@skip_networking") != "1":
        raise RuntimeError("Temporary MariaDB networking is not disabled")
    existing = run_sql(f"SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='{DB}'")
    if existing != "0":
        raise RuntimeError("Isolated restore database already exists; no overwrite")
    run_sql(f"CREATE DATABASE {DB} CHARACTER SET utf8mb4")
    streamed = 0
    with zipfile.ZipFile(BACKUP) as zf:
        infos = [x for x in zf.infolist() if x.filename == "database.sql"]
        if len(infos) != 1 or infos[0].file_size < 1000:
            raise RuntimeError("Exactly one nonempty root database.sql is required")
        proc = subprocess.Popen(
            [str(CLIENT), "--no-defaults", "--protocol=PIPE", f"--socket={PIPE}",
             "--user=root", f"--database={DB}", "--batch", "--silent"],
            stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
        )
        try:
            assert proc.stdin is not None
            with zf.open(infos[0]) as source:
                while chunk := source.read(256 * 1024):
                    proc.stdin.write(chunk)
                    streamed += len(chunk)
            proc.stdin.close()
            proc.stdin = None
            stdout, stderr = proc.communicate(timeout=300)
        except Exception:
            proc.kill()
            proc.communicate()
            raise
        if proc.returncode:
            # Do not print the client's SQL error text; it may contain source data.
            raise RuntimeError(f"SQL import failed, exit={proc.returncode}, bytes_streamed={streamed}")
        if stdout:
            raise RuntimeError("Unexpected SQL import output (suppressed)")
    tables = int(run_sql(f"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{DB}'"))
    posts = int(run_sql(f"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{DB}' AND table_name LIKE '%\\_posts'"))
    options = int(run_sql(f"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{DB}' AND table_name LIKE '%\\_options'"))
    users = int(run_sql(f"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{DB}' AND table_name LIKE '%\\_users'"))
    if tables < 10 or posts < 1 or options < 1 or users < 1:
        raise RuntimeError("Imported schema lacks expected WordPress table classes")
    result = {
        "status": "database_import_verified",
        "archive_checksum_verified": True,
        "sql_bytes_streamed": streamed,
        "tables": tables,
        "wp_posts_tables": posts,
        "wp_options_tables": options,
        "wp_users_tables": users,
        "runtime_version": run_sql("SELECT VERSION()"),
        "networking_disabled": True,
        "seconds_to_import_and_check": round(time.monotonic() - started, 3),
        "application_restore_tested": False,
    }
    out = ROOT / "memory/data/2026-10-06-issue-78-isolated-db-restore.json"
    out.write_text(json.dumps(result, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(result, ensure_ascii=False))

if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        print(f"Restore rehearsal failed: {exc}", file=sys.stderr)
        raise SystemExit(1)
