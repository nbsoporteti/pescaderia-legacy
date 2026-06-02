#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd
source .env
DB_NAME="${DB_NAME:-u207708227_pesquera}"
SQL_FILE="${1:-backup.sql}"

echo "Creando base ${DB_NAME} si no existe..."
docker compose exec -T db mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -e \
  "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci;
   GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${MYSQL_USER}'@'%';
   FLUSH PRIVILEGES;"

echo "Importando ${SQL_FILE}..."
docker compose exec -T db mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$DB_NAME" < "$SQL_FILE"

echo "Tablas importadas:"
docker compose exec -T db mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$DB_NAME" -e "SHOW TABLES;" | head -20
echo "OK"
