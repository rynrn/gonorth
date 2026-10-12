#!/bin/bash
set -euo pipefail
theme=/var/www/gonorth/wp-content/themes/gonorth-child
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
cp /home/claude/issue83/header.php.pre "$theme/header.php"
cp /home/claude/issue83/functions.php.pre "$theme/functions.php"
cp /home/claude/issue83/gonorth.css.pre "$theme/assets/css/gonorth.css"
cp /home/claude/issue83/place-analytics.js.pre "$plugin/assets/place-analytics.js"
php -l "$theme/header.php"
php -l "$theme/functions.php"
wp eval-file /home/claude/issue83/issue83-restore-policy.php --path=/var/www/gonorth --allow-root
wp autoptimize clear --path=/var/www/gonorth --allow-root
wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root
wp cache flush --path=/var/www/gonorth --allow-root