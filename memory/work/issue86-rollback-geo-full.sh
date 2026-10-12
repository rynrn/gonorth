#!/usr/bin/env bash
set -euo pipefail
live=/var/www/gonorth/wp-content/themes/gonorth-child/functions.php
before=/home/claude/issue86-functions-before-geo-full.php
expected=1c9cf74319c9bac42622db408f1e1a19d41a884f45a3e1f8ae3944c14b612152
previous=97256f627b28048be3ac0a14b053073905612508e6aa97121f039f46280b1f27
[ "$(sha256sum "$live" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$before" | cut -d' ' -f1)" = "$previous" ]
if [ "${1:-}" = --check-only ]; then echo 'rollback guard passed; no files changed'; exit 0; fi
cp "$before" "$live"
php -l "$live"
wp cache flush --path=/var/www/gonorth --allow-root
sha256sum "$live"
