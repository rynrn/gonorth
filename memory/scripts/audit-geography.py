import json
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime, timezone
from pathlib import Path

BASE = "https://gonorth.co.il/wp-json/geodir/v2/places"
places = []
for page in (1, 2):
    with urllib.request.urlopen(f"{BASE}?per_page=100&page={page}&_fields=id,link,country,region,city,latitude,longitude", timeout=45) as response:
        places.extend(json.load(response))

bad_location = []
bad_coordinates = []
for place in places:
    if place.get("country") != "ישראל" or place.get("region") != "צפון":
        bad_location.append(place["id"])
    try:
        lat, lng = float(place["latitude"]), float(place["longitude"])
        if not (29 <= lat <= 34 and 34 <= lng <= 36):
            bad_coordinates.append(place["id"])
    except (TypeError, ValueError):
        bad_coordinates.append(place["id"])

def scan(place):
    try:
        with urllib.request.urlopen(place["link"], timeout=45) as response:
            html = response.read().decode("utf-8", errors="replace")
        return place["id"], ("United States" in html or "Pennsylvania" in html), False
    except Exception:
        return place["id"], False, True

us_markers, http_failures = [], []
with ThreadPoolExecutor(max_workers=16) as pool:
    futures = [pool.submit(scan, place) for place in places]
    for future in as_completed(futures):
        place_id, has_us, failed = future.result()
        if has_us:
            us_markers.append(place_id)
        if failed:
            http_failures.append(place_id)

result = {
    "checked_at": datetime.now(timezone.utc).isoformat(),
    "total": len(places),
    "bad_country_or_region": sorted(bad_location),
    "bad_coordinate_bounds": sorted(bad_coordinates),
    "html_us_markers": sorted(us_markers),
    "http_failures": sorted(http_failures),
}
Path("memory/data/2026-09-30-issue-79-geography-scan.json").write_text(
    json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8"
)
print(json.dumps(result, ensure_ascii=False, indent=2))
