#!/usr/bin/env bash
set -euo pipefail
cd /var/www/gonorth
for id in 339 341 343 425; do
  if wp post get "$id" --field=content | grep -q 'golan-accommodation-comparison'; then echo "$id linked"; else echo "$id missing"; fi
done
wp post meta get 447 _yoast_wpseo_title || true