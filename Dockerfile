# syntax=docker/dockerfile:1
# Imagen para el NAS (linux/arm64); también construye en amd64.
# Nunca ejecuta `php artisan migrate`: la base de producción ya tiene su esquema.
#
# Espejos chinos por defecto (el NAS está en China). Para usar los orígenes oficiales:
#   docker compose build --build-arg APT_MIRROR= --build-arg NPM_REGISTRY= --build-arg COMPOSER_MIRROR=
ARG APT_MIRROR=mirrors.aliyun.com
ARG NPM_REGISTRY=https://registry.npmmirror.com
ARG COMPOSER_MIRROR=https://mirrors.aliyun.com/composer/

# ---- Assets (Vite -> public/build) ----
FROM node:20-bookworm-slim AS assets
ARG NPM_REGISTRY
WORKDIR /app
COPY package.json package-lock.json ./
# npm reemplaza registry.npmjs.org de package-lock.json por el registro configurado.
RUN if [ -n "$NPM_REGISTRY" ]; then npm config set registry "$NPM_REGISTRY"; fi \
    && npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---- Dependencias PHP ----
FROM composer:2 AS composer

# ---- Aplicación ----
FROM php:8.2-apache
ARG APT_MIRROR
ARG COMPOSER_MIRROR

# Espejo de apt (incluye debian-security). La imagen trae ca-certificates, así que va por https.
RUN if [ -n "$APT_MIRROR" ]; then \
        for f in /etc/apt/sources.list.d/debian.sources /etc/apt/sources.list; do \
            [ -f "$f" ] || continue; \
            sed -ri \
                -e "s#https?://deb\.debian\.org/#https://${APT_MIRROR}/#g" \
                -e "s#https?://security\.debian\.org/debian-security#https://${APT_MIRROR}/debian-security#g" \
                -e "s#https?://security\.debian\.org/?([[:space:]]|\$)#https://${APT_MIRROR}/debian-security\1#g" \
                "$f"; \
        done; \
    fi

# mbstring, ctype, tokenizer, fileinfo, dom, openssl ya vienen compiladas en la imagen oficial.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath zip gd intl opcache \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-garabatos.ini"
COPY docker/mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
# El contexto puede llegar por SMB con permisos solo para el dueño.
RUN chmod 644 "$PHP_INI_DIR/conf.d/zz-garabatos.ini" /etc/apache2/mods-available/mpm_prefork.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
        > /etc/apache2/conf-available/garabatos.conf \
    && a2enconf garabatos

COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN if [ -n "$COMPOSER_MIRROR" ]; then composer config -g repos.packagist composer "$COMPOSER_MIRROR"; fi

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

# Permisos independientes del contexto (por SMB llegan solo para el dueño y Apache
# no puede leer .htaccess): directorios 755 y archivos 644. vendor/ lo genera composer
# dentro de la imagen; storage y bootstrap/cache se ajustan abajo para www-data.
RUN find . \( -path ./vendor -o -path ./storage -o -path ./bootstrap/cache \) -prune \
        -o -type d -exec chmod 755 {} + \
    && find . \( -path ./vendor -o -path ./storage -o -path ./bootstrap/cache \) -prune \
        -o -type f -exec chmod 644 {} + \
    && chmod 755 artisan

# storage/ puede no venir en el contexto (en el NAS es un volumen): crear la estructura
# antes de que `composer install` ejecute package:discover, que arranca Laravel.
RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache public/dist/img \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache public/dist/img \
    && chmod -R ug+rwX storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/garabatos-entrypoint
RUN chmod 755 /usr/local/bin/garabatos-entrypoint

EXPOSE 80
ENTRYPOINT ["garabatos-entrypoint"]
CMD ["apache2-foreground"]
