#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd
source .env
DB_NAME="${DB_NAME:-u207708227_pesquera}"
docker compose exec -T db mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$DB_NAME" -e "
SELECT 'usuarios' AS tabla, COUNT(*) AS filas FROM usuarios
UNION ALL SELECT 'movimientos', COUNT(*) FROM movimientos
UNION ALL SELECT 'proveedores', COUNT(*) FROM proveedores;
"
