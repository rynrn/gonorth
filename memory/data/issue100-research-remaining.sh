#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
python3 - "$key" <<'PY'
import json,sys,urllib.request
key=sys.argv[1]
queries=['TRIO טריו קפה דפנה','שירי פסטה דפנה','קמפינג מטיילים דפנה','מלון מטיילים ארץ דפנה','קלומפוס דפנה','הטנק הסורי ההפוך בניאס']
for q in queries:
 body=json.dumps({'textQuery':q,'languageCode':'he','regionCode':'IL','maxResultCount':3}).encode();req=urllib.request.Request('https://places.googleapis.com/v1/places:searchText',data=body,headers={'Content-Type':'application/json','X-Goog-Api-Key':key,'X-Goog-FieldMask':'places.id,places.displayName,places.formattedAddress,places.location,places.rating,places.userRatingCount,places.nationalPhoneNumber,places.websiteUri,places.businessStatus,places.primaryType,places.photos'})
 try:
  with urllib.request.urlopen(req) as r:data=json.load(r)
  print(json.dumps({'query':q,'places':data.get('places',[])},ensure_ascii=False))
 except Exception as e: print(json.dumps({'query':q,'error':str(e)},ensure_ascii=False))
PY
