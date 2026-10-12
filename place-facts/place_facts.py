"""SQLite-backed verification workflow for GoNorth dynamic place facts."""

from __future__ import annotations

import html
import json
import os
import re
import sqlite3
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from contextlib import contextmanager
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any, Iterable, Sequence

SCHEMA_PATH = Path(__file__).with_name("schema.sql")
BASE_SCHEMA_VERSION = 1
CURRENT_SCHEMA_VERSION = 4
BUSINESS_STATUSES = {
    "operational",
    "temporarily_closed",
    "permanently_closed",
    "unknown",
}
GOOGLE_STATUS_MAP = {
    "OPERATIONAL": "operational",
    "CLOSED_TEMPORARILY": "temporarily_closed",
    "CLOSED_PERMANENTLY": "permanently_closed",
}
PLACE_FIELDS = {"google_place_id", "google_maps_url", "website_url"}
FACT_FIELDS = {
    "business_status",
    "phone",
    "address",
    "city",
    "regular_hours_json",
    "current_hours_json",
    "google_rating",
    "google_review_count",
    "google_checked_at",
}
ALL_DYNAMIC_FIELDS = PLACE_FIELDS | FACT_FIELDS
WORDPRESS_PROJECTION_FIELDS = {
    "address": "address",
    "city": "city",
    "phone": "phone",
    "website_url": "website",
    "regular_hours_json": "business_hours",
    "google_place_id": "google.place_id",
    "google_rating": "google.rating",
    "google_review_count": "google.review_count",
}
WORDPRESS_SYNC_FIELDS = {
    "address": "street",
    "city": "city",
    "phone": "phone",
    "website_url": "website",
    "regular_hours_json": "business_hours",
    "google_place_id": "google_place_id",
    "google_rating": "google_rating",
    "google_review_count": "google_review_count",
}
FIELD_ALIASES = {
    "website": "website_url",
    "google_maps_uri": "google_maps_url",
    "business_hours": "regular_hours_json",
    "regular_hours": "regular_hours_json",
    "current_hours": "current_hours_json",
}
CLOSURE_STATUSES = {"temporarily_closed", "permanently_closed"}


class PlaceFactsError(ValueError):
    """Raised when structured verification input is invalid."""


def utc_now() -> str:
    return datetime.now(timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z")


def normalize_iso_datetime(value: Any, field: str = "checked_at") -> str:
    if not isinstance(value, str) or not value.strip():
        raise PlaceFactsError(f"{field} must be a non-empty ISO-8601 string")
    raw = value.strip().replace("Z", "+00:00")
    try:
        parsed = datetime.fromisoformat(raw)
    except ValueError as exc:
        raise PlaceFactsError(f"{field} is not valid ISO-8601: {value}") from exc
    if parsed.tzinfo is None:
        raise PlaceFactsError(f"{field} must include a timezone")
    return parsed.astimezone(timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z")


@contextmanager
def connect(db_path: str | os.PathLike[str]):
    path = Path(db_path)
    path.parent.mkdir(parents=True, exist_ok=True)
    connection = sqlite3.connect(path)
    connection.row_factory = sqlite3.Row
    connection.execute("PRAGMA foreign_keys = ON")
    connection.execute("PRAGMA journal_mode = WAL")
    connection.execute("PRAGMA busy_timeout = 5000")
    try:
        yield connection
        connection.commit()
    except Exception:
        connection.rollback()
        raise
    finally:
        connection.close()


def _database_integrity(connection: sqlite3.Connection) -> None:
    result = connection.execute("PRAGMA integrity_check").fetchone()[0]
    if result != "ok":
        raise PlaceFactsError(f"SQLite integrity check failed: {result}")


def _backup_database(
    connection: sqlite3.Connection,
    db_path: Path,
    backup_directory: Path,
    version: int,
) -> Path:
    backup_directory.mkdir(parents=True, exist_ok=True)
    timestamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    backup_path = backup_directory / f"{db_path.stem}-v{version}-{timestamp}{db_path.suffix}"
    if backup_path.exists():
        raise PlaceFactsError(f"refusing to overwrite database backup: {backup_path}")
    destination = sqlite3.connect(backup_path)
    try:
        connection.backup(destination)
        _database_integrity(destination)
    finally:
        destination.close()
    return backup_path


def _migration_v2(connection: sqlite3.Connection) -> None:
    """Establish the migration ledger for databases created by schema version 1."""
    connection.execute(
        """
        CREATE TABLE IF NOT EXISTS schema_migrations (
            version INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            applied_at TEXT NOT NULL
        )
        """
    )


def _has_column(connection: sqlite3.Connection, table: str, column: str) -> bool:
    rows = connection.execute(f"PRAGMA table_info({table})").fetchall()
    return any(row["name"] == column for row in rows)


def _migration_v3(connection: sqlite3.Connection) -> None:
    """Track the separate WordPress projection lifecycle for approved facts."""
    if not _has_column(connection, "place_changes", "wordpress_sync_status"):
        connection.execute(
            """
            ALTER TABLE place_changes
            ADD COLUMN wordpress_sync_status TEXT NOT NULL DEFAULT 'pending'
            CHECK (wordpress_sync_status IN ('pending', 'synced', 'deferred', 'error'))
            """
        )
    if not _has_column(connection, "place_changes", "wordpress_synced_at"):
        connection.execute(
            "ALTER TABLE place_changes ADD COLUMN wordpress_synced_at TEXT"
        )
    if not _has_column(connection, "place_changes", "wordpress_sync_note"):
        connection.execute(
            "ALTER TABLE place_changes ADD COLUMN wordpress_sync_note TEXT"
        )
    connection.execute(
        """
        CREATE INDEX IF NOT EXISTS idx_place_changes_wordpress_sync
        ON place_changes (review_status, wordpress_sync_status, id)
        """
    )


def _migration_v4(connection: sqlite3.Connection) -> None:
    """Add the complete, freshness-aware Google rating fact set."""
    if not _has_column(connection, "place_facts", "google_rating"):
        connection.execute("ALTER TABLE place_facts ADD COLUMN google_rating REAL")
    if not _has_column(connection, "place_facts", "google_review_count"):
        connection.execute("ALTER TABLE place_facts ADD COLUMN google_review_count INTEGER")
    if not _has_column(connection, "place_facts", "google_checked_at"):
        connection.execute("ALTER TABLE place_facts ADD COLUMN google_checked_at TEXT")
    connection.execute(
        """
        CREATE INDEX IF NOT EXISTS idx_place_facts_google_checked
        ON place_facts (google_checked_at)
        """
    )


MIGRATIONS: dict[int, tuple[str, Any]] = {
    2: ("Initialize durable schema migration ledger", _migration_v2),
    3: ("Track approved WordPress projection state", _migration_v3),
    4: ("Add complete Google rating and freshness facts", _migration_v4),
}


def init_db(
    db_path: str | os.PathLike[str],
    backup_directory: str | os.PathLike[str] | None = None,
) -> dict[str, Any]:
    """Create or safely migrate the verification ledger.

    Existing databases are backed up once, immediately before a schema upgrade.
    Normal read/write commands call this function too, but do not create backups
    when the schema is already current.
    """
    path = Path(db_path)
    existed = path.exists()
    schema = SCHEMA_PATH.read_text(encoding="utf-8")
    with connect(path) as connection:
        connection.executescript(schema)
        version_row = connection.execute(
            "SELECT value FROM metadata WHERE key = 'schema_version'"
        ).fetchone()
        if version_row is None:
            current_version = BASE_SCHEMA_VERSION
            connection.execute(
                "INSERT INTO metadata (key, value) VALUES ('schema_version', ?)",
                (str(current_version),),
            )
            connection.execute(
                """
                INSERT OR IGNORE INTO schema_migrations (version, name, applied_at)
                VALUES (?, ?, ?)
                """,
                (BASE_SCHEMA_VERSION, "Initial schema", utc_now()),
            )
        else:
            try:
                current_version = int(version_row["value"])
            except (TypeError, ValueError) as exc:
                raise PlaceFactsError("schema_version must be an integer") from exc

        if current_version > CURRENT_SCHEMA_VERSION:
            raise PlaceFactsError(
                "database schema is newer than this version of place-facts"
            )

        backup_path: Path | None = None
        migrated: list[int] = []
        if current_version < CURRENT_SCHEMA_VERSION:
            _database_integrity(connection)
            if existed:
                backup_path = _backup_database(
                    connection,
                    path,
                    Path(backup_directory) if backup_directory else path.parent / "backups",
                    current_version,
                )
            for version in range(current_version + 1, CURRENT_SCHEMA_VERSION + 1):
                try:
                    name, migrate = MIGRATIONS[version]
                except KeyError as exc:
                    raise PlaceFactsError(f"missing schema migration {version}") from exc
                migrate(connection)
                connection.execute(
                    "UPDATE metadata SET value = ? WHERE key = 'schema_version'",
                    (str(version),),
                )
                connection.execute(
                    """
                    INSERT OR REPLACE INTO schema_migrations (version, name, applied_at)
                    VALUES (?, ?, ?)
                    """,
                    (version, name, utc_now()),
                )
                migrated.append(version)
            _database_integrity(connection)

    return {
        "database": str(path.resolve()),
        "schema_version": CURRENT_SCHEMA_VERSION,
        "migrated": migrated,
        "backup": str(backup_path) if backup_path else None,
    }


def _text(value: Any) -> str | None:
    if value is None:
        return None
    normalized = str(value).strip()
    return normalized or None


def _url(value: Any) -> str | None:
    normalized = _text(value)
    if normalized is None:
        return None
    parsed = urllib.parse.urlparse(normalized)
    if parsed.scheme not in {"http", "https"} or not parsed.netloc:
        raise PlaceFactsError(f"invalid URL: {normalized}")
    return normalized


def _business_status(value: Any) -> str:
    normalized = (_text(value) or "unknown").lower()
    if normalized not in BUSINESS_STATUSES:
        raise PlaceFactsError(
            "business_status must be operational, temporarily_closed, "
            "permanently_closed, or unknown"
        )
    return normalized


def _hours_json(value: Any) -> str | None:
    if value is None or value == "":
        return None
    parsed: Any = value
    if isinstance(value, str):
        stripped = value.strip()
        if not stripped:
            return None
        try:
            parsed = json.loads(stripped)
        except json.JSONDecodeError:
            parsed = stripped
    return json.dumps(parsed, ensure_ascii=False, sort_keys=True, separators=(",", ":"))


def normalize_field(field: str, value: Any) -> Any:
    canonical = FIELD_ALIASES.get(field, field)
    if canonical == "business_status":
        return _business_status(value)
    if canonical in {"website_url", "google_maps_url"}:
        return _url(value)
    if canonical in {"regular_hours_json", "current_hours_json"}:
        return _hours_json(value)
    if canonical == "google_rating":
        if value is None or value == "":
            return None
        try:
            rating = float(value)
        except (TypeError, ValueError) as exc:
            raise PlaceFactsError("google_rating must be a number from 0 to 5") from exc
        if not 0 <= rating <= 5:
            raise PlaceFactsError("google_rating must be a number from 0 to 5")
        return round(rating, 1)
    if canonical == "google_review_count":
        if value is None or value == "":
            return None
        if isinstance(value, bool):
            raise PlaceFactsError("google_review_count must be a non-negative integer")
        try:
            count = int(value)
        except (TypeError, ValueError) as exc:
            raise PlaceFactsError("google_review_count must be a non-negative integer") from exc
        if count < 0 or str(count) != str(value).strip():
            raise PlaceFactsError("google_review_count must be a non-negative integer")
        return count
    if canonical == "google_checked_at":
        return normalize_iso_datetime(value, "google_checked_at") if value else None
    if canonical in ALL_DYNAMIC_FIELDS:
        return _text(value)
    raise PlaceFactsError(f"unsupported dynamic field: {field}")


def _json_value(value: Any) -> str:
    return json.dumps(value, ensure_ascii=False, sort_keys=True, separators=(",", ":"))


def _from_json_value(value: str | None) -> Any:
    if value is None:
        return None
    return json.loads(value)


def _slug_from_row(row: dict[str, Any]) -> str:
    canonical = _text(row.get("canonical"))
    url = canonical or _text(row.get("url")) or ""
    path = urllib.parse.urlparse(url).path.rstrip("/")
    if path:
        return urllib.parse.unquote(path.split("/")[-1])
    return str(row["id"])


def _categories(row: dict[str, Any]) -> str | None:
    categories = row.get("categories") or row.get("post_category")
    if not categories:
        return None
    values: list[str] = []
    for item in categories:
        if isinstance(item, dict):
            value = item.get("slug") or item.get("name") or item.get("id")
        else:
            value = item
        if value is not None:
            values.append(str(value))
    return ",".join(values) or None


def _audit_hours(row: dict[str, Any]) -> str | None:
    value = row.get("business_hours")
    if isinstance(value, dict) and "raw" in value:
        value = value.get("raw")
    return _hours_json(value)


def load_snapshot(path: str | os.PathLike[str]) -> dict[str, Any]:
    with open(path, "r", encoding="utf-8-sig") as handle:
        payload = json.load(handle)
    if not isinstance(payload, dict):
        raise PlaceFactsError("snapshot root must be an object")
    rows = payload.get("rows") or payload.get("records")
    if not isinstance(rows, list):
        raise PlaceFactsError("snapshot must contain rows or records")
    return payload


def import_snapshot(
    db_path: str | os.PathLike[str],
    snapshot_path: str | os.PathLike[str],
    replace_facts: bool = False,
) -> dict[str, int]:
    init_db(db_path)
    payload = load_snapshot(snapshot_path)
    rows = payload.get("rows") or payload.get("records") or []
    now = utc_now()
    inserted = 0
    updated = 0
    facts_created = 0

    with connect(db_path) as connection:
        for raw in rows:
            if not isinstance(raw, dict) or not raw.get("id"):
                raise PlaceFactsError("every snapshot row must contain an id")
            place_id = int(raw["id"])
            name_raw = _text(raw.get("title") or raw.get("name"))
            name = html.unescape(name_raw) if name_raw else None
            url = _url(raw.get("url") or raw.get("link"))
            if not name or not url:
                raise PlaceFactsError(f"place {place_id} is missing name or URL")
            existing = connection.execute(
                "SELECT 1 FROM places WHERE id = ?", (place_id,)
            ).fetchone()
            google_place_id = _text(raw.get("google_place_id"))
            google_maps_url = (
                "https://www.google.com/maps/place/?q=place_id:"
                + urllib.parse.quote(google_place_id)
                if google_place_id
                else None
            )
            connection.execute(
                """
                INSERT INTO places (
                    id, wordpress_id, slug, name, url, google_place_id,
                    google_maps_url, website_url, category, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT(id) DO UPDATE SET
                    wordpress_id = excluded.wordpress_id,
                    slug = excluded.slug,
                    name = excluded.name,
                    url = excluded.url,
                    google_place_id = COALESCE(excluded.google_place_id, places.google_place_id),
                    google_maps_url = COALESCE(excluded.google_maps_url, places.google_maps_url),
                    website_url = COALESCE(excluded.website_url, places.website_url),
                    category = COALESCE(excluded.category, places.category),
                    updated_at = excluded.updated_at
                """,
                (
                    place_id,
                    place_id,
                    _slug_from_row(raw),
                    name,
                    url,
                    google_place_id,
                    google_maps_url,
                    _url(raw.get("website")) if raw.get("website") else None,
                    _categories(raw),
                    now,
                    now,
                ),
            )
            inserted += 0 if existing else 1
            updated += 1 if existing else 0

            checked_raw = _text(raw.get("practical_checked_at"))
            checked_at = (
                normalize_iso_datetime(checked_raw, "practical_checked_at")
                if checked_raw
                else None
            )
            incoming = {
                "business_status": _business_status(raw.get("business_status")),
                "phone": _text(raw.get("phone")),
                "address": _text(raw.get("street") or raw.get("address")),
                "city": _text(raw.get("city")),
                "regular_hours_json": _audit_hours(raw),
                "current_hours_json": None,
                "last_checked_at": checked_at,
                "verification_source": "wordpress_snapshot",
                "source_url": _text(payload.get("source")),
                "updated_at": now,
            }
            existing_facts = connection.execute(
                "SELECT * FROM place_facts WHERE place_id = ?", (place_id,)
            ).fetchone()
            if not existing_facts:
                connection.execute(
                    """
                    INSERT INTO place_facts (
                        place_id, business_status, phone, address, city,
                        regular_hours_json, current_hours_json, last_checked_at,
                        verification_source, source_url, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    """,
                    (place_id, *incoming.values()),
                )
                facts_created += 1
            elif replace_facts:
                connection.execute(
                    """
                    UPDATE place_facts SET
                        business_status = ?, phone = ?, address = ?, city = ?,
                        regular_hours_json = ?, current_hours_json = ?,
                        last_checked_at = ?, verification_source = ?,
                        source_url = ?, updated_at = ?
                    WHERE place_id = ?
                    """,
                    (*incoming.values(), place_id),
                )
    return {
        "places": len(rows),
        "inserted": inserted,
        "updated": updated,
        "facts_created": facts_created,
    }


def _parse_db_datetime(value: str | None) -> datetime | None:
    if not value:
        return None
    return datetime.fromisoformat(value.replace("Z", "+00:00")).astimezone(timezone.utc)


def places_due(
    db_path: str | os.PathLike[str],
    days: int = 90,
    limit: int | None = None,
    google_only: bool = False,
) -> list[dict[str, Any]]:
    if days < 0:
        raise PlaceFactsError("days must be zero or greater")
    cutoff = datetime.now(timezone.utc) - timedelta(days=days)
    query = """
        SELECT p.id, p.wordpress_id, p.slug, p.name, p.url, p.google_place_id,
               p.google_maps_url, p.website_url, p.category,
               f.business_status, f.last_checked_at,
               COUNT(c.id) AS pending_changes
        FROM places p
        JOIN place_facts f ON f.place_id = p.id
        LEFT JOIN place_changes c
          ON c.place_id = p.id AND c.review_status = 'pending'
    """
    if google_only:
        query += " WHERE p.google_place_id IS NOT NULL AND p.google_place_id != ''"
    query += " GROUP BY p.id ORDER BY f.last_checked_at IS NOT NULL, f.last_checked_at, p.id"

    with connect(db_path) as connection:
        rows = connection.execute(query).fetchall()
    result = []
    for row in rows:
        checked = _parse_db_datetime(row["last_checked_at"])
        if checked is not None and checked > cutoff:
            continue
        item = dict(row)
        item["seo_action"] = seo_action(item["business_status"])
        result.append(item)
        if limit is not None and len(result) >= limit:
            break
    return result


def _current_record(connection: sqlite3.Connection, place_id: int) -> sqlite3.Row:
    row = connection.execute(
        """
        SELECT p.*, f.business_status, f.phone, f.address, f.city,
               f.regular_hours_json, f.current_hours_json,
               f.google_rating, f.google_review_count, f.google_checked_at,
               f.last_checked_at, f.verification_source, f.source_url
        FROM places p
        JOIN place_facts f ON f.place_id = p.id
        WHERE p.id = ?
        """,
        (place_id,),
    ).fetchone()
    if row is None:
        raise PlaceFactsError(f"unknown place id: {place_id}")
    return row


def _structured_result_fields(result: dict[str, Any]) -> dict[str, Any]:
    facts = result.get("facts", {})
    if facts is None:
        facts = {}
    if not isinstance(facts, dict):
        raise PlaceFactsError("facts must be an object")
    combined = dict(facts)
    for field in ALL_DYNAMIC_FIELDS | set(FIELD_ALIASES):
        if field in result:
            combined[field] = result[field]
    normalized: dict[str, Any] = {}
    for field, value in combined.items():
        canonical = FIELD_ALIASES.get(field, field)
        if canonical in ALL_DYNAMIC_FIELDS:
            normalized[canonical] = normalize_field(canonical, value)
    if not normalized and not result.get("error"):
        raise PlaceFactsError("verification result contains no supported facts")
    return normalized


def submit_results(
    db_path: str | os.PathLike[str],
    results: Sequence[dict[str, Any]],
) -> dict[str, Any]:
    init_db(db_path)
    summary = {
        "processed": 0,
        "unchanged": 0,
        "with_changes": 0,
        "errors": 0,
        "changes_created": 0,
        "closure_reviews": 0,
    }
    received_at = utc_now()

    with connect(db_path) as connection:
        for result in results:
            if not isinstance(result, dict):
                raise PlaceFactsError("each verification result must be an object")
            raw_id = result.get("id", result.get("place_id", result.get("wordpress_id")))
            if raw_id is None:
                raise PlaceFactsError("verification result is missing id")
            place_id = int(raw_id)
            current = _current_record(connection, place_id)
            source = _text(result.get("source"))
            if not source:
                raise PlaceFactsError(f"place {place_id}: source is required")
            source_url = _url(result.get("source_url")) if result.get("source_url") else None
            checked_at = normalize_iso_datetime(result.get("checked_at"))
            confidence_raw = result.get("confidence")
            confidence = float(confidence_raw) if confidence_raw is not None else None
            if confidence is not None and not 0 <= confidence <= 1:
                raise PlaceFactsError("confidence must be between 0 and 1")

            error_message = _text(result.get("error"))
            normalized_fields = _structured_result_fields(result)
            if error_message:
                connection.execute(
                    """
                    INSERT INTO place_verifications (
                        place_id, source, source_url, checked_at, received_at,
                        confidence, result_json, outcome, error_message
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'error', ?)
                    """,
                    (
                        place_id,
                        source,
                        source_url,
                        checked_at,
                        received_at,
                        confidence,
                        _json_value(result),
                        error_message,
                    ),
                )
                summary["processed"] += 1
                summary["errors"] += 1
                continue

            differences = [
                (field, current[field], new_value)
                for field, new_value in normalized_fields.items()
                if current[field] != new_value
            ]
            outcome = "changes_detected" if differences else "unchanged"
            cursor = connection.execute(
                """
                INSERT INTO place_verifications (
                    place_id, source, source_url, checked_at, received_at,
                    confidence, result_json, outcome, error_message
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)
                """,
                (
                    place_id,
                    source,
                    source_url,
                    checked_at,
                    received_at,
                    confidence,
                    _json_value(result),
                    outcome,
                ),
            )
            verification_id = int(cursor.lastrowid)
            connection.execute(
                """
                UPDATE place_facts SET
                    last_checked_at = ?, verification_source = ?,
                    source_url = ?, updated_at = ?
                WHERE place_id = ?
                """,
                (checked_at, source, source_url, received_at, place_id),
            )

            for field, old_value, new_value in differences:
                existing = connection.execute(
                    """
                    SELECT id FROM place_changes
                    WHERE place_id = ? AND field = ? AND review_status = 'pending'
                    """,
                    (place_id, field),
                ).fetchone()
                values = (
                    verification_id,
                    _json_value(old_value),
                    _json_value(new_value),
                    checked_at,
                    confidence,
                )
                if existing:
                    connection.execute(
                        """
                        UPDATE place_changes SET
                            verification_id = ?, old_value = ?, new_value = ?,
                            detected_at = ?, confidence = ?
                        WHERE id = ?
                        """,
                        (*values, existing["id"]),
                    )
                else:
                    connection.execute(
                        """
                        INSERT INTO place_changes (
                            place_id, verification_id, field, old_value,
                            new_value, detected_at, review_status, confidence
                        ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
                        """,
                        (place_id, verification_id, field, *values[1:]),
                    )
                    summary["changes_created"] += 1
                if field == "business_status" and new_value in CLOSURE_STATUSES:
                    summary["closure_reviews"] += 1

            summary["processed"] += 1
            if differences:
                summary["with_changes"] += 1
            else:
                summary["unchanged"] += 1
    return summary


def _set_field(
    connection: sqlite3.Connection,
    place_id: int,
    field: str,
    value: Any,
    now: str,
) -> None:
    if field in PLACE_FIELDS:
        connection.execute(
            f"UPDATE places SET {field} = ?, updated_at = ? WHERE id = ?",
            (value, now, place_id),
        )
    elif field in FACT_FIELDS:
        connection.execute(
            f"UPDATE place_facts SET {field} = ?, updated_at = ? WHERE place_id = ?",
            (value, now, place_id),
        )
    else:
        raise PlaceFactsError(f"cannot apply unsupported field: {field}")


def review_changes(
    db_path: str | os.PathLike[str],
    change_ids: Iterable[int],
    decision: str,
    note: str | None = None,
) -> dict[str, int]:
    if decision not in {"approve", "reject"}:
        raise PlaceFactsError("decision must be approve or reject")
    ids = [int(item) for item in change_ids]
    if not ids:
        raise PlaceFactsError("at least one change id is required")
    now = utc_now()
    applied = 0
    rejected = 0

    with connect(db_path) as connection:
        for change_id in ids:
            change = connection.execute(
                "SELECT * FROM place_changes WHERE id = ?", (change_id,)
            ).fetchone()
            if change is None:
                raise PlaceFactsError(f"unknown change id: {change_id}")
            if change["review_status"] != "pending":
                raise PlaceFactsError(f"change {change_id} is already reviewed")
            if decision == "approve":
                _set_field(
                    connection,
                    int(change["place_id"]),
                    change["field"],
                    _from_json_value(change["new_value"]),
                    now,
                )
                status = "applied"
                applied += 1
            else:
                status = "rejected"
                rejected += 1
            connection.execute(
                """
                UPDATE place_changes SET
                    review_status = ?, reviewed_at = ?, review_note = ?
                WHERE id = ?
                """,
                (status, now, _text(note), change_id),
            )
    return {"applied": applied, "rejected": rejected}


def seo_action(status: str) -> str:
    if status in CLOSURE_STATUSES:
        return "keep_url_live_with_status_notice_and_remove_stale_actions"
    if status == "unknown":
        return "verify_business_status"
    return "none"


def _pending_changes(connection: sqlite3.Connection, place_id: int) -> list[dict[str, Any]]:
    rows = connection.execute(
        """
        SELECT id, field, old_value, new_value, detected_at, confidence
        FROM place_changes
        WHERE place_id = ? AND review_status = 'pending'
        ORDER BY id
        """,
        (place_id,),
    ).fetchall()
    return [
        {
            "id": row["id"],
            "field": row["field"],
            "old_value": _from_json_value(row["old_value"]),
            "new_value": _from_json_value(row["new_value"]),
            "detected_at": row["detected_at"],
            "confidence": row["confidence"],
        }
        for row in rows
    ]


def export_records(db_path: str | os.PathLike[str]) -> list[dict[str, Any]]:
    with connect(db_path) as connection:
        rows = connection.execute(
            """
            SELECT p.id, p.wordpress_id, p.slug, p.name, p.url,
                   p.google_place_id, p.google_maps_url, p.website_url, p.category,
                   f.business_status, f.phone, f.address, f.city,
                   f.regular_hours_json, f.current_hours_json,
                   f.google_rating, f.google_review_count, f.google_checked_at,
                   f.last_checked_at, f.verification_source, f.source_url
            FROM places p
            JOIN place_facts f ON f.place_id = p.id
            ORDER BY p.id
            """
        ).fetchall()
        result = []
        for row in rows:
            item = dict(row)
            item["seo_action"] = seo_action(item["business_status"])
            item["pending_changes"] = _pending_changes(connection, int(item["id"]))
            result.append(item)
        return result


def _nested_value(record: dict[str, Any], path: str) -> Any:
    value: Any = record
    for segment in path.split("."):
        if not isinstance(value, dict):
            return None
        value = value.get(segment)
    return value


def _projection_hours(value: Any) -> str | None:
    """Normalize ledger and GeoDirectory hours to the weekday-period list.

    GeoDirectory exports its periods followed by a timezone metadata array as
    two adjacent JSON arrays. The ledger may hold either the periods array or
    that raw serialized form, so reconciliation compares the useful schedule
    rather than storage syntax.
    """
    if value is None or value == "":
        return None
    candidate = value
    if isinstance(candidate, str):
        stripped = candidate.strip()
        try:
            decoded = json.loads(stripped)
        except json.JSONDecodeError:
            decoded = stripped
        candidate = decoded
        if isinstance(candidate, str):
            marker = '],['
            if marker in candidate:
                candidate = candidate.split(marker, 1)[0] + ']'
            try:
                candidate = json.loads(candidate)
            except json.JSONDecodeError:
                pass
    return _hours_json(candidate)


def _projection_value(field: str, value: Any) -> Any:
    if field == "regular_hours_json":
        return _projection_hours(value)
    return normalize_field(field, value)


def compare_wordpress_projection(
    db_path: str | os.PathLike[str], wordpress_records: Sequence[dict[str, Any]],
) -> dict[str, Any]:
    """Compare approved SQLite facts with a read-only WordPress export.

    The ledger also tracks status, temporary hours and map URLs; those values
    deliberately have no direct GeoDirectory projection yet and are reported as
    deferred rather than silently treated as a match.
    """
    wp_by_id: dict[int, dict[str, Any]] = {}
    for record in wordpress_records:
        if not isinstance(record, dict) or record.get("id") is None:
            raise PlaceFactsError("every WordPress export record must contain an id")
        wp_by_id[int(record["id"])] = record

    rows = export_records(db_path)
    mismatches: list[dict[str, Any]] = []
    matches = 0
    for row in rows:
        wordpress_id = int(row["wordpress_id"])
        wordpress = wp_by_id.get(wordpress_id)
        if wordpress is None:
            mismatches.append(
                {
                    "place_id": row["id"],
                    "wordpress_id": wordpress_id,
                    "url": row["url"],
                    "field": "record",
                    "sqlite_value": "present",
                    "wordpress_value": None,
                    "reason": "missing_wordpress_record",
                }
            )
            continue
        for field, wordpress_path in WORDPRESS_PROJECTION_FIELDS.items():
            sqlite_value = _projection_value(field, row.get(field))
            wordpress_value = _projection_value(
                field, _nested_value(wordpress, wordpress_path)
            )
            if sqlite_value == wordpress_value:
                matches += 1
                continue
            mismatches.append(
                {
                    "place_id": row["id"],
                    "wordpress_id": wordpress_id,
                    "url": row["url"],
                    "field": field,
                    "sqlite_value": sqlite_value,
                    "wordpress_value": wordpress_value,
                    "reason": "value_mismatch",
                }
            )

    return {
        "places_in_ledger": len(rows),
        "wordpress_records": len(wp_by_id),
        "matched_fields": matches,
        "mismatch_count": len(mismatches),
        "mismatches": mismatches,
        "deferred_fields": [
            "business_status",
            "current_hours_json",
            "google_maps_url",
            "last_checked_at",
        ],
    }


def build_wordpress_sync_package(
    db_path: str | os.PathLike[str],
    change_ids: Iterable[int] | None = None,
) -> dict[str, Any]:
    """Export approved, unsynced facts for a later WordPress dry run.

    The package is only a reviewable artifact. It has no ability to write to
    WordPress, and carries each previously approved value as a precondition so
    that a stale WordPress page cannot be overwritten by a later sync command.
    """
    requested_ids = {int(item) for item in change_ids} if change_ids else None
    with connect(db_path) as connection:
        query = """
            SELECT c.id, c.place_id, c.field, c.old_value, c.new_value,
                   c.detected_at, c.review_status, c.wordpress_sync_status,
                   p.wordpress_id, p.name, p.url,
                   v.source, v.source_url, v.checked_at
            FROM place_changes c
            JOIN places p ON p.id = c.place_id
            JOIN place_verifications v ON v.id = c.verification_id
            WHERE c.review_status = 'applied'
              AND c.wordpress_sync_status = 'pending'
            ORDER BY c.place_id, c.id
        """
        rows = connection.execute(query).fetchall()

    grouped: dict[int, dict[str, Any]] = {}
    deferred: list[dict[str, Any]] = []
    for row in rows:
        if requested_ids is not None and int(row["id"]) not in requested_ids:
            continue
        item = dict(row)
        field = item["field"]
        if field not in WORDPRESS_SYNC_FIELDS:
            deferred.append(
                {
                    "change_id": item["id"],
                    "place_id": item["place_id"],
                    "wordpress_id": item["wordpress_id"],
                    "field": field,
                    "reason": "no_canonical_wordpress_projection",
                    "seo_action": (
                        "manual_seo_review_required"
                        if field == "business_status"
                        else "retain_in_verification_ledger"
                    ),
                }
            )
            continue
        place_id = int(item["place_id"])
        package_item = grouped.setdefault(
            place_id,
            {
                "place_id": place_id,
                "wordpress_id": int(item["wordpress_id"]),
                "name": item["name"],
                "url": item["url"],
                "change_ids": [],
                "expected": {},
                "updates": {},
                "verification": {
                    "source": item["source"],
                    "source_url": item["source_url"],
                    "checked_at": item["checked_at"],
                    "practical_verified": item["source"] != "google_places",
                    "google_verified": item["source"] == "google_places",
                },
            },
        )
        wordpress_field = WORDPRESS_SYNC_FIELDS[field]
        package_item["change_ids"].append(int(item["id"]))
        package_item["expected"][wordpress_field] = _from_json_value(item["old_value"])
        package_item["updates"][wordpress_field] = _from_json_value(item["new_value"])

    return {
        "format": "gonorth-place-facts-wordpress-sync/v1",
        "generated_at": utc_now(),
        "changes": list(grouped.values()),
        "deferred": deferred,
        "summary": {
            "places": len(grouped),
            "change_count": sum(len(item["change_ids"]) for item in grouped.values()),
            "deferred_count": len(deferred),
        },
    }


def record_wordpress_sync_receipt(
    db_path: str | os.PathLike[str], receipt: dict[str, Any]
) -> dict[str, int]:
    """Record a completed WordPress sync receipt without trusting dry runs."""
    if receipt.get("format") != "gonorth-place-facts-wordpress-sync-receipt/v1":
        raise PlaceFactsError("unrecognized WordPress sync receipt format")
    results = receipt.get("results")
    if not isinstance(results, list):
        raise PlaceFactsError("WordPress sync receipt must contain results")
    synced = 0
    errors = 0
    deferred = 0
    now = utc_now()
    with connect(db_path) as connection:
        for result in results:
            if not isinstance(result, dict):
                raise PlaceFactsError("every sync receipt result must be an object")
            status = result.get("status")
            ids = result.get("change_ids", [])
            if status not in {"synced", "error", "deferred"}:
                raise PlaceFactsError("receipt result status must be synced, error, or deferred")
            if not isinstance(ids, list) or not ids:
                raise PlaceFactsError("receipt result must contain one or more change_ids")
            note = _text(result.get("message"))
            for change_id in ids:
                row = connection.execute(
                    "SELECT review_status, wordpress_sync_status FROM place_changes WHERE id = ?",
                    (int(change_id),),
                ).fetchone()
                if row is None:
                    raise PlaceFactsError(f"unknown change id in receipt: {change_id}")
                if row["review_status"] != "applied":
                    raise PlaceFactsError(f"change {change_id} is not approved")
                if row["wordpress_sync_status"] != "pending":
                    raise PlaceFactsError(f"change {change_id} already has a sync result")
                connection.execute(
                    """
                    UPDATE place_changes
                    SET wordpress_sync_status = ?, wordpress_synced_at = ?, wordpress_sync_note = ?
                    WHERE id = ?
                    """,
                    (status, now, note, int(change_id)),
                )
                if status == "synced":
                    synced += 1
                elif status == "error":
                    errors += 1
                else:
                    deferred += 1
    return {"synced": synced, "errors": errors, "deferred": deferred}


def monitoring_report(
    db_path: str | os.PathLike[str], days: int = 90
) -> dict[str, Any]:
    """Produce a read-only operational report suitable for a scheduled run."""
    if days < 0:
        raise PlaceFactsError("days must be zero or greater")
    records = export_records(db_path)
    due = places_due(db_path, days=days)
    pending = pending_changes(db_path)
    google_ids = sum(1 for item in records if item.get("google_place_id"))
    google_ratings = sum(
        1
        for item in records
        if item.get("google_rating") is not None
        and item.get("google_review_count") is not None
        and item.get("google_checked_at")
    )
    verified_records = [
        item
        for item in records
        if item.get("last_checked_at")
        and item.get("verification_source")
        and item.get("verification_source") != "wordpress_snapshot"
    ]
    verified_google = sum(
        1 for item in verified_records if item.get("verification_source") == "google_places"
    )
    verified_alternative = len(verified_records) - verified_google
    source_breakdown: dict[str, int] = {}
    for item in verified_records:
        source = str(item.get("verification_source"))
        source_breakdown[source] = source_breakdown.get(source, 0) + 1
    closure_reviews = [
        item
        for item in pending
        if item["field"] == "business_status"
        and item["new_value"] in CLOSURE_STATUSES
    ]
    return {
        "generated_at": utc_now(),
        "verification_due_days": days,
        "places": len(records),
        "due_for_verification": len(due),
        "pending_changes": len(pending),
        "closure_reviews": len(closure_reviews),
        "verified_source_coverage": {
            "complete": len(verified_records),
            "google": verified_google,
            "alternative": verified_alternative,
            "total": len(records),
        },
        "verification_source_breakdown": dict(sorted(source_breakdown.items())),
        "google_place_id_coverage": {"with_id": google_ids, "total": len(records)},
        "google_rating_coverage": {"complete": google_ratings, "total": len(records)},
        "needs_attention": bool(due or pending),
    }


def markdown_review_table(records: Sequence[dict[str, Any]]) -> str:
    lines = [
        "| ID | מקום | סטטוס | בדיקה אחרונה | שינויים ממתינים | פעולת SEO |",
        "|---:|---|---|---|---:|---|",
    ]
    for item in records:
        def cell(value: Any) -> str:
            return str(value or "-").replace("|", "\\|").replace("\n", " ")

        lines.append(
            "| {id} | {name} | {status} | {checked} | {pending} | {action} |".format(
                id=item["id"],
                name=cell(item["name"]),
                status=cell(item["business_status"]),
                checked=cell(item["last_checked_at"]),
                pending=len(item.get("pending_changes", [])),
                action=cell(item["seo_action"]),
            )
        )
    return "\n".join(lines) + "\n"


def normalize_google_response(
    place_id: int,
    payload: dict[str, Any],
    checked_at: str | None = None,
) -> dict[str, Any]:
    if not isinstance(payload, dict):
        raise PlaceFactsError("Google response must be an object")
    status_raw = _text(payload.get("businessStatus")) or "UNKNOWN"
    status = GOOGLE_STATUS_MAP.get(status_raw, "unknown")
    facts = {
        "business_status": status,
        "phone": payload.get("nationalPhoneNumber")
        or payload.get("internationalPhoneNumber"),
        "address": payload.get("formattedAddress"),
        "website_url": payload.get("websiteUri"),
        "google_maps_url": payload.get("googleMapsUri"),
        "regular_hours_json": payload.get("regularOpeningHours"),
        "current_hours_json": payload.get("currentOpeningHours"),
        "google_checked_at": checked_at or utc_now(),
    }
    if payload.get("rating") is not None:
        facts["google_rating"] = payload["rating"]
    if payload.get("userRatingCount") is not None:
        facts["google_review_count"] = payload["userRatingCount"]
    return {
        "id": int(place_id),
        "source": "google_places",
        "source_url": payload.get("googleMapsUri"),
        "checked_at": checked_at or utc_now(),
        "confidence": 1.0,
        "facts": facts,
    }


def _match_text(value: Any) -> str:
    normalized = unicodedata.normalize("NFKD", _text(value) or "").lower()
    return " ".join(re.sub(r"[^0-9a-z\u0590-\u05ff]+", " ", normalized).split())


def select_google_place_candidate(
    place: dict[str, Any], candidates: Sequence[dict[str, Any]]
) -> tuple[dict[str, Any] | None, float, str]:
    """Choose only unambiguous Google candidates; ambiguous matches stay queued."""
    scores: list[tuple[float, dict[str, Any]]] = []
    place_name = _match_text(place.get("name"))
    place_city = _match_text(place.get("city"))
    for candidate in candidates:
        if not isinstance(candidate, dict) or not candidate.get("id"):
            continue
        candidate_name = _match_text((candidate.get("displayName") or {}).get("text"))
        candidate_address = _match_text(candidate.get("formattedAddress"))
        if not place_name or not candidate_name:
            score = 0.0
        elif place_name == candidate_name:
            score = 1.0
        else:
            from difflib import SequenceMatcher

            score = SequenceMatcher(None, place_name, candidate_name).ratio()
            if place_name in candidate_name or candidate_name in place_name:
                score = max(score, 0.86)
        if place_city and place_city in candidate_address:
            score = min(1.0, score + 0.08)
        scores.append((score, candidate))
    if not scores:
        return None, 0.0, "no_candidates"
    scores.sort(key=lambda item: item[0], reverse=True)
    score, candidate = scores[0]
    runner_up = scores[1][0] if len(scores) > 1 else 0.0
    if score >= 0.99 or (score >= 0.78 and score - runner_up >= 0.12):
        return candidate, score, "accepted"
    return None, score, "ambiguous"


def search_google_place_candidates(
    records: Sequence[dict[str, Any]],
    api_key: str,
    timeout: int = 20,
    retries: int = 2,
    min_delay: float = 0.2,
) -> list[dict[str, Any]]:
    """Return reviewable Place ID candidates without changing the ledger."""
    if not api_key:
        raise PlaceFactsError("Google Places API key is required")
    if retries < 0 or min_delay < 0:
        raise PlaceFactsError("retries and min_delay must not be negative")
    results: list[dict[str, Any]] = []
    for index, item in enumerate(records):
        query = ", ".join(part for part in (item.get("name"), item.get("city"), "ישראל") if part)
        body = json.dumps(
            {"textQuery": query, "languageCode": "he", "regionCode": "IL", "pageSize": 5},
            ensure_ascii=False,
        ).encode("utf-8")
        request = urllib.request.Request(
            "https://places.googleapis.com/v1/places:searchText",
            data=body,
            method="POST",
            headers={
                "X-Goog-Api-Key": api_key,
                "X-Goog-FieldMask": "places.id,places.displayName,places.formattedAddress,places.googleMapsUri",
                "Content-Type": "application/json; charset=utf-8",
                "Accept": "application/json",
                "User-Agent": "GoNorth-Place-Facts/1.0",
            },
        )
        payload: dict[str, Any] | None = None
        error: str | None = None
        for attempt in range(retries + 1):
            try:
                with urllib.request.urlopen(request, timeout=timeout) as response:
                    payload = json.load(response)
                break
            except (urllib.error.URLError, urllib.error.HTTPError, TimeoutError, json.JSONDecodeError) as exc:
                error = str(exc)
                if attempt < retries:
                    time.sleep(min_delay * (2**attempt))
        if payload is None:
            results.append({"id": item["id"], "query": query, "status": "error", "error": error})
        else:
            candidates = payload.get("places", [])
            selected, score, status = select_google_place_candidate(item, candidates)
            results.append(
                {
                    "id": item["id"],
                    "query": query,
                    "status": status,
                    "confidence": round(score, 3),
                    "selected": selected,
                    "candidates": candidates,
                }
            )
        if min_delay and index < len(records) - 1:
            time.sleep(min_delay)
    return results


def fetch_google_results(
    due_records: Sequence[dict[str, Any]],
    api_key: str,
    timeout: int = 20,
) -> list[dict[str, Any]]:
    if not api_key:
        raise PlaceFactsError("Google Places API key is required")
    fields = ",".join(
        [
            "businessStatus",
            "formattedAddress",
            "nationalPhoneNumber",
            "internationalPhoneNumber",
            "websiteUri",
            "googleMapsUri",
            "regularOpeningHours",
            "currentOpeningHours",
            "rating",
            "userRatingCount",
        ]
    )
    checked_at = utc_now()
    results: list[dict[str, Any]] = []
    for item in due_records:
        place_id = item.get("google_place_id")
        internal_id = int(item["id"])
        if not place_id:
            continue
        encoded_id = urllib.parse.quote(str(place_id), safe="")
        request = urllib.request.Request(
            f"https://places.googleapis.com/v1/places/{encoded_id}",
            headers={
                "X-Goog-Api-Key": api_key,
                "X-Goog-FieldMask": fields,
                "Accept": "application/json",
                "User-Agent": "GoNorth-Place-Facts/1.0",
            },
        )
        try:
            with urllib.request.urlopen(request, timeout=timeout) as response:
                payload = json.load(response)
            results.append(normalize_google_response(internal_id, payload, checked_at))
        except (urllib.error.URLError, urllib.error.HTTPError, TimeoutError, json.JSONDecodeError) as exc:
            results.append(
                {
                    "id": internal_id,
                    "source": "google_places",
                    "checked_at": checked_at,
                    "error": str(exc),
                }
            )
    return results


def pending_changes(db_path: str | os.PathLike[str]) -> list[dict[str, Any]]:
    with connect(db_path) as connection:
        rows = connection.execute(
            """
            SELECT c.id, c.place_id, p.name, p.url, c.field,
                   c.old_value, c.new_value, c.detected_at,
                   c.confidence, v.source, v.source_url
            FROM place_changes c
            JOIN places p ON p.id = c.place_id
            JOIN place_verifications v ON v.id = c.verification_id
            WHERE c.review_status = 'pending'
            ORDER BY c.detected_at, c.id
            """
        ).fetchall()
    result = []
    for row in rows:
        item = dict(row)
        item["old_value"] = _from_json_value(item["old_value"])
        item["new_value"] = _from_json_value(item["new_value"])
        item["seo_action"] = (
            "keep_url_live_and_review_closure"
            if item["field"] == "business_status"
            and item["new_value"] in CLOSURE_STATUSES
            else "review_change"
        )
        result.append(item)
    return result



