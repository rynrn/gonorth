#!/bin/bash
set -euo pipefail
wpbase='--path=/var/www/gonorth --allow-root'
content=$(wp post get 518 --field=post_content --path=/var/www/gonorth --allow-root)
source=$(wp post meta get 518 _gn_source_post_id --path=/var/www/gonorth --allow-root)
test "$source" = 56
case "$content" in *GN95TEST20261004*) ;; *) echo 'Synthetic marker missing; refusing delete' >&2; exit 1;; esac
wp post delete 518 --force --path=/var/www/gonorth --allow-root
wp gonorth corrections summary --path=/var/www/gonorth --allow-root