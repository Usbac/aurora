FROM node:26-alpine AS frontend

WORKDIR /app

COPY app/react/package*.json ./

RUN mkdir -p /public/assets/js

RUN npm install

COPY app/react .

RUN npm run build

FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    libicu-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=node:26-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:26-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY docker/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-interaction --prefer-dist

COPY . .

COPY --from=frontend /public/assets/js/admin.js app/public/assets/js/admin.js
COPY --from=frontend /public/assets/js/tinymce app/public/assets/js/tinymce

EXPOSE 80
