#!/usr/bin/env bash
set -euo pipefail
cd /var/www/gonorth
status="$(wp post get 439 --field=post_status)"
parent="$(wp post get 439 --field=post_parent)"
if [[ "$status" != "publish" || "$parent" != "24" ]]; then
  echo "Unexpected state: status=$status parent=$parent" >&2
  exit 3
fi
wp post update 439 --post_parent=0 --porcelain
wp rewrite flush
wp cache flush
wp post url 439