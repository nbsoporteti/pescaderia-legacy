#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd
docker compose exec -T web php -r '
require "/var/www/html/includes/db_connect.php";
$r = $connect->query("SELECT COUNT(*) AS c FROM usuarios");
$row = $r->fetch_assoc();
echo "usuarios=" . $row["c"] . "\n";
'
