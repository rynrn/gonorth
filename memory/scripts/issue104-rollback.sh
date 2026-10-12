#!/usr/bin/env bash
set -euo pipefail
root=/var/www/gonorth/wp-content/themes/gonorth-child
base=/home/claude/issue104
cp "$base/pre/functions.php" "$root/functions.php"
cp "$base/pre/gonorth.css" "$root/assets/css/gonorth.css"
wp cache flush --path=/var/www/gonorth --allow-root >/dev/null
wp autoptimize clear --path=/var/www/gonorth --allow-root >/dev/null
wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root >/dev/null
echo restored
