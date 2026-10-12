#!/usr/bin/env bash
set -euo pipefail
root=/var/www/gonorth/wp-content/themes/gonorth-child
base=/home/claude/issue104
test "$(sha256sum "$root/functions.php" | cut -d' ' -f1)" = a937d5728ba00b004086affd67da9bc632f95a6fdf304f7f4a5eb88a8eccdd14
test "$(sha256sum "$root/assets/css/gonorth.css" | cut -d' ' -f1)" = 76331c549c3da2af9128b169131e629c7797f7907b95994e3aa0236f2b194f3b
php -l "$base/stage/functions.php" >/dev/null
cp "$base/stage/functions.php" "$root/functions.php"
cp "$base/stage/gonorth.css" "$root/assets/css/gonorth.css"
wp cache flush --path=/var/www/gonorth --allow-root >/dev/null
wp autoptimize clear --path=/var/www/gonorth --allow-root >/dev/null
wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root >/dev/null
echo deployed
