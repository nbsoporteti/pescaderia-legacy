#!/bin/bash
# Opcional: servir pescaderia.amjsoft.com desde el PHP legado (avanzado).
set -euo pipefail
source "$(dirname "$0")/common.sh"
LEGACY="$(legacy_root)"
COOLIFY_APP=/data/coolify/applications/psw0x17s3rhdp8yav3n899yo
RULE='(Host(`pescaderia.nbsoporteti.com`) || Host(`pescaderia.amjsoft.com`)) && PathPrefix(`/`)'

echo "=== pescaderia.amjsoft.com -> sistema PHP de produccion ==="

if [ -f "$COOLIFY_APP/docker-compose.yaml" ]; then
  cp "$COOLIFY_APP/docker-compose.yaml" "$COOLIFY_APP/docker-compose.yaml.bak-amjsoft-alias-$(date +%Y%m%d%H%M%S)"
  python3 <<PY
from pathlib import Path
p = Path("$COOLIFY_APP/docker-compose.yaml")
t = p.read_text()
old = "Host(\`pescaderia.amjsoft.com\`) && PathPrefix(\`/\`)"
if old not in t:
    print("Coolify: regla amjsoft ya no presente")
else:
    t = t.replace("traefik.enable=true", "traefik.enable=false")
    p.write_text(t)
    print("Coolify: traefik desactivado en app PocketBase")
PY
  (cd "$COOLIFY_APP" && docker compose up -d) || true
fi

cd "$LEGACY"
cp docker-compose.yaml "docker-compose.yaml.bak-amjsoft-alias-$(date +%Y%m%d%H%M%S)"
python3 <<PY
from pathlib import Path
p = Path("$LEGACY/docker-compose.yaml")
t = p.read_text()
rule = "(Host(\`pescaderia.nbsoporteti.com\`) || Host(\`pescaderia.amjsoft.com\`)) && PathPrefix(\`/\`)"
single = "Host(\`pescaderia.nbsoporteti.com\`) && PathPrefix(\`/\`)"
if rule in t:
    print("Legacy: reglas duales ya aplicadas")
elif single in t:
    print("Legacy: agregar dominio amjsoft desde Coolify UI (Domains), no labels manuales")
else:
    raise SystemExit("Legacy: revisar docker-compose.yaml")
PY

CFG="$LEGACY/html/includes/config.php"
if [ -f "$CFG" ] && ! grep -q 'HTTP_HOST' "$CFG" 2>/dev/null; then
  ./scripts/fix-https-base.sh
fi

docker compose up -d
echo "Listo. Probar ambos dominios."
