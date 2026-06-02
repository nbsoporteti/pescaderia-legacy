#!/bin/bash
# Uso: ./scripts/import-sql.sh /ruta/al/backup.sql
set -euo pipefail
SQL_FILE="${1:?Falta ruta al .sql}"
source "$(dirname "$0")/common.sh"
legacy_cd
source .env
DB_NAME="${DB_NAME:-u207708227_pesquera}"
docker compose exec -T db mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$DB_NAME" < "$SQL_FILE"
echo "Importación completada en $DB_NAME"
