FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    nginx \
    supervisor \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    intl \
    zip \
    bcmath \
    opcache \
    exif \
    && rm -f /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/sites-available/default \
    && rm -f /var/www/html/index.nginx-debian.html \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js
COPY --from=node:22 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx


RUN npm install -g corepack \
    && corepack enable \
    && corepack prepare pnpm@latest --activate

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN git config --global --add safe.directory /var/www/html

# PHP deps
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Frontend deps + build
RUN pnpm install --frozen-lockfile
RUN pnpm build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/entrypoint.sh"]
