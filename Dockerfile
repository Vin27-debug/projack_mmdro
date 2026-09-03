FROM node:22-bookworm-slim AS frontend

WORKDIR /app
npm run build
COPY composer.json composer.lock ./
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

EXPOSE 8080

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
