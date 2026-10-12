#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
python3 - <<'PY' >/tmp/yarden-req.json
import json
print(json.dumps({"textQuery":"Yarden Estate Boutique Hotel מלון אחוזת ירדן יסוד המעלה","languageCode":"he","regionCode":"IL","locationBias":{"circle":{"center":{"latitude":33.044,"longitude":35.573},"radius":3000}}},ensure_ascii=False))
PY
curl -sS -X POST https://places.googleapis.com/v1/places:searchText -H 'Content-Type: application/json' -H "X-Goog-Api-Key: $key" -H 'X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.location,places.nationalPhoneNumber,places.websiteUri,places.rating,places.userRatingCount,places.businessStatus,places.primaryType,places.googleMapsUri' --data-binary @/tmp/yarden-req.json

