#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
d=/tmp/issue100-f;mkdir -p "$d"
search(){ local s="$1" q="$2";python3 - "$q" >"$d/$s-req.json" <<'PY'
import json,sys
print(json.dumps({"textQuery":sys.argv[1],"languageCode":"he","regionCode":"IL","locationBias":{"circle":{"center":{"latitude":33.15,"longitude":35.60},"radius":30000}}},ensure_ascii=False))
PY
curl -sS -X POST https://places.googleapis.com/v1/places:searchText -H 'Content-Type: application/json' -H "X-Goog-Api-Key: $key" -H 'X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.location,places.nationalPhoneNumber,places.websiteUri,places.rating,places.userRatingCount,places.businessStatus,places.primaryType,places.googleMapsUri,places.photos' --data-binary "@$d/$s-req.json" -o "$d/$s.json";}
search galilion 'מלון גליליון'
search einteo 'שמורת טבע עין תאו מעיין תאו'
python3 - <<'PY'
import json
for s in ("galilion","einteo"):
 d=json.load(open(f"/tmp/issue100-f/{s}.json",encoding="utf-8"));print("\n==",s,"==")
 for p in d.get("places",[])[:3]: print(json.dumps({k:p.get(k) for k in ("id","displayName","formattedAddress","location","nationalPhoneNumber","websiteUri","rating","userRatingCount","businessStatus","primaryType","googleMapsUri")},ensure_ascii=False))
PY

