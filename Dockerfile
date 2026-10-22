# syntax=docker/dockerfile:1
# Imagen para el NAS (linux/arm64); también construye en amd64.
# Nunca ejecuta `php artisan migrate`: la base de producción ya tiene su esquema.

# ---- Assets (Vite -> public/build) ----
FROM node:20-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---- Dependencias PHP ----
FROM composer:2 AS composer

# ---- Aplicación ----
FROM php:8.2-apache

# mbstring, ctype, tokenizer, fileinfo, dom, openssl ya vienen compiladas en la imagen oficial.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath zip gd intl opcache \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-garabatos.ini"
COPY docker/mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/garabatos.conf \
    && a2enconf garabatos

COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p public/dist/img \
    && chown -R www-data:www-data storage bootstrap/cache public/dist/img \
    && chmod -R ug+rwX storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/garabatos-entrypoint
RUN chmod +x /usr/local/bin/garabatos-entrypoint

EXPOSE 80
ENTRYPOINT ["garabatos-entrypoint"]
CMD ["apache2-foreground"]
