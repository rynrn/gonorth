#!/bin/sh
set -eu
echo "a8cfc90c5481c86363acd12d0d6e71dd75fc6a185fddba80b126756a5580d2b2  /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php" | sha256sum -c -
echo "9679b78f2f8a5225b9969ecde8196ed4648a207defbfed6f44f8f6326465da0f  /var/www/gonorth/wp-content/themes/gonorth-child/functions.php" | sha256sum -c -
echo "0bc130cb6295436a6001ac2361f8337640688b4abdfeaa00771c2dcbdd7ec24b  /var/www/gonorth/wp-content/themes/gonorth-child/assets/css/gonorth.css" | sha256sum -c -
cp /home/claude/.local/share/gonorth-issue87-category/front-page-candidate.php /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php
cp /home/claude/.local/share/gonorth-issue87-category/functions-candidate.php /var/www/gonorth/wp-content/themes/gonorth-child/functions.php
cp /home/claude/.local/share/gonorth-issue87-category/gonorth-candidate.css /var/www/gonorth/wp-content/themes/gonorth-child/assets/css/gonorth.css
php -l /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php
php -l /var/www/gonorth/wp-content/themes/gonorth-child/functions.php
wp cache flush --path=/var/www/gonorth --allow-root
