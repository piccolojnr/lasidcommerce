#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R backthred:backthred storage bootstrap/cache

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    chown backthred:backthred .env
fi

if [ "${BACKTHRED_STORAGE_LINK:-true}" = "true" ]; then
    su-exec backthred php artisan storage:link --force >/dev/null 2>&1 || true
fi

if [ "${BACKTHRED_RUN_MIGRATIONS:-false}" = "true" ]; then
    su-exec backthred php artisan migrate --force
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    su-exec backthred php artisan config:cache
    su-exec backthred php artisan route:cache
    su-exec backthred php artisan view:cache
fi

if [ "${1:-}" = "php-fpm" ]; then
    exec "$@"
fi

exec su-exec backthred "$@"
