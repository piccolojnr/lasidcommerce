# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
RUN apk add --no-cache git \
    && docker-php-ext-install exif
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM composer:2 AS frontend
WORKDIR /app
RUN apk add --no-cache nodejs npm \
    && docker-php-ext-install exif \
    && npm install -g pnpm@10.26.2
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml .npmrc ./
RUN pnpm install --frozen-lockfile
COPY . .
RUN rm -f bootstrap/cache/*.php
COPY --from=vendor /app/vendor ./vendor
RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= php artisan wayfinder:generate --with-form
RUN pnpm run build

FROM php:8.4-fpm-alpine AS runtime

ARG UID=1000
ARG GID=1000

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    freetype-dev \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    sqlite-dev \
    su-exec \
    unzip \
    zip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && addgroup -g "${GID}" lasidcommerce \
    && adduser -D -G lasidcommerce -u "${UID}" lasidcommerce \
    && sed -i 's/^user = .*/user = lasidcommerce/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = lasidcommerce/' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --chown=lasidcommerce:lasidcommerce . .
COPY --from=vendor --chown=lasidcommerce:lasidcommerce /app/vendor ./vendor
COPY --from=frontend --chown=lasidcommerce:lasidcommerce /app/public/build ./public/build
COPY docker/php/php.ini /usr/local/etc/php/conf.d/lasidcommerce.ini
COPY docker/entrypoint.sh /usr/local/bin/lasidcommerce-entrypoint

RUN chmod +x /usr/local/bin/lasidcommerce-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && rm -f bootstrap/cache/*.php \
    && composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi \
    && chown -R lasidcommerce:lasidcommerce storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["lasidcommerce-entrypoint"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS web

WORKDIR /var/www/html

COPY docker/nginx/default.conf /etc/nginx/templates/default.conf.template
COPY --from=runtime /var/www/html/public ./public

RUN mkdir -p /var/www/html/storage/app/public \
    && rm -rf /var/www/html/public/storage \
    && ln -s /var/www/html/storage/app/public /var/www/html/public/storage
