# Garabatos Stock en el NAS (Docker)

Corre en el UGREEN DH4300 Plus (ARM64) con dos contenedores: `app` (PHP 8.2 + Apache, puerto **8090**) y `db` (MariaDB 11.8, sin puerto publicado). El proyecto de compose se llama `garabatos`, así que no choca con flyon-exports.

> ⚠️ **No ejecutes `php artisan migrate`** (ni `migrate:fresh`, ni `db:seed`). La base de producción ya tiene su esquema y las migraciones no lo reproducen. Ni la imagen ni el arranque migran.

## Primer arranque

```sh
git clone https://github.com/santilondono/garabatos-stock.git && cd garabatos-stock
cp .env.docker.example .env        # app: APP_URL, DB_*, MAIL_*
cp .env.db.example .env.db         # MariaDB: MARIADB_*
docker compose run --rm --no-deps app php artisan key:generate --show   # pegar en APP_KEY
mkdir -p data/product-images
cp -n public/dist/img/* data/product-images/   # logo e imágenes versionadas (ver "Fotos")
docker compose up -d --build
```

`DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` del `.env` deben coincidir con `MARIADB_DATABASE`, `MARIADB_USER` y `MARIADB_PASSWORD` del `.env.db`. MariaDB crea esa base y ese usuario solo la primera vez, cuando el volumen está vacío; si se cambian después, hay que cambiarlos también dentro de MariaDB.

La app queda en `http://<ip-del-nas>:8090`. Para HTTPS: `tailscale serve --bg 8090` y poner esa URL `https://…ts.net` en `APP_URL`.

## Importar un dump

```sh
gunzip < dump.sql.gz | docker compose exec -T db mariadb -u<DB_USERNAME> -p<DB_PASSWORD> <DB_DATABASE>
```

Si el dump es `.sql` sin comprimir: `docker compose exec -T db mariadb -u… -p… <DB_DATABASE> < dump.sql`. Revisa que el dump no traiga `CREATE DATABASE`/`USE` con el nombre de la base de Hostinger.

## Fotos de productos

Las fotos viven en `./data/product-images`, que se monta sobre `public/dist/img`. Ahí se guardan las fotos nuevas que se suben desde la app. Copia ahí las fotos del servidor actual.

El volumen **tapa** lo que trae git en `public/dist/img`, incluido `garabatos_logo.png` (logo del login y del menú). Por eso el primer arranque las copia con `cp -n`, que no sobrescribe nada.

`./storage` (logs, sesiones y caché) también es persistente.

## Espejos (China)

El `Dockerfile` usa espejos chinos por defecto: Aliyun para apt y Composer, y npmmirror para npm. Para construir fuera de China con los orígenes oficiales, deja los argumentos vacíos:

```sh
docker compose build --build-arg APT_MIRROR= --build-arg NPM_REGISTRY= --build-arg COMPOSER_MIRROR=
docker compose up -d
```

## Actualizar

```sh
git pull --ff-only
docker compose up -d --build
```

## Respaldo

```sh
./scripts/backup-db.sh    # usa .env.db -> backups/garabatos-YYYY-MM-DD_HHMM.sql.gz; borra los de más de 14 días
```

Para que sea diario, agrega una tarea en cron (o en el programador de tareas de UGOS), por ejemplo `30 3 * * * /ruta/garabatos-stock/scripts/backup-db.sh`. Copia `backups/` fuera del NAS de vez en cuando.
