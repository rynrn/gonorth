#!/bin/bash
set -euo pipefail
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
php -l /home/claude/issue95/gonorth-place-data.php.pre
cp /home/claude/issue95/gonorth-place-data.php.pre "$plugin/gonorth-place-data.php"
php -l "$plugin/gonorth-place-data.php"
wp cache flush --path=/var/www/gonorth --allow-root
# The new CSS and class remain inert after the original bootstrap is restored.