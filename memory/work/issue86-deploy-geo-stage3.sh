#!/usr/bin/env bash
set -euo pipefail
live=/var/www/gonorth/wp-content/themes/gonorth-child/functions.php
before=/home/claude/issue86-functions-before-geo-stage3.php
candidate=/home/claude/issue86-functions-geo-stage3.php
expected=519f6c1b55216290d4e97139d4cbb6617c039429cae9e18cfec01a3a8144052d
candidate_hash=97256f627b28048be3ac0a14b053073905612508e6aa97121f039f46280b1f27
[ "$(sha256sum "$live" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$before" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$candidate" | cut -d' ' -f1)" = "$candidate_hash" ]
php -l "$candidate"
cp "$candidate" "$live"
if ! php -l "$live"; then cp "$before" "$live"; exit 31; fi
wp cache flush --path=/var/www/gonorth --allow-root
sha256sum "$live"
