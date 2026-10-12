#!/usr/bin/env bash
set -euo pipefail
live=/var/www/gonorth/wp-content/themes/gonorth-child/functions.php
before=/home/claude/issue86-functions-before-geo.php
candidate=/home/claude/issue86-functions-geo-canary.php
expected=a3ec4e487089355f50abbb858469312f037b33422a0d736a9d7d8b654b432e23
candidate_hash=519f6c1b55216290d4e97139d4cbb6617c039429cae9e18cfec01a3a8144052d
[ "$(sha256sum "$live" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$before" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$candidate" | cut -d' ' -f1)" = "$candidate_hash" ]
php -l "$candidate"
cp "$candidate" "$live"
if ! php -l "$live"; then cp "$before" "$live"; exit 31; fi
wp cache flush --path=/var/www/gonorth --allow-root
sha256sum "$live"
