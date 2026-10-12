#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
mkdir -p /tmp/issue100-c-research
search_place() {
  local slug="$1" query="$2"
  python3 - "$query" > "/tmp/issue100-c-research/$slug-request.json" <<'PY'
import json,sys
print(json.dumps({"textQuery":sys.argv[1],"languageCode":"he","regionCode":"IL","locationBias":{"circle":{"center":{"latitude":33.05,"longitude":35.57},"radius":25000}}},ensure_ascii=False))
PY
  curl -sS -X POST 'https://places.googleapis.com/v1/places:searchText'     -H 'Content-Type: application/json' -H "X-Goog-Api-Key: $key"     -H 'X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.location,places.nationalPhoneNumber,places.websiteUri,places.rating,places.userRatingCount,places.businessStatus,places.regularOpeningHours,places.googleMapsUri,places.primaryType,places.photos'     --data-binary "@/tmp/issue100-c-research/$slug-request.json"     -o "/tmp/issue100-c-research/$slug.json"
}
search_place shwartz 'שוורצע משק שוורץ שדה אליעזר'
search_place petipeti 'Peti Peti שדה אליעזר'
python3 - <<'PY'
import json
for slug in ("shwartz","petipeti"):
 d=json.load(open(f"/tmp/issue100-c-research/{slug}.json",encoding="utf-8"))
 print("\n==",slug,"==")
 for p in d.get("places",[])[:5]:
  print(json.dumps({k:p.get(k) for k in ("id","displayName","formattedAddress","location","nationalPhoneNumber","websiteUri","rating","userRatingCount","businessStatus","primaryType","googleMapsUri")},ensure_ascii=False))
PY

