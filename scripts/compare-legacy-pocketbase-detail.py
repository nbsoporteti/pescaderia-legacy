#!/usr/bin/env python3
import subprocess
import sqlite3
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
env = {}
for line in (ROOT / ".env").read_text().splitlines():
    if "=" in line:
        k, v = line.split("=", 1)
        env[k.strip()] = v.strip()
user, pw, db = env["MYSQL_USER"], env["MYSQL_PASSWORD"], "u207708227_pesquera"
M = ["docker", "compose", "exec", "-T", "db", "mariadb", f"-u{user}", f"-p{pw}", db, "-N", "-e"]

def sql(q):
    return subprocess.check_output(M + [q], text=True, cwd=ROOT).strip()

subprocess.check_call(["docker", "cp", "pocketbase-psw0x17s3rhdp8yav3n899yo-152834476221:/data/data.db", "/tmp/pb-data.db"])
conn = sqlite3.connect("/tmp/pb-data.db")
cur = conn.cursor()

print("=== DETALLE EMBARCACIONES / ACARREOS ===\n")
print("Legacy embarcaciones (30)     -> PocketBase coleccion 'acarreos' (30)")
print(f"  Legacy activos: {sql('SELECT COUNT(*) FROM embarcaciones WHERE eliminado=0')}")
print(f"  PB acarreos:    {cur.execute('SELECT COUNT(*) FROM acarreos').fetchone()[0]}")

print("\nLegacy embarcaciones2 (302)  -> PocketBase coleccion 'embarcaciones' (284)")
print(f"  Legacy activos: {sql('SELECT COUNT(*) FROM embarcaciones2 WHERE eliminado=0')}")
print(f"  PB embarcaciones: {cur.execute('SELECT COUNT(*) FROM embarcaciones').fetchone()[0]}")

print("\n=== MOVIMIENTOS: DELTA ===\n")
leg_total = int(sql("SELECT COUNT(*) FROM movimientos WHERE eliminado=0"))
leg_max_id = int(sql("SELECT MAX(id_movimiento) FROM movimientos"))
leg_max_fecha = sql("SELECT MAX(fecha) FROM movimientos WHERE fecha>'1900-01-01'")
leg_min_fecha_reciente = sql("SELECT MIN(fecha) FROM movimientos WHERE id_movimiento>16743 AND eliminado=0")

pb_total = cur.execute("SELECT COUNT(*) FROM movimientos WHERE eliminado=0 OR eliminado IS NULL").fetchone()[0]
pb_max_nro = cur.execute("SELECT MAX(nro_movimiento) FROM movimientos").fetchone()[0]
cur.execute("SELECT MAX(fecha) FROM movimientos")
pb_max_fecha = cur.fetchone()[0]

print(f"Legacy activos:     {leg_total} (id_movimiento max {leg_max_id})")
print(f"PocketBase:         {pb_total} (nro_movimiento max {pb_max_nro})")
print(f"Faltan en PB (aprox): {leg_total - pb_total} movimientos activos")
print(f"Ultima fecha legacy:  {leg_max_fecha}")
print(f"Ultima fecha PB:      {pb_max_fecha}")

# ids legacy > 16743
n = int(sql("SELECT COUNT(*) FROM movimientos WHERE id_movimiento>16743 AND eliminado=0"))
print(f"\nLegacy id_movimiento > 16743 (activos): {n} registros")
if n:
    sample = sql("SELECT id_movimiento, fecha, embarcacion, embarcacion2, tipo_movimiento, total FROM movimientos WHERE id_movimiento>16743 AND eliminado=0 ORDER BY id_movimiento LIMIT 5")
    print("Primeros 5 nuevos (legacy):")
    for line in sample.split("\n"):
        print(f"  {line.replace(chr(9), ' | ')}")

print("\n=== CAMPOS movimientos PocketBase ===")
cur.execute("SELECT fields FROM _collections WHERE name='movimientos'")
fields = json.loads(cur.fetchone()[0])
for f in fields:
    if f.get("name") in ("embarcacion", "acarreo", "proveedor", "trabajador", "tipo", "fecha", "total", "nro_movimiento", "eliminado"):
        print(f"  {f.get('name'):15} type={f.get('type')} relation={f.get('collectionId', '-')}")

print("\n=== RESUMEN PARA MIGRACION (sin ejecutar) ===")
print("  1. Importar ~1273 movimientos activos faltantes (ids > 16743 aprox.)")
print("  2. Mapear FK: movimientos.embarcacion -> PB.embarcacion (embarcaciones2 legacy)")
print("  3. Mapear FK: movimientos.embarcacion2 -> PB.acarreo (embarcaciones legacy)")
print("  4. Revisar movimientos_detalle y cheques asociados a ids nuevos")
print("  5. Actualizar embarcaciones faltantes (302 legacy2 vs 284 PB = 18?)")
