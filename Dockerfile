# syntax = docker/dockerfile:1

# Laravel Application Dockerfile for Fly.io
ARG PHP_VERSION=8.3
ARG NODE_VERSION=18
FROM fideloper/fly-laravel:${PHP_VERSION} as base

LABEL fly_launch_runtime="laravel"

# PHP extensions come pre-installed in fideloper/fly-laravel

RUN mkdir -p /var/www/html

WORKDIR /var/www/html

FROM base

COPY --link . .

# Storage should be on a persistent volume, so we optimize permissions here
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache \
    && mkdir -p database \
    && touch database/database.sqlite \
    && chmod -R ug+rwx storage bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database

# Composer install
RUN composer install --optimize-autoloader --no-dev \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

EXPOSE 8080
