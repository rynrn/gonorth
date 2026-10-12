#!/usr/bin/env bash
set -euo pipefail
base=/var/www/gonorth/wp-content/themes/gonorth-child
live_css="$base/assets/css/gonorth.css"
live_php="$base/functions.php"
before_css=/home/claude/issue88-blog-overflow-before.css
before_php=/home/claude/issue88-blog-overflow-before-functions.php
[ "$(sha256sum "$live_css" | cut -d' ' -f1)" = "a138c9a2d0ef41077a797df8513684f4148ad65163755f912829204cd7a4490c" ]
[ "$(sha256sum "$live_php" | cut -d' ' -f1)" = "aeb1a129ab3b004275aaa257e8f141dfdeb594e92bfdb7965329797e9bb6fd3f" ]
[ "$(sha256sum "$before_css" | cut -d' ' -f1)" = "9dd4a23157b8f6ee1960334df1f6d45f914ccabb2fc7e70718d047257f65396a" ]
[ "$(sha256sum "$before_php" | cut -d' ' -f1)" = "1c9cf74319c9bac42622db408f1e1a19d41a884f45a3e1f8ae3944c14b612152" ]
if [ "${1:-}" = --check-only ]; then echo 'rollback guard passed; no files changed'; exit 0; fi
cp "$before_css" "$live_css"
cp "$before_php" "$live_php"
php -l "$live_php"
wp cache flush --path=/var/www/gonorth --allow-root
sha256sum "$live_css" "$live_php"
