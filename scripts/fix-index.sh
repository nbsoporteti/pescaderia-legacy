#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd

cp html/index.php "html/index.php.bak-zip-$(date +%Y%m%d%H%M%S)"
cat > html/index.php <<'PHP'
<?php
session_start();
if (!isset($_SESSION['correo_usuario'])) {
    header('Location: login.php');
    exit;
}
header('Location: home.php');
exit;
PHP

docker compose build web --no-cache
docker compose up -d
echo "index.php corregido y contenedor reconstruido"
