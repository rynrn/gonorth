#!/usr/bin/env bash
set -euo pipefail
base=/home/claude/issue83-legacy-v2
theme=/var/www/gonorth/wp-content/themes/gonorth-child
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
cp "$base/pre/header.php" "$theme/header.php"
cp "$base/pre/functions.php" "$theme/functions.php"
cp "$base/pre/place-analytics.js" "$plugin/assets/place-analytics.js"
cp "$base/pre/preferred-source.js" "$plugin/assets/preferred-source.js"
cp "$base/pre/gonorth-place-data.php" "$plugin/gonorth-place-data.php"
cp "$base/pre/preferred-source-test.php" "$plugin/tests/preferred-source.php"
wp cache flush --path=/var/www/gonorth --allow-root >/dev/null
wp autoptimize clear --path=/var/www/gonorth --allow-root >/dev/null
wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root >/dev/null
echo restored