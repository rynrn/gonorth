import html
import json
import re
import sys
import urllib.request
from datetime import datetime, timezone
from pathlib import Path

sys.stdout.reconfigure(encoding="utf-8")
targets = {
    "guide": "https://gonorth.co.il/agamon-hahula-guide/",
    "place": "https://gonorth.co.il/places/%D7%90%D7%92%D7%9E%D7%95%D7%9F-%D7%94%D7%97%D7%95%D7%9C%D7%94/",
}
approved = "לגבי מצפור השקנאים קיימת אי־התאמה"
forbidden = [
    "קיבוץ אגמון",
    "מאות אלפים בו-זמנית",
    "מבוגר 35",
    "ילד 21",
    "מצפור השקנאים פתוח",
]
result = {"checked_at": datetime.now(timezone.utc).isoformat(), "pages": {}, "errors": []}
for name, url in targets.items():
    with urllib.request.urlopen(url, timeout=45) as response:
        raw = response.read().decode("utf-8", errors="replace")
        status = response.status
    decoded = html.unescape(raw)
    canonical_match = re.search(r'<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)', raw, re.I)
    if not canonical_match:
        canonical_match = re.search(r'<link[^>]+href=["\']([^"\']+)["\'][^>]+rel=["\']canonical["\']', raw, re.I)
    meta_match = re.search(r'<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)', raw, re.I)
    ld_blocks = re.findall(r'<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>', raw, re.I | re.S)
    ld_errors = []
    for block in ld_blocks:
        try:
            json.loads(html.unescape(block).strip())
        except Exception as exc:
            ld_errors.append(str(exc))
    page = {
        "status": status,
        "canonical": canonical_match.group(1) if canonical_match else None,
        "meta_description_present": bool(meta_match and meta_match.group(1).strip()),
        "approved_conflict_notice": approved in decoded,
        "forbidden_found": [term for term in forbidden if term in decoded],
        "jsonld_blocks": len(ld_blocks),
        "jsonld_parse_errors": ld_errors,
        "us_markers": bool("United States" in decoded or "Pennsylvania" in decoded),
        "israel_marker": "ישראל" in decoded,
    }
    result["pages"][name] = page
    if status != 200 or not page["approved_conflict_notice"] or page["forbidden_found"] or page["jsonld_parse_errors"] or page["us_markers"]:
        result["errors"].append(name)

with urllib.request.urlopen("https://gonorth.co.il/wp-json/wp/v2/posts/432", timeout=45) as response:
    guide_rest = json.load(response)
with urllib.request.urlopen("https://gonorth.co.il/wp-json/geodir/v2/places/173", timeout=45) as response:
    place_rest = json.load(response)
result["rest"] = {
    "guide_status": guide_rest.get("status"),
    "guide_notice": approved in html.unescape(guide_rest["content"]["rendered"]),
    "place_status": place_rest.get("status"),
    "place_notice": approved in html.unescape(place_rest["content"]["rendered"]),
    "place_business_hours": place_rest.get("business_hours"),
    "place_country": place_rest.get("country"),
    "place_region": place_rest.get("region"),
    "place_city": place_rest.get("city"),
    "place_coordinates": [place_rest.get("latitude"), place_rest.get("longitude")],
}
if not result["rest"]["guide_notice"] or not result["rest"]["place_notice"]:
    result["errors"].append("rest_content")
if result["rest"]["place_business_hours"] is not None:
    result["errors"].append("unexpected_structured_hours")
out = Path("memory/data/2026-09-30-issue-80-live-qa.json")
out.write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8")
print(json.dumps(result, ensure_ascii=False, indent=2))
if result["errors"]:
    raise SystemExit(1)



