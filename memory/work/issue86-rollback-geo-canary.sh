#!/usr/bin/env bash
set -euo pipefail
live=/var/www/gonorth/wp-content/themes/gonorth-child/functions.php
before=/home/claude/issue86-functions-before-geo.php
expected=519f6c1b55216290d4e97139d4cbb6617c039429cae9e18cfec01a3a8144052d
previous=a3ec4e487089355f50abbb858469312f037b33422a0d736a9d7d8b654b432e23
[ "$(sha256sum "$live" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$before" | cut -d' ' -f1)" = "$previous" ]
if [ "${1:-}" = --check-only ]; then echo 'rollback guard passed; no files changed'; exit 0; fi
cp "$before" "$live"
php -l "$live"
wp cache flush --path=/var/www/gonorth --allow-root
sha256sum "$live"
