#!/usr/bin/env bash
set -euo pipefail
base=/home/claude/issue83-legacy-v2
theme=/var/www/gonorth/wp-content/themes/gonorth-child
plugin=/var/www/gonorth/wp-content/plugins/gonorth-place-data
check() {
  test "$(sha256sum "$1" | cut -d' ' -f1)" = "$2"
}
check "$theme/header.php" 79743122f237b68a10d4a83f481d6173dc7f6a73bb9ee3ecf561d8d5e7f7a0a6
check "$theme/functions.php" 11ac1b456bb4850ec9a6ffb7fbb8d90dd49b65431aa00a3dca49d62499448e01
check "$plugin/assets/place-analytics.js" 6f0eb4af51fc7d392ee3606b9402c054e65372ea2658038f1af8d4ada498bab0
check "$plugin/assets/preferred-source.js" 3ca05c19f087daefcbc342f9cc06e28f22999519c7b69b80d1df8aed2290e2bb
check "$plugin/gonorth-place-data.php" dc86f3f1bb61cf4acab31bb768baa4894bdfac849e20e69a9f8e983921034ef7
check "$plugin/tests/preferred-source.php" b94a8e27b5d8de5d2bc5bb43f733b42700467827c033d254ec96083edb7c33ea
php -l "$base/stage/header.php" >/dev/null
php -l "$base/stage/functions.php" >/dev/null
php -l "$base/stage/gonorth-place-data.php" >/dev/null
cp "$base/stage/header.php" "$theme/header.php"
cp "$base/stage/functions.php" "$theme/functions.php"
cp "$base/stage/place-analytics.js" "$plugin/assets/place-analytics.js"
cp "$base/stage/preferred-source.js" "$plugin/assets/preferred-source.js"
cp "$base/stage/gonorth-place-data.php" "$plugin/gonorth-place-data.php"
cp "$base/stage/preferred-source-test.php" "$plugin/tests/preferred-source.php"
wp cache flush --path=/var/www/gonorth --allow-root >/dev/null
wp autoptimize clear --path=/var/www/gonorth --allow-root >/dev/null
wp eval 'wp_cache_clear_cache();' --path=/var/www/gonorth --allow-root >/dev/null
echo deployed