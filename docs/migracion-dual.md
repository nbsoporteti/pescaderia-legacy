# Pescadería: dos sistemas en paralelo

| Dominio | Sistema | Stack |
|---------|---------|--------|
| `https://pescaderia.amjsoft.com` | App nueva | Coolify `pescaderia-app` (PocketBase) |
| `https://pescaderia.nbsoporteti.com` | PHP legado | Este repo → [coolify.md](./coolify.md) |

## 1. DNS

| Tipo | Nombre | Valor |
|------|--------|-------|
| A | `pescaderia` | `177.7.58.246` |

(Zona `nbsoporteti.com` para el legado; `amjsoft.com` para la app nueva.)

## 2. Base de datos

- Base: **`u207708227_pesquera`**
- Importar: `./scripts/import-backup.sh backup.sql`

## 3. Archivos PHP

Zip desde hPanel → en el VPS (clone de este repo):

```bash
git clone git@github.com:nbsoporteti/pescaderia-legacy.git
cd pescaderia-legacy
./scripts/deploy-html.sh public_html.zip
./scripts/patch-connect.sh
```

## 4. Comprobar

- `https://pescaderia.amjsoft.com` → app nueva
- `https://pescaderia.nbsoporteti.com/login.php` → legado PHP
