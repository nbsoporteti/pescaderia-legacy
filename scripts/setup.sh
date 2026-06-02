#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd

if [ ! -f .env ]; then
  ROOT_PW=$(openssl rand -base64 24 | tr -d '/+=' | head -c 32)
  APP_PW=$(openssl rand -base64 24 | tr -d '/+=' | head -c 32)
  cat > .env <<EOF
MYSQL_ROOT_PASSWORD=${ROOT_PW}
MYSQL_DATABASE=pescaderia
MYSQL_USER=pescaderia
MYSQL_PASSWORD=${APP_PW}
DB_NAME=u207708227_pesquera
EOF
  chmod 600 .env
  echo "Created .env"
fi

chmod +x scripts/*.sh 2>/dev/null || true
mkdir -p html mysql-init
docker compose build --no-cache
docker compose up -d
docker compose ps
