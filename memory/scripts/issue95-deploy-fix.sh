#!/bin/bash
set -euo pipefail
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
live_sha=$(sha256sum "$plugin/gonorth-place-data.php" | cut -d' ' -f1)
test "$live_sha" = 1ab8ecb62eca14cdd535fe47be20aacf02f1e0b4a2278bfccc97d82b479ee5c4
cp /home/claude/issue95/class-gonorth-corrections.php "$plugin/includes/class-gonorth-corrections.php"
cp /home/claude/issue95/gonorth-place-data.php "$plugin/gonorth-place-data.php"
php -l "$plugin/includes/class-gonorth-corrections.php"
php -l "$plugin/gonorth-place-data.php"
wp cache flush --path=/var/www/gonorth --allow-root
wp gonorth corrections summary --path=/var/www/gonorth --allow-root