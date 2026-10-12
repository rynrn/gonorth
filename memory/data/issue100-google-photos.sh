#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
mkdir -p /tmp/issue100-google-photos
fetch_place() {
  local slug="$1" place_id="$2"
  curl -sS "https://places.googleapis.com/v1/places/$place_id" -H "X-Goog-Api-Key: $key" -H "X-Goog-FieldMask: id,displayName,photos" -o "/tmp/issue100-google-photos/$slug.json"
  python3 - "$slug" "$key" <<'PY'
import json, subprocess, sys
slug,key=sys.argv[1:3]
path=f"/tmp/issue100-google-photos/{slug}.json"
data=json.load(open(path,encoding="utf-8"))
for i,p in enumerate(data.get("photos",[])[:3],1):
    name=p["name"]
    url=f"https://places.googleapis.com/v1/{name}/media?maxWidthPx=1600"
    out=f"/tmp/issue100-google-photos/{slug}-{i}.jpg"
    subprocess.run(["curl","-sSL","-H",f"X-Goog-Api-Key: {key}",url,"-o",out],check=True)
    print(json.dumps({"slug":slug,"index":i,"file":out,"name":name,"width":p.get("widthPx"),"height":p.get("heightPx"),"authorAttributions":p.get("authorAttributions",[])},ensure_ascii=False))
PY
}
fetch_place arnon ChIJd1H9FQChHhURr33cM2c8b38
fetch_place renato ChIJ0853nvohHBURd5AEOnjKy_A
