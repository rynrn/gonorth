#!/usr/bin/env python3
"""Isolated behavioral rehearsal. All data are synthetic; no SSH or live database."""
import json
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch
import zipfile
import monthly_backup as backup

class Rehearsal(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix="gonorth-backup-rehearsal-")
        self.base = Path(self.temp.name).resolve()
        self.source = self.base / "snapshot"
        self.root = self.base / "monthly"
        for directory in ("wp-content/themes", "wp-content/plugins", "wp-content/uploads", "server-config"):
            (self.source / directory).mkdir(parents=True)
        for name, data in {
            "database.sql": "CREATE TABLE demo (id int); INSERT INTO demo VALUES (1);",
            "wp-config.php": "<?php /* synthetic; no credentials */",
            "wp-content/themes/theme.php": "<?php /* version one */",
            "server-config/site.conf": "# synthetic server configuration",
        }.items():
            (self.source / name).write_text(data, encoding="utf-8")
        (self.source / "snapshot.json").write_text(json.dumps({
            "wordpress_version":"test-only", "database_export_exit_code":0,
            "server_config":["server-config/site.conf"]}), encoding="utf-8")

    def tearDown(self):
        # TemporaryDirectory owns only this explicitly created rehearsal tree.
        self.temp.cleanup()

    def run_month(self, month):
        with patch.object(backup, "timestamp", return_value=month+"-02T00:00:00+00:00"):
            return backup.run(self.source, self.root)

    def old_hash(self):
        return backup.verify(self.root/"current")["archive_sha256"]

    def test_01_create_and_extract_exact_snapshot(self):
        self.assertEqual(self.run_month("2026-01")["status"], "complete")
        target = self.base/"restored"
        with zipfile.ZipFile(self.root/"current/site.zip") as z:
            z.extractall(target)
        for path in self.source.rglob("*"):
            other=target/path.relative_to(self.source)
            self.assertTrue(other.exists(), str(path.relative_to(self.source)))
            if path.is_file():
                self.assertEqual(path.read_bytes(), other.read_bytes())
        self.assertFalse(backup.verify(self.root/"current")["database_restore_tested"])

    def test_02_new_month_replaces_old(self):
        self.run_month("2026-01")
        old=self.old_hash()
        (self.source/"database.sql").write_text("CREATE TABLE second (id int);", encoding="utf-8")
        self.run_month("2026-02")
        self.assertNotEqual(old, self.old_hash())
        self.assertEqual({p.name for p in self.root.iterdir()}, {"current"})

    def test_03_same_month_skips(self):
        self.run_month("2026-01")
        old=self.old_hash()
        self.assertEqual(self.run_month("2026-01")["status"], "skipped")
        self.assertEqual(old, self.old_hash())

    def test_04_empty_database_preserves_old(self):
        self.run_month("2026-01")
        old=self.old_hash()
        (self.source/"database.sql").write_bytes(b"")
        with self.assertRaises(ValueError): self.run_month("2026-02")
        self.assertEqual(old, self.old_hash())

    def test_05_corrupt_new_archive_preserves_old(self):
        self.run_month("2026-01")
        old=self.old_hash()
        original=backup.verify
        def corrupt(folder):
            if folder.name == "new":
                (folder/"site.zip").write_bytes(b"broken")
            return original(folder)
        with patch.object(backup, "verify", side_effect=corrupt):
            with self.assertRaises(ValueError): self.run_month("2026-02")
        self.assertEqual(old, self.old_hash())
        self.assertTrue((self.root/"new").exists())

    def test_06_interrupted_run_is_preserved(self):
        self.run_month("2026-01")
        old=self.old_hash()
        (self.root/"previous").mkdir()
        with self.assertRaises(ValueError): self.run_month("2026-02")
        self.assertEqual(old, self.old_hash())

    def test_07_lock_prevents_concurrent_run(self):
        self.run_month("2026-01")
        (self.root/".lock").write_text("other process")
        with self.assertRaises(FileExistsError): self.run_month("2026-02")
        self.assertEqual((self.root/".lock").read_text(), "other process")

    def test_08_promotion_failure_recovers_current(self):
        self.run_month("2026-01")
        old=self.old_hash()
        original=Path.rename
        def fail_new(p, target):
            if p == self.root/"new": raise OSError("simulated rename failure")
            return original(p, target)
        with patch.object(Path, "rename", fail_new):
            with self.assertRaises(OSError): self.run_month("2026-02")
        self.assertEqual(old, self.old_hash())

    def test_09_delete_failure_keeps_both(self):
        self.run_month("2026-01")
        old=self.old_hash()
        with patch.object(backup.shutil, "rmtree", side_effect=PermissionError("simulated")):
            with self.assertRaises(PermissionError): self.run_month("2026-02")
        backup.verify(self.root/"current")
        self.assertEqual(backup.verify(self.root/"previous")["archive_sha256"], old)

    def test_10_reject_overlap(self):
        with self.assertRaises(ValueError):
            backup.run(self.source, self.source/"monthly")

    def test_11_failed_database_export_receipt(self):
        self.run_month("2026-01")
        old=self.old_hash()
        p=self.source/"snapshot.json"
        data=json.loads(p.read_text())
        data["database_export_exit_code"]=1
        p.write_text(json.dumps(data))
        with self.assertRaises(ValueError): self.run_month("2026-02")
        self.assertEqual(old, self.old_hash())

    def test_12_missing_server_config(self):
        self.run_month("2026-01")
        old=self.old_hash()
        (self.source/"server-config/site.conf").unlink()
        with self.assertRaises(ValueError): self.run_month("2026-02")
        self.assertEqual(old, self.old_hash())


    def test_13_downloaded_package_is_installed_and_rotated(self):
        import install_local
        import shutil
        local=self.base/"local"
        local.mkdir(mode=0o700)
        self.run_month("2026-01")
        shutil.copytree(self.root/"current",local/"new")
        install_local.install(local)
        old=backup.verify(local/"current")["archive_sha256"]
        (self.source/"database.sql").write_text("CREATE TABLE renewed(id int);",encoding="utf-8")
        self.run_month("2026-02")
        shutil.copytree(self.root/"current",local/"new")
        install_local.install(local)
        self.assertNotEqual(old,backup.verify(local/"current")["archive_sha256"])
        self.assertEqual({p.name for p in local.iterdir()},{"current"})

    def test_14_damaged_download_keeps_local_backup(self):
        import install_local
        import shutil
        local=self.base/"local"
        local.mkdir(mode=0o700)
        self.run_month("2026-01")
        shutil.copytree(self.root/"current",local/"new")
        install_local.install(local)
        old=backup.verify(local/"current")["archive_sha256"]
        shutil.copytree(self.root/"current",local/"new")
        (local/"new/site.zip").write_bytes(b"incomplete transfer")
        with self.assertRaises(ValueError):
            install_local.install(local)
        self.assertEqual(old,backup.verify(local/"current")["archive_sha256"])

if __name__ == "__main__":
    unittest.main(verbosity=2)

