#!/bin/sh

set -eu

APP_ROOT="/var/www/html"

mkdir -p \
  "$APP_ROOT/storage/app/public" \
  "$APP_ROOT/storage/framework/cache" \
  "$APP_ROOT/storage/framework/sessions" \
  "$APP_ROOT/storage/framework/views" \
  "$APP_ROOT/storage/logs" \
  "$APP_ROOT/bootstrap/cache"

chown -R www-data:www-data \
  "$APP_ROOT/storage" \
  "$APP_ROOT/bootstrap/cache"

chmod -R 775 \
  "$APP_ROOT/storage" \
  "$APP_ROOT/bootstrap/cache"

rm -rf "$APP_ROOT/public/storage"
ln -s "$APP_ROOT/storage/app/public" "$APP_ROOT/public/storage"
chown -h www-data:www-data "$APP_ROOT/public/storage"

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
