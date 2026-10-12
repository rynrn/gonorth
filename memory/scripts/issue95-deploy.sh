#!/bin/bash
set -euo pipefail
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
live_sha=$(sha256sum "$plugin/gonorth-place-data.php" | cut -d' ' -f1)
test "$live_sha" = a76d67fd528b7b5efbced9f25912e0e495c451eb370857ff16172992d4a339f0
cp /home/claude/issue95/class-gonorth-corrections.php "$plugin/includes/class-gonorth-corrections.php"
cp /home/claude/issue95/corrections.css "$plugin/assets/corrections.css"
php -l "$plugin/includes/class-gonorth-corrections.php"
cp /home/claude/issue95/gonorth-place-data.php "$plugin/gonorth-place-data.php"
php -l "$plugin/gonorth-place-data.php"
wp cache flush --path=/var/www/gonorth --allow-root