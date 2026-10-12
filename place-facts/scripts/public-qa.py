#!/usr/bin/env python3
"""Public QA for every place URL tracked by the place-facts ledger."""

from __future__ import annotations

import argparse
import concurrent.futures
import json
import re
import sqlite3
import urllib.error
import urllib.request
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from typing import Any


class PageSignals(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.canonical_count = 0
        self.h1_count = 0
        self.text: list[str] = []

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        values = {key.lower(): (value or "") for key, value in attrs}
        if tag.lower() == "h1":
            self.h1_count += 1
        if tag.lower() == "link" and "canonical" in values.get("rel", "").lower().split():
            self.canonical_count += 1

    def handle_data(self, data: str) -> None:
        self.text.append(data)


def load_places(db_path: Path) -> list[dict[str, Any]]:
    with sqlite3.connect(db_path) as connection:
        connection.row_factory = sqlite3.Row
        rows = connection.execute(
            "SELECT wordpress_id, name, url FROM places ORDER BY wordpress_id"
        ).fetchall()
    return [dict(row) for row in rows]


def scan(place: dict[str, Any], timeout: int) -> dict[str, Any]:
    request = urllib.request.Request(
        place["url"],
        headers={"User-Agent": "GoNorth-Public-QA/1.0"},
    )
    status = 0
    final_url = place["url"]
    html = ""
    error = None
    try:
        with urllib.request.urlopen(request, timeout=timeout) as response:
            status = int(response.status)
            final_url = response.geturl()
            html = response.read().decode("utf-8", errors="replace")
    except urllib.error.HTTPError as exc:
        status = int(exc.code)
        final_url = exc.geturl()
        html = exc.read().decode("utf-8", errors="replace")
        error = str(exc)
    except (urllib.error.URLError, TimeoutError) as exc:
        error = str(exc)

    parser = PageSignals()
    if html:
        parser.feed(html)
    visible_text = re.sub(r"\s+", " ", " ".join(parser.text))
    closure_notice = bool(
        re.search(r"(?:העסק\s+סגור|המקום\s+סגור|מסומ(?:ן|נת)\s+כסגור(?:ה)?|סגור\s+זמנית|נסגר|אינו\s+פעיל|לא\s+פעיל)", visible_text)
    )
    critical_error = bool(
        re.search(r"(?:Fatal error|There has been a critical error)", html, re.IGNORECASE)
    )
    return {
        **place,
        "status": status,
        "final_url": final_url,
        "canonical_count": parser.canonical_count,
        "h1_count": parser.h1_count,
        "aggregate_rating": '"aggregateRating"' in html,
        "closure_notice": closure_notice,
        "critical_error": critical_error,
        "error": error,
    }


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--db", type=Path, default=Path("place-facts/data/place-facts.sqlite3"))
    parser.add_argument("--output", type=Path, required=True)
    parser.add_argument("--workers", type=int, default=8)
    parser.add_argument("--timeout", type=int, default=20)
    args = parser.parse_args()

    places = load_places(args.db)
    with concurrent.futures.ThreadPoolExecutor(max_workers=max(1, args.workers)) as pool:
        rows = list(pool.map(lambda place: scan(place, args.timeout), places))

    by_id = {int(row["wordpress_id"]): row for row in rows}
    summary = {
        "total": len(rows),
        "http_200": sum(row["status"] == 200 for row in rows),
        "canonical_one": sum(row["canonical_count"] == 1 for row in rows),
        "h1_one": sum(row["h1_count"] == 1 for row in rows),
        "aggregate_rating_pages": sum(bool(row["aggregate_rating"]) for row in rows),
        "critical_error_pages": sum(bool(row["critical_error"]) for row in rows),
        "closure_219_notice": bool(by_id.get(219, {}).get("closure_notice")),
        "closure_221_notice": bool(by_id.get(221, {}).get("closure_notice")),
    }
    payload = {
        "generated_at": datetime.now(timezone.utc).replace(microsecond=0).isoformat().replace("+00:00", "Z"),
        "summary": summary,
        "results": rows,
    }
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(summary, ensure_ascii=False))
    return 0 if (
        summary["http_200"] == summary["total"]
        and summary["canonical_one"] == summary["total"]
        and summary["h1_one"] == summary["total"]
        and summary["aggregate_rating_pages"] == 0
        and summary["critical_error_pages"] == 0
    ) else 1


if __name__ == "__main__":
    raise SystemExit(main())
