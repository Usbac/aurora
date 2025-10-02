FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader || true

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

COPY ./php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

EXPOSE 80
