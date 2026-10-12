#!/bin/sh
set -eu
echo "a1910176300d507a2e4fe7dd273011fafb2e90c91d125df57c6f475b3c1d3737  /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php" | sha256sum -c -
echo "8e8d5110f1f231715311fa60039ea35f0db7f9382e1aab08bd7687c68c473b90  /var/www/gonorth/wp-content/themes/gonorth-child/functions.php" | sha256sum -c -
echo "9dd4a23157b8f6ee1960334df1f6d45f914ccabb2fc7e70718d047257f65396a  /var/www/gonorth/wp-content/themes/gonorth-child/assets/css/gonorth.css" | sha256sum -c -
cp /home/claude/.local/share/gonorth-issue87-category/front-page-before.php /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php
cp /home/claude/.local/share/gonorth-issue87-category/functions-before.php /var/www/gonorth/wp-content/themes/gonorth-child/functions.php
cp /home/claude/.local/share/gonorth-issue87-category/gonorth-before.css /var/www/gonorth/wp-content/themes/gonorth-child/assets/css/gonorth.css
php -l /var/www/gonorth/wp-content/themes/gonorth-child/front-page.php
php -l /var/www/gonorth/wp-content/themes/gonorth-child/functions.php
wp cache flush --path=/var/www/gonorth --allow-root
