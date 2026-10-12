#!/usr/bin/env bash
set -euo pipefail
root=/var/www/gonorth
backup=/home/claude/.local/share/gonorth-issue88
functions="$root/wp-content/themes/gonorth-child/functions.php"
single="$root/wp-content/themes/gonorth-child/single.php"
css="$root/wp-content/themes/gonorth-child/assets/css/gonorth.css"
check_hash() { test "$(sha256sum "$1" | awk '{print $1}')" = "$2"; }
check_hash "$functions" 9679b78f2f8a5225b9969ecde8196ed4648a207defbfed6f44f8f6326465da0f
check_hash "$single" cdf537a171b478850c4355b699a3b41a6ae19ad72b1b29e1b499f979a9dbe2cc
check_hash "$css" 0bc130cb6295436a6001ac2361f8337640688b4abdfeaa00771c2dcbdd7ec24b
for pair in 94:24 95:21 96:21 97:23 427:21 428:21 464:25; do
  id=${pair%%:*}; expected=${pair##*:}
  current=$(wp post term list "$id" category --field=term_id --path="$root" --allow-root)
  test "$current" = "$expected"
done
if test "${1:-}" = --check; then echo 'Rollback preconditions pass'; exit 0; fi
cp "$backup/functions.php" "$functions"
cp "$backup/single.php" "$single"
cp "$backup/gonorth.css" "$css"
for id in 94 95 96 97 427 428 464; do
  wp post term set "$id" category 22 --by=id --path="$root" --allow-root
done
php -l "$functions"
php -l "$single"
wp cache flush --path="$root" --allow-root
wp eval 'if (function_exists("wp_cache_clear_cache")) wp_cache_clear_cache();' --path="$root" --allow-root
sha256sum "$functions" "$single" "$css"
