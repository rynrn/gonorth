#!/bin/bash
set -euo pipefail
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
current=$(sha256sum "$plugin/gonorth-place-data.php" | cut -d' ' -f1)
expected=$(sha256sum /home/claude/issue95/gonorth-place-data.php | cut -d' ' -f1)
test "$current" != "$expected"
cp /home/claude/issue95/class-gonorth-corrections.php "$plugin/includes/class-gonorth-corrections.php"
cp /home/claude/issue95/gonorth-place-data.php "$plugin/gonorth-place-data.php"
php -l "$plugin/includes/class-gonorth-corrections.php"
php -l "$plugin/gonorth-place-data.php"
wp cache flush --path=/var/www/gonorth --allow-root
wp gonorth corrections summary --path=/var/www/gonorth --allow-root