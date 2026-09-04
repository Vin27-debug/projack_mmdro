FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

FROM php:8.4-cli-bookworm

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "set -e; if [ -z \"${APP_KEY:-}\" ]; then echo 'APP_KEY is required for production.' >&2; exit 1; fi; php artisan migrate --force; php artisan optimize; exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
