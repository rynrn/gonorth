#!/usr/bin/env bash
set -euo pipefail
arnon=$(python3 -c 'import json; print(json.load(open("/tmp/issue100-google-photos/arnon.json"))["photos"][0]["name"])')
renato=$(python3 -c 'import json; print(json.load(open("/tmp/issue100-google-photos/renato.json"))["photos"][0]["name"])')
wp post meta update 474 _gonorth_google_photo_name "$arnon" --path=/var/www/gonorth --allow-root
wp post meta update 475 _gonorth_google_photo_name "$renato" --path=/var/www/gonorth --allow-root
