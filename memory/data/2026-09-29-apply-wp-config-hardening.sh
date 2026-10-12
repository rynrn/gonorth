#!/usr/bin/env bash
set -eu
live=/var/www/gonorth/wp-config.php
backup=/home/claude/backups/2026-09-29-issue-75/wp-config.php.before
rollback() {
  cp -a "$backup" "$live"
  chgrp www-data "$live"
  chmod 640 "$live"
  echo ROLLED_BACK
}
trap 'code=$?; if [ "$code" -ne 0 ]; then rollback; fi; exit "$code"' EXIT

test -f "$backup"
wp config set DISALLOW_FILE_EDIT true --raw --type=constant --path=/var/www/gonorth --allow-root
wp config set FORCE_SSL_ADMIN true --raw --type=constant --path=/var/www/gonorth --allow-root
chgrp www-data "$live"
chmod 640 "$live"
php -l "$live"
test "$(wp config get DISALLOW_FILE_EDIT --path=/var/www/gonorth --allow-root)" = "1"
test "$(wp config get FORCE_SSL_ADMIN --path=/var/www/gonorth --allow-root)" = "1"
test "$(wp config get FS_METHOD --path=/var/www/gonorth --allow-root)" = "direct"
wp core version --path=/var/www/gonorth --allow-root >/dev/null

home_code=$(curl -s -o /dev/null -w "%{http_code}" https://gonorth.co.il/)
rest_code=$(curl -s -o /dev/null -w "%{http_code}" https://gonorth.co.il/wp-json/)
admin_code=$(curl -s -o /dev/null -w "%{http_code}" https://gonorth.co.il/wp-admin/)

case "$home_code" in 200|301|302) ;; *) exit 20;; esac
case "$rest_code" in 200) ;; *) exit 21;; esac
case "$admin_code" in 200|301|302) ;; *) exit 22;; esac

cp -a "$live" /home/claude/backups/2026-09-29-issue-75/wp-config.php.after
sha256sum "$backup" "$live"
stat -c "%a %U:%G %n" "$live"
echo "HTTP home=$home_code rest=$rest_code admin=$admin_code"
echo "FLAGS DISALLOW_FILE_EDIT=1 FORCE_SSL_ADMIN=1 FS_METHOD=direct"
trap - EXIT
