#!/usr/bin/env python3
"""Command-line interface for the GoNorth place-facts database."""

from __future__ import annotations

import argparse
import json
import os
import sys
from pathlib import Path
from typing import Any

if hasattr(sys.stdout, "reconfigure"):
    sys.stdout.reconfigure(encoding="utf-8")
if hasattr(sys.stderr, "reconfigure"):
    sys.stderr.reconfigure(encoding="utf-8")

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT))

from place_facts import (  # noqa: E402
    PlaceFactsError,
    build_wordpress_sync_package,
    compare_wordpress_projection,
    export_records,
    fetch_google_results,
    import_snapshot,
    init_db,
    markdown_review_table,
    monitoring_report,
    pending_changes,
    places_due,
    record_wordpress_sync_receipt,
    review_changes,
    search_google_place_candidates,
    submit_results,
)

DEFAULT_DB = ROOT / "data" / "place-facts.sqlite3"


def read_json(path: str) -> Any:
    if path == "-":
        return json.load(sys.stdin)
    with open(path, "r", encoding="utf-8-sig") as handle:
        return json.load(handle)


def write_output(value: str, output: str | None) -> None:
    if output:
        target = Path(output)
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(value, encoding="utf-8")
    else:
        sys.stdout.write(value)


def json_text(value: Any) -> str:
    return json.dumps(value, ensure_ascii=False, indent=2) + "\n"


def cmd_init(args: argparse.Namespace) -> None:
    print(json_text(init_db(args.db, args.backup_dir)), end="")


def cmd_import(args: argparse.Namespace) -> None:
    result = import_snapshot(args.db, args.snapshot, args.replace_facts)
    print(json_text(result), end="")


def cmd_due(args: argparse.Namespace) -> None:
    records = places_due(args.db, args.days, args.limit, args.google_only)
    if args.format == "markdown":
        table_records = [
            {
                **item,
                "pending_changes": [{}] * int(item["pending_changes"]),
            }
            for item in records
        ]
        write_output(markdown_review_table(table_records), args.output)
    else:
        write_output(json_text(records), args.output)


def cmd_submit(args: argparse.Namespace) -> None:
    payload = read_json(args.results)
    if isinstance(payload, dict):
        payload = payload.get("results", [payload])
    if not isinstance(payload, list):
        raise PlaceFactsError("results input must be an object, list, or {results: [...]}")
    print(json_text(submit_results(args.db, payload)), end="")


def cmd_pending(args: argparse.Namespace) -> None:
    rows = pending_changes(args.db)
    write_output(json_text(rows), args.output)


def cmd_review(args: argparse.Namespace) -> None:
    print(
        json_text(review_changes(args.db, args.change_id, args.decision, args.note)),
        end="",
    )


def cmd_export(args: argparse.Namespace) -> None:
    records = export_records(args.db)
    if args.format == "markdown":
        write_output(markdown_review_table(records), args.output)
    else:
        write_output(json_text(records), args.output)


def cmd_reconcile(args: argparse.Namespace) -> None:
    payload = read_json(args.wordpress_export)
    if isinstance(payload, dict):
        payload = payload.get("records", payload.get("rows", []))
    if not isinstance(payload, list):
        raise PlaceFactsError("WordPress export must be a list or contain records")
    report = compare_wordpress_projection(args.db, payload)
    write_output(json_text(report), args.output)


def cmd_export_wordpress_sync(args: argparse.Namespace) -> None:
    package = build_wordpress_sync_package(args.db, args.change_id)
    write_output(json_text(package), args.output)


def cmd_record_wordpress_sync(args: argparse.Namespace) -> None:
    receipt = read_json(args.receipt)
    if not isinstance(receipt, dict):
        raise PlaceFactsError("WordPress sync receipt must be an object")
    print(json_text(record_wordpress_sync_receipt(args.db, receipt)), end="")


def cmd_monitor(args: argparse.Namespace) -> None:
    report = monitoring_report(args.db, args.days)
    write_output(json_text(report), args.output)
    if args.fail_on_attention and report["needs_attention"]:
        raise PlaceFactsError("place-facts monitoring found items requiring review")


def cmd_fetch_google(args: argparse.Namespace) -> None:
    api_key = os.environ.get(args.api_key_env, "")
    if not api_key:
        raise PlaceFactsError(
            f"environment variable {args.api_key_env} is missing; "
            "API keys are intentionally not accepted as command-line values"
        )
    due = places_due(args.db, args.days, args.limit, google_only=True)
    results = fetch_google_results(due, api_key, args.timeout)
    write_output(json_text(results), args.output)


def cmd_find_google_place_ids(args: argparse.Namespace) -> None:
    api_key = os.environ.get(args.api_key_env, "")
    if not api_key:
        raise PlaceFactsError(f"environment variable {args.api_key_env} is missing")
    records = [item for item in export_records(args.db) if not item.get("google_place_id")]
    if args.limit is not None:
        records = records[: args.limit]
    write_output(
        json_text(search_google_place_candidates(records, api_key, args.timeout, args.retries, args.min_delay)),
        args.output,
    )


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Verify changing GoNorth place facts without editing editorial content."
    )
    parser.add_argument(
        "--db",
        default=str(DEFAULT_DB),
        help=f"SQLite database path (default: {DEFAULT_DB})",
    )
    subparsers = parser.add_subparsers(dest="command", required=True)

    init_parser = subparsers.add_parser("init", help="Create or migrate the database schema.")
    init_parser.add_argument(
        "--backup-dir",
        help="Directory for an automatic SQLite backup before a schema upgrade.",
    )
    init_parser.set_defaults(func=cmd_init)

    import_parser = subparsers.add_parser(
        "import-snapshot", help="Import a public GeoDirectory audit snapshot."
    )
    import_parser.add_argument("snapshot")
    import_parser.add_argument(
        "--replace-facts",
        action="store_true",
        help="Replace existing fact rows. Identity fields are always refreshed.",
    )
    import_parser.set_defaults(func=cmd_import)

    due_parser = subparsers.add_parser(
        "due", help="List places whose facts have not been checked recently."
    )
    due_parser.add_argument("--days", type=int, default=90)
    due_parser.add_argument("--limit", type=int)
    due_parser.add_argument("--google-only", action="store_true")
    due_parser.add_argument("--format", choices=("json", "markdown"), default="json")
    due_parser.add_argument("--output")
    due_parser.set_defaults(func=cmd_due)

    submit_parser = subparsers.add_parser(
        "submit", help="Compare structured verification results and queue changes."
    )
    submit_parser.add_argument("results", help="JSON file or - for stdin.")
    submit_parser.set_defaults(func=cmd_submit)

    pending_parser = subparsers.add_parser(
        "pending", help="List changes waiting for explicit review."
    )
    pending_parser.add_argument("--output")
    pending_parser.set_defaults(func=cmd_pending)

    review_parser = subparsers.add_parser(
        "review", help="Approve or reject queued fact changes."
    )
    review_parser.add_argument("decision", choices=("approve", "reject"))
    review_parser.add_argument("change_id", type=int, nargs="+")
    review_parser.add_argument("--note")
    review_parser.set_defaults(func=cmd_review)

    export_parser = subparsers.add_parser(
        "export", help="Export the current source of truth for agents and skills."
    )
    export_parser.add_argument("--format", choices=("json", "markdown"), default="json")
    export_parser.add_argument("--output")
    export_parser.set_defaults(func=cmd_export)

    reconcile_parser = subparsers.add_parser(
        "reconcile",
        help="Compare SQLite facts against a read-only WordPress canonical-data export.",
    )
    reconcile_parser.add_argument("wordpress_export", help="JSON file or - for stdin.")
    reconcile_parser.add_argument("--output")
    reconcile_parser.set_defaults(func=cmd_reconcile)

    sync_parser = subparsers.add_parser(
        "export-wordpress-sync",
        help="Build a read-only package of approved facts for a WordPress dry run.",
    )
    sync_parser.add_argument("--change-id", type=int, action="append")
    sync_parser.add_argument("--output", required=True)
    sync_parser.set_defaults(func=cmd_export_wordpress_sync)

    receipt_parser = subparsers.add_parser(
        "record-wordpress-sync",
        help="Record an apply receipt returned by the WordPress sync command.",
    )
    receipt_parser.add_argument("receipt", help="JSON file or - for stdin.")
    receipt_parser.set_defaults(func=cmd_record_wordpress_sync)

    monitor_parser = subparsers.add_parser(
        "monitor",
        help="Create a read-only freshness and review-queue report for scheduled monitoring.",
    )
    monitor_parser.add_argument("--days", type=int, default=90)
    monitor_parser.add_argument("--output")
    monitor_parser.add_argument("--fail-on-attention", action="store_true")
    monitor_parser.set_defaults(func=cmd_monitor)

    google_parser = subparsers.add_parser(
        "fetch-google",
        help="Fetch only dynamic Google Places fields for due places and emit JSON.",
    )
    google_parser.add_argument("--days", type=int, default=90)
    google_parser.add_argument("--limit", type=int)
    google_parser.add_argument("--timeout", type=int, default=20)
    google_parser.add_argument("--api-key-env", default="GOOGLE_PLACES_API_KEY")
    google_parser.add_argument("--output", required=True)
    google_parser.set_defaults(func=cmd_fetch_google)

    mapping_parser = subparsers.add_parser(
        "find-google-place-ids",
        help="Find reviewable Google Place ID candidates; this command never writes to SQLite.",
    )
    mapping_parser.add_argument("--limit", type=int)
    mapping_parser.add_argument("--timeout", type=int, default=20)
    mapping_parser.add_argument("--retries", type=int, default=2)
    mapping_parser.add_argument("--min-delay", type=float, default=0.2)
    mapping_parser.add_argument("--api-key-env", default="GOOGLE_PLACES_API_KEY")
    mapping_parser.add_argument("--output", required=True)
    mapping_parser.set_defaults(func=cmd_find_google_place_ids)

    return parser


def main() -> int:
    parser = build_parser()
    args = parser.parse_args()
    try:
        args.func(args)
    except (PlaceFactsError, OSError, json.JSONDecodeError) as exc:
        parser.error(str(exc))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())

