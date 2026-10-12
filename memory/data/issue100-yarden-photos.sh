#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
d=/tmp/issue100-d-google-photos
curl -sS 'https://places.googleapis.com/v1/places/ChIJ6TyNuOMfHBURJIWElcvImbQ' -H "X-Goog-Api-Key: $key" -H 'X-Goog-FieldMask: id,displayName,photos' -o "$d/yarden.json"
python3 - "$key" "$d" <<'PY'
import json,subprocess,sys
key,d=sys.argv[1:3]; data=json.load(open(f"{d}/yarden.json",encoding="utf-8"))
for i,p in enumerate(data.get("photos",[])[:3],1):
 out=f"{d}/yarden-{i}.jpg"; subprocess.run(["curl","-sSL","-H",f"X-Goog-Api-Key: {key}",f"https://places.googleapis.com/v1/{p['name']}/media?maxWidthPx=1600","-o",out],check=True)
 print(json.dumps({"index":i,"name":p["name"],"width":p.get("widthPx"),"height":p.get("heightPx"),"authorAttributions":p.get("authorAttributions",[])},ensure_ascii=False))
PY

