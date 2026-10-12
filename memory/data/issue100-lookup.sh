#!/usr/bin/env bash
set -euo pipefail
key="$(wp option get gonorth_google_server_api_key --path=/var/www/gonorth --allow-root 2>/dev/null)"
if [[ -z "$key" ]]; then
  echo '{"error":"missing_server_key"}'
  exit 2
fi
for encoded_query in "$@"; do
  query="$(printf '%s' "$encoded_query" | base64 -d)"
  payload="$(python3 -c 'import json,sys; print(json.dumps({"textQuery":sys.argv[1]}, ensure_ascii=False))' "$query")"
  curl -sS -X POST 'https://places.googleapis.com/v1/places:searchText'     -H 'Content-Type: application/json'     -H "X-Goog-Api-Key: $key"     -H 'X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.location,places.rating,places.userRatingCount,places.nationalPhoneNumber,places.websiteUri,places.regularOpeningHours'     -d "$payload"
  printf '\n'
done
