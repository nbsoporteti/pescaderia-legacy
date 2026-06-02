#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd
source .env
DB_NAME="${DB_NAME:-u207708227_pesquera}"

IDS=$(docker compose exec -T db mariadb -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$DB_NAME" -N -B -e \
  "SELECT GROUP_CONCAT(id_movimiento) FROM (SELECT id_movimiento FROM movimientos WHERE eliminado=0 ORDER BY id_movimiento DESC LIMIT 5) t")
echo "Testing with IDs: $IDS"
docker compose exec -T web curl -sS -o /tmp/test.pdf \
  -w '%{http_code} %{size_download} %{content_type}\n' \
  -X POST 'http://127.0.0.1/vistas/maestro_de_reportes/generar_pdf_general.php' \
  --data-urlencode "ids_csv=${IDS}" \
  --data-urlencode 'tipo_movimiento=' \
  --data-urlencode 'fecha_inicio=' \
  --data-urlencode 'fecha_fin='
echo "First bytes:"
docker compose exec -T web head -c 8 /tmp/test.pdf | od -An -tx1
echo "File size:"
docker compose exec -T web wc -c /tmp/test.pdf
