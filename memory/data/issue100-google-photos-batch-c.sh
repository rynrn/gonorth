#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
dir=/tmp/issue100-c-google-photos
mkdir -p "$dir"
fetch_place() {
  local slug="$1" place_id="$2"
  curl -sS "https://places.googleapis.com/v1/places/$place_id" -H "X-Goog-Api-Key: $key" -H "X-Goog-FieldMask: id,displayName,photos" -o "$dir/$slug.json"
  python3 - "$slug" "$key" "$dir" <<'PY'
import json,subprocess,sys
slug,key,d=sys.argv[1:4]
data=json.load(open(f"{d}/{slug}.json",encoding="utf-8"))
for i,p in enumerate(data.get("photos",[])[:3],1):
 name=p["name"]; out=f"{d}/{slug}-{i}.jpg"
 subprocess.run(["curl","-sSL","-H",f"X-Goog-Api-Key: {key}",f"https://places.googleapis.com/v1/{name}/media?maxWidthPx=1600","-o",out],check=True)
 print(json.dumps({"slug":slug,"index":i,"file":out,"name":name,"width":p.get("widthPx"),"height":p.get("heightPx"),"authorAttributions":p.get("authorAttributions",[])},ensure_ascii=False))
PY
}
fetch_place shwartz ChIJM5EjMbQhHBURl3TFbMs_-PE
fetch_place petipeti ChIJmeF0nmUfHBURwC8gAyBgeJg

