#!/bin/bash
set -euo pipefail
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
live=$(sha256sum "$plugin/assets/place-analytics.js" | cut -d' ' -f1)
test "$live" = 712020a63be3971f7de6d727503a98faac7123b003ec88618d7cc5b12577a76a
cp "$plugin/assets/place-analytics.js" /home/claude/issue83/place-analytics.js.stage1
cp /home/claude/issue83/place-analytics.js "$plugin/assets/place-analytics.js"
if ! wp eval-file /home/claude/issue83/issue83-update-policy.php --path=/var/www/gonorth --allow-root; then
  cp /home/claude/issue83/place-analytics.js.stage1 "$plugin/assets/place-analytics.js"
  exit 1
fi
wp autoptimize clear --path=/var/www/gonorth --allow-root
wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root