#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd
source .env
DB_NAME="${DB_NAME:-u207708227_pesquera}"

cp html/includes/db_connect.php "html/includes/db_connect.php.bak-$(date +%Y%m%d%H%M%S)"

cat > html/includes/db_connect.php <<PHP
<?php

\$localhost = "db";
\$username = "${MYSQL_USER}";
\$password = "${MYSQL_PASSWORD}";
\$dbname = "${DB_NAME}";

\$connect = new mysqli(\$localhost, \$username, \$password, \$dbname);
mysqli_set_charset(\$connect, "utf8");

if (\$connect->connect_error) {
  die("Connection Failed : " . \$connect->connect_error);
}

?>
PHP

if [ -f html/config.xml ]; then
  sed -i 's|<web_url>.*</web_url>|<web_url>https://pescaderia.nbsoporteti.com/</web_url>|' html/config.xml
fi

echo "db_connect.php actualizado para Docker (host=db)"
