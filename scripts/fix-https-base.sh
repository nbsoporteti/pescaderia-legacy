#!/bin/bash
set -euo pipefail
source "$(dirname "$0")/common.sh"
legacy_cd

CFG=html/includes/config.php
cp "$CFG" "${CFG}.bak-fix-https-$(date +%Y%m%d%H%M%S)"

ROOT="$(pwd)"
python3 <<PY
from pathlib import Path

p = Path("$ROOT/html/includes/config.php")
t = p.read_text()

old = """\t\$scheme = (!empty(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
\t\$base_home = \$scheme . '://' . \$_SERVER['HTTP_HOST'] . '/';"""

new = """\t// Detras de Traefik: siempre HTTPS hacia el cliente
\t\$base_home = 'https://' . \$_SERVER['HTTP_HOST'] . '/';"""

if old in t:
    p.write_text(t.replace(old, new))
    print("config.php: base_home forzado a https")
elif "https://' . \$_SERVER['HTTP_HOST']" in t:
    print("config.php: ya corregido")
else:
    raise SystemExit("config.php: formato inesperado")
PY

grep -A1 'base_home' "$CFG"
