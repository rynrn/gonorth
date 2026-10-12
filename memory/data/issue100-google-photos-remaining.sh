#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)";d=/tmp/issue100-remaining-photos;mkdir -p "$d"
fetch(){ local slug="$1" pid="$2";curl -sS "https://places.googleapis.com/v1/places/$pid" -H "X-Goog-Api-Key: $key" -H 'X-Goog-FieldMask: photos' -o "$d/$slug.json";python3 - "$slug" "$key" "$d" <<'PY'
import json,subprocess,sys
s,key,d=sys.argv[1:4];data=json.load(open(f'{d}/{s}.json'))
for i,p in enumerate(data.get('photos',[])[:3],1):
 out=f'{d}/{s}-{i}.jpg';subprocess.run(['curl','-sSL','-H',f'X-Goog-Api-Key: {key}',f"https://places.googleapis.com/v1/{p['name']}/media?maxWidthPx=1600",'-o',out],check=True)
 print(json.dumps({'slug':s,'index':i,'name':p['name'],'authorAttributions':p.get('authorAttributions',[])},ensure_ascii=False))
PY
}
fetch trio ChIJbxyosay9HhURiTTZYa0VnEs
fetch shiri ChIJBSPjm229HhURIVTVnzvTX20
fetch camping ChIJueTarJG9HhURMuWRJVZYzlk
fetch hotel ChIJbaAQq2W8HhURx1Etmz8RxmI
fetch klompus ChIJW4o8VPm8HhURS3ZkRfVsgHc
fetch tank ChIJjeO2GMO7HhURnB0BuN1ELuE
