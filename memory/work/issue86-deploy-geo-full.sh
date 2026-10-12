#!/usr/bin/env bash
set -euo pipefail
live=/var/www/gonorth/wp-content/themes/gonorth-child/functions.php
before=/home/claude/issue86-functions-before-geo-full.php
candidate=/home/claude/issue86-functions-geo-full.php
expected=97256f627b28048be3ac0a14b053073905612508e6aa97121f039f46280b1f27
candidate_hash=1c9cf74319c9bac42622db408f1e1a19d41a884f45a3e1f8ae3944c14b612152
[ "$(sha256sum "$live" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$before" | cut -d' ' -f1)" = "$expected" ]
[ "$(sha256sum "$candidate" | cut -d' ' -f1)" = "$candidate_hash" ]
php -l "$candidate"
cp "$candidate" "$live"
if ! php -l "$live"; then cp "$before" "$live"; exit 31; fi
wp cache flush --path=/var/www/gonorth --allow-root
sha256sum "$live"
