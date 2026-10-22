#!/bin/sh
# Arranque del contenedor app. Sin migraciones: la base de producción ya tiene su esquema.
set -e

cd /var/www/html

# ./storage y las fotos llegan como volúmenes del host: recrear estructura y permisos.
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache public/dist/img

# config/filesystems.php define el link public/storage -> storage/app/public.
[ -e public/storage ] || php artisan storage:link

php artisan config:clear
php artisan route:clear
php artisan view:clear

# Al final, por si artisan (que corre como root) creó algún archivo, p. ej. un log.
chown -R www-data:www-data storage bootstrap/cache public/dist/img
chmod -R ug+rwX storage bootstrap/cache public/dist/img

exec docker-php-entrypoint "$@"
