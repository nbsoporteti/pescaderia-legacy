# Pescadería — sistema PHP legado

Stack Docker (PHP 8.2 + Apache + MariaDB) para **`https://pescaderia.nbsoporteti.com`**.

Los archivos PHP de producción **no van en Git** (volumen `html/`). Este repo trae infraestructura, scripts y documentación.

## Inicio rápido (VPS o Coolify)

```bash
cp .env.example .env
# Editar .env con claves seguras
./scripts/setup.sh
./scripts/import-backup.sh backup.sql   # opcional
./scripts/deploy-html.sh public_html.zip
./scripts/patch-connect.sh
```

## Coolify (panel gráfico)

Ver [docs/coolify.md](docs/coolify.md): recurso **Docker Compose**, compose en la raíz de este repo, dominio solo `pescaderia.nbsoporteti.com`.

## Documentación

| Archivo | Contenido |
|---------|-----------|
| [docs/migracion-dual.md](docs/migracion-dual.md) | Convivencia con `pescaderia.amjsoft.com` (app nueva) |
| [docs/coolify.md](docs/coolify.md) | Pasos en el panel Coolify |
| [scripts/dns-client/LEEME.txt](scripts/dns-client/LEEME.txt) | Fix DNS para usuarios con NXDOMAIN |

## Estructura

```
docker-compose.yaml   # web + db
Dockerfile
html/                 # PHP (volumen, no commitear)
scripts/              # import, deploy, parches, tests
docs/
```

## Variables

Copiar `.env.example` → `.env`. Base de datos del legado: `DB_NAME=u207708227_pesquera`.
