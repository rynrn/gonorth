#!/bin/bash
set -euo pipefail
theme=/var/www/gonorth/wp-content/themes/gonorth-child
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
cmp -s "$theme/header.php" /home/claude/issue83/header.php.pre
cmp -s "$theme/functions.php" /home/claude/issue83/functions.php.pre
cmp -s "$theme/assets/css/gonorth.css" /home/claude/issue83/gonorth.css.pre
cmp -s "$plugin/assets/place-analytics.js" /home/claude/issue83/place-analytics.js.pre
rollback(){ bash /home/claude/issue83/issue83-rollback.sh; }
trap rollback ERR
cp /home/claude/issue83/place-analytics.js "$plugin/assets/place-analytics.js"
cp /home/claude/issue83/functions.php "$theme/functions.php"
cp /home/claude/issue83/gonorth.css "$theme/assets/css/gonorth.css"
cp /home/claude/issue83/header.php "$theme/header.php"
php -l "$theme/header.php"
php -l "$theme/functions.php"
wp cache flush --path=/var/www/gonorth --allow-root
cmp -s "$theme/header.php" /home/claude/issue83/header.php
cmp -s "$theme/functions.php" /home/claude/issue83/functions.php
cmp -s "$theme/assets/css/gonorth.css" /home/claude/issue83/gonorth.css
cmp -s "$plugin/assets/place-analytics.js" /home/claude/issue83/place-analytics.js
trap - ERR