#!/usr/bin/env bash
# Respaldo de la base del contenedor `db` en ./backups/garabatos-YYYY-MM-DD_HHMM.sql.gz.
# Borra los respaldos de más de 14 días. Credenciales: MARIADB_ROOT_PASSWORD y MARIADB_DATABASE de .env.db.
# Uso (también desde cron): /ruta/al/proyecto/scripts/backup-db.sh
set -euo pipefail

cd "$(dirname "$0")/.."

env_value() {
    # Lee KEY=valor de .env.db sin ejecutarlo; quita comillas envolventes.
    local value
    value=$(grep -E "^$1=" .env.db | tail -n 1 | cut -d= -f2-)
    value=${value%\"}; value=${value#\"}
    value=${value%\'}; value=${value#\'}
    printf '%s' "$value"
}

[ -f .env.db ] || { echo "No existe .env.db en $(pwd)" >&2; exit 1; }

DB_NAME=$(env_value MARIADB_DATABASE)
ROOT_PASSWORD=$(env_value MARIADB_ROOT_PASSWORD)
[ -n "$DB_NAME" ] && [ -n "$ROOT_PASSWORD" ] || { echo "Faltan MARIADB_DATABASE o MARIADB_ROOT_PASSWORD en .env.db" >&2; exit 1; }

mkdir -p backups
target="backups/garabatos-$(date +%Y-%m-%d_%H%M).sql.gz"
tmp="$target.partial"

# La contraseña va por MYSQL_PWD, no en la línea de comandos.
if docker compose exec -T -e MYSQL_PWD="$ROOT_PASSWORD" db \
    mariadb-dump -uroot --single-transaction --routines --triggers --events "$DB_NAME" \
    | gzip -9 > "$tmp"; then
    mv "$tmp" "$target"
    echo "Respaldo creado: $target"
else
    rm -f "$tmp"
    echo "Falló el respaldo" >&2
    exit 1
fi

find backups -name 'garabatos-*.sql.gz' -type f -mtime +14 -delete
