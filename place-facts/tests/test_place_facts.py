from __future__ import annotations

import json
from contextlib import closing
import sqlite3
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT))

from place_facts import (  # noqa: E402
    CURRENT_SCHEMA_VERSION,
    PlaceFactsError,
    build_wordpress_sync_package,
    compare_wordpress_projection,
    export_records,
    import_snapshot,
    init_db,
    markdown_review_table,
    monitoring_report,
    normalize_google_response,
    pending_changes,
    places_due,
    record_wordpress_sync_receipt,
    review_changes,
    select_google_place_candidate,
    submit_results,
)


class PlaceFactsIntegrationTest(unittest.TestCase):
    def setUp(self) -> None:
        self.temp = tempfile.TemporaryDirectory()
        self.root = Path(self.temp.name)
        self.db = self.root / "facts.sqlite3"
        self.snapshot = self.root / "snapshot.json"
        payload = {
            "source": "https://gonorth.co.il/wp-json/geodir/v2/places",
            "rows": [
                {
                    "id": 10,
                    "title": "מקום ראשון",
                    "url": "https://gonorth.co.il/places/first/",
                    "canonical": "https://gonorth.co.il/places/first/",
                    "categories": [{"slug": "batei-kafe"}],
                    "street": "רחוב 1",
                    "city": "צפת",
                    "phone": "04-1111111",
                    "website": "https://example.com/",
                    "business_hours": {"raw": '["Mo 08:00-14:00"]'},
                    "practical_checked_at": "2026-09-22T10:00:00+03:00",
                    "google_place_id": "google-10",
                },
                {
                    "id": 20,
                    "title": "מקום שני",
                    "url": "https://gonorth.co.il/places/second/",
                    "categories": [{"slug": "teva"}],
                    "street": "שביל 2",
                    "city": "קצרין",
                    "phone": None,
                    "website": None,
                    "business_hours": None,
                    "practical_checked_at": None,
                    "google_place_id": None,
                },
            ],
        }
        self.snapshot.write_text(
            json.dumps(payload, ensure_ascii=False), encoding="utf-8"
        )
        self.import_result = import_snapshot(self.db, self.snapshot)

    def tearDown(self) -> None:
        self.temp.cleanup()

    def test_import_due_and_exports(self) -> None:
        self.assertEqual(2, self.import_result["inserted"])
        self.assertEqual(2, self.import_result["facts_created"])
        due = places_due(self.db, days=0)
        self.assertEqual([20, 10], [item["id"] for item in due])

        records = export_records(self.db)
        self.assertEqual(2, len(records))
        self.assertEqual("first", records[0]["slug"])
        self.assertEqual("verify_business_status", records[0]["seo_action"])
        markdown = markdown_review_table(records)
        self.assertIn("מקום ראשון", markdown)
        self.assertIn("שינויים ממתינים", markdown)

    def test_existing_database_is_backed_up_before_migration(self) -> None:
        legacy = self.root / "legacy.sqlite3"
        schema = (ROOT / "schema.sql").read_text(encoding="utf-8")
        with closing(sqlite3.connect(legacy)) as connection:
            connection.executescript(schema)
            connection.execute(
                "INSERT INTO metadata (key, value) VALUES ('schema_version', '2')"
            )
            connection.execute(
                "INSERT INTO schema_migrations (version, name, applied_at) VALUES (1, 'Initial schema', '2026-09-01T00:00:00Z')"
            )
            connection.execute(
                "INSERT INTO schema_migrations (version, name, applied_at) VALUES (2, 'Migration ledger', '2026-09-01T00:00:00Z')"
            )
            connection.commit()

        backups = self.root / "backups"
        result = init_db(legacy, backups)
        self.assertEqual([3, 4], result["migrated"])
        self.assertEqual(CURRENT_SCHEMA_VERSION, result["schema_version"])
        self.assertIsNotNone(result["backup"])
        self.assertTrue(Path(result["backup"]).is_file())

        with closing(sqlite3.connect(legacy)) as connection:
            version = connection.execute(
                "SELECT value FROM metadata WHERE key = 'schema_version'"
            ).fetchone()[0]
            migrations = connection.execute(
                "SELECT version FROM schema_migrations ORDER BY version"
            ).fetchall()
        self.assertEqual(str(CURRENT_SCHEMA_VERSION), version)
        self.assertEqual([(1,), (2,), (3,), (4,)], migrations)

    def test_changes_require_review_and_closure_keeps_url_live(self) -> None:
        unchanged = submit_results(
            self.db,
            [
                {
                    "id": 10,
                    "source": "official_website",
                    "source_url": "https://example.com/",
                    "checked_at": "2026-09-23T10:00:00Z",
                    "facts": {
                        "business_status": "unknown",
                        "phone": "04-1111111",
                    },
                }
            ],
        )
        self.assertEqual(1, unchanged["unchanged"])
        self.assertEqual([], pending_changes(self.db))

        changed = submit_results(
            self.db,
            [
                {
                    "id": 10,
                    "source": "manual_review",
                    "checked_at": "2026-09-23T11:00:00Z",
                    "confidence": 0.95,
                    "facts": {
                        "business_status": "permanently_closed",
                        "phone": None,
                    },
                }
            ],
        )
        self.assertEqual(1, changed["with_changes"])
        self.assertEqual(1, changed["closure_reviews"])
        pending = pending_changes(self.db)
        self.assertEqual(2, len(pending))
        closure = next(item for item in pending if item["field"] == "business_status")
        phone = next(item for item in pending if item["field"] == "phone")
        self.assertEqual("keep_url_live_and_review_closure", closure["seo_action"])

        before = export_records(self.db)[0]
        self.assertEqual("unknown", before["business_status"])
        self.assertEqual("04-1111111", before["phone"])

        review_changes(self.db, [closure["id"]], "approve", "Verified closure")
        review_changes(self.db, [phone["id"]], "reject", "Keep until second source")
        after = export_records(self.db)[0]
        self.assertEqual("permanently_closed", after["business_status"])
        self.assertEqual("04-1111111", after["phone"])
        self.assertEqual(
            "keep_url_live_with_status_notice_and_remove_stale_actions",
            after["seo_action"],
        )

    def test_new_submission_updates_existing_pending_change(self) -> None:
        first = {
            "id": 10,
            "source": "manual_review",
            "checked_at": "2026-09-23T11:00:00Z",
            "facts": {"phone": "04-2222222"},
        }
        second = {
            "id": 10,
            "source": "official_website",
            "checked_at": "2026-09-23T12:00:00Z",
            "facts": {"phone": "04-3333333"},
        }
        submit_results(self.db, [first])
        submit_results(self.db, [second])
        pending = pending_changes(self.db)
        self.assertEqual(1, len(pending))
        self.assertEqual("04-3333333", pending[0]["new_value"])

        with closing(sqlite3.connect(self.db)) as connection:
            count = connection.execute(
                "SELECT COUNT(*) FROM place_verifications"
            ).fetchone()[0]
        self.assertEqual(2, count)

    def test_google_response_normalization(self) -> None:
        result = normalize_google_response(
            10,
            {
                "businessStatus": "CLOSED_TEMPORARILY",
                "formattedAddress": "רחוב 1, צפת",
                "nationalPhoneNumber": "04-1111111",
                "websiteUri": "https://example.com/",
                "googleMapsUri": "https://maps.google.com/example",
                "rating": 4.7,
                "userRatingCount": 123,
                "regularOpeningHours": {"weekdayDescriptions": ["Monday"]},
            },
            "2026-09-23T12:00:00Z",
        )
        self.assertEqual("temporarily_closed", result["facts"]["business_status"])
        self.assertEqual("google_places", result["source"])
        self.assertEqual(
            {"weekdayDescriptions": ["Monday"]},
            result["facts"]["regular_hours_json"],
        )
        self.assertEqual(4.7, result["facts"]["google_rating"])
        self.assertEqual(123, result["facts"]["google_review_count"])

    def test_google_place_id_mapping_requires_review(self) -> None:
        submit_results(
            self.db,
            [
                {
                    "id": 20,
                    "source": "google_places",
                    "source_url": "https://maps.google.com/second",
                    "checked_at": "2026-09-23T13:00:00Z",
                    "facts": {
                        "google_place_id": "google-20",
                        "google_maps_url": "https://maps.google.com/second",
                    },
                }
            ],
        )
        changes = pending_changes(self.db)
        self.assertEqual(
            {"google_place_id", "google_maps_url"},
            {item["field"] for item in changes},
        )
        review_changes(self.db, [item["id"] for item in changes], "approve")
        record = next(item for item in export_records(self.db) if item["id"] == 20)
        self.assertEqual("google-20", record["google_place_id"])
        self.assertEqual("https://maps.google.com/second", record["google_maps_url"])

    def test_google_candidate_selection_rejects_ambiguous_results(self) -> None:
        place = {"name": "קפה צפון", "city": "צפת"}
        accepted, score, status = select_google_place_candidate(
            place,
            [{"id": "a", "displayName": {"text": "קפה צפון"}, "formattedAddress": "צפת, ישראל"}],
        )
        self.assertEqual("accepted", status)
        self.assertEqual("a", accepted["id"])
        self.assertGreaterEqual(score, 0.92)
        ambiguous, _, status = select_google_place_candidate(
            place,
            [
                {"id": "a", "displayName": {"text": "קפה צפון גליל"}, "formattedAddress": "צפת, ישראל"},
                {"id": "b", "displayName": {"text": "קפה צפון העתיקה"}, "formattedAddress": "צפת, ישראל"},
            ],
        )
        self.assertIsNone(ambiguous)
        self.assertEqual("ambiguous", status)

    def test_monitoring_report_flags_due_and_pending_work(self) -> None:
        report = monitoring_report(self.db, days=0)
        self.assertEqual(2, report["places"])
        self.assertEqual(2, report["due_for_verification"])
        self.assertTrue(report["needs_attention"])
        self.assertEqual(
            {"complete": 0, "google": 0, "alternative": 0, "total": 2},
            report["verified_source_coverage"],
        )
        self.assertEqual({}, report["verification_source_breakdown"])
        self.assertEqual({"with_id": 1, "total": 2}, report["google_place_id_coverage"])

        submit_results(
            self.db,
            [
                {
                    "id": 10,
                    "source": "google_places",
                    "checked_at": "2026-09-23T10:00:00Z",
                    "facts": {"business_status": "unknown"},
                },
                {
                    "id": 20,
                    "source": "official_directory",
                    "source_url": "https://example.org/place/20",
                    "checked_at": "2026-09-23T10:00:00Z",
                    "facts": {"business_status": "unknown"},
                },
            ],
        )
        report = monitoring_report(self.db, days=90)
        self.assertEqual(
            {"complete": 2, "google": 1, "alternative": 1, "total": 2},
            report["verified_source_coverage"],
        )
        self.assertEqual(
            {"google_places": 1, "official_directory": 1},
            report["verification_source_breakdown"],
        )

    def test_wordpress_projection_reconciliation_is_read_only(self) -> None:
        matching = [
            {
                "id": 10,
                "address": "רחוב 1",
                "city": "צפת",
                "phone": "04-1111111",
                "website": "https://example.com/",
                "business_hours": '["Mo 08:00-14:00"],["UTC":"+0","Timezone":"UTC"]',
                "google": {"place_id": "google-10"},
            },
            {
                "id": 20,
                "address": "שביל 2",
                "city": "קצרין",
                "phone": None,
                "website": None,
                "business_hours": None,
                "google": {"place_id": None},
            },
        ]
        report = compare_wordpress_projection(self.db, matching)
        self.assertEqual(0, report["mismatch_count"])
        self.assertEqual(16, report["matched_fields"])

        matching[0]["phone"] = "04-2222222"
        report = compare_wordpress_projection(self.db, matching)
        self.assertEqual(1, report["mismatch_count"])
        self.assertEqual("phone", report["mismatches"][0]["field"])
        self.assertEqual("04-1111111", export_records(self.db)[0]["phone"])

    def test_approved_changes_export_a_wordpress_sync_package(self) -> None:
        submit_results(
            self.db,
            [
                {
                    "id": 10,
                    "source": "official_website",
                    "source_url": "https://example.com/",
                    "checked_at": "2026-09-24T12:00:00Z",
                    "facts": {"phone": "04-2222222"},
                }
            ],
        )
        change = pending_changes(self.db)[0]
        review_changes(self.db, [change["id"]], "approve", "Verified")
        package = build_wordpress_sync_package(self.db)
        self.assertEqual("gonorth-place-facts-wordpress-sync/v1", package["format"])
        self.assertEqual(1, package["summary"]["places"])
        self.assertEqual([change["id"]], package["changes"][0]["change_ids"])
        self.assertEqual("04-1111111", package["changes"][0]["expected"]["phone"])
        self.assertEqual("04-2222222", package["changes"][0]["updates"]["phone"])
        self.assertTrue(package["changes"][0]["verification"]["practical_verified"])
        receipt = {
            "format": "gonorth-place-facts-wordpress-sync-receipt/v1",
            "results": [
                {
                    "change_ids": package["changes"][0]["change_ids"],
                    "status": "synced",
                    "message": "Applied after dry run",
                }
            ],
        }
        self.assertEqual({"synced": 1, "errors": 0, "deferred": 0}, record_wordpress_sync_receipt(self.db, receipt))
        self.assertEqual(0, build_wordpress_sync_package(self.db)["summary"]["change_count"])


    def test_validation_rejects_ambiguous_timestamps_and_statuses(self) -> None:
        with self.assertRaises(PlaceFactsError):
            submit_results(
                self.db,
                [
                    {
                        "id": 10,
                        "source": "manual",
                        "checked_at": "2026-09-23 12:00:00",
                        "facts": {"business_status": "closed"},
                    }
                ],
            )


if __name__ == "__main__":
    unittest.main()



