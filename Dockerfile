FROM node:22-alpine AS assets
WORKDIR /app
COPY . .
RUN npm ci && npm run build

FROM php:8.4-cli-alpine
RUN apk add --no-cache postgresql-dev && docker-php-ext-install pdo_pgsql
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
COPY --from=assets /app/public/build ./public/build
RUN composer install --optimize-autoloader --no-interaction && touch .env
