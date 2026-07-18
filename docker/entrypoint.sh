#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R lasidcommerce:lasidcommerce storage bootstrap/cache

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    chown lasidcommerce:lasidcommerce .env
fi

if [ "${LASIDCOMMERCE_STORAGE_LINK:-true}" = "true" ]; then
    su-exec lasidcommerce php artisan storage:link --force >/dev/null 2>&1 || true
fi

if [ "${LASIDCOMMERCE_RUN_MIGRATIONS:-false}" = "true" ]; then
    su-exec lasidcommerce php artisan migrate --force
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    su-exec lasidcommerce php artisan config:cache
    su-exec lasidcommerce php artisan route:cache
    su-exec lasidcommerce php artisan view:cache
fi

if [ "${1:-}" = "php-fpm" ]; then
    exec "$@"
fi

exec su-exec lasidcommerce "$@"
