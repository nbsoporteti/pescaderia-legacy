#!/usr/bin/env python3
import re
import sqlite3
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SQL = ROOT / "backup.sql"
PB_DB = "/data/data.db"

MAP = """
MAPEO (legacy SQL -> sistema nuevo PocketBase):
  embarcaciones   -> embarcacion / acarreo
  embarcaciones2  -> acarreo / acarreos2
  movimientos.embarcacion  -> FK embarcacion
  movimientos.embarcacion2 -> FK acarreo (embarcaciones2)
"""

def sql_counts():
    text = SQL.read_text(encoding="utf-8", errors="replace")
    out = {}
    for table in ["movimientos", "movimientos_detalle", "embarcaciones", "embarcaciones2", "proveedores", "trabajadores"]:
        parts = text.split(f"INSERT INTO `{table}`")[1:]
        rows = 0
        max_id = None
        min_f = max_f = None
        for block in parts:
            chunk = block.split(";")[0]
            for m in re.finditer(r"\((\d+),\s*'(\d{4}-\d{2}-\d{2})'", chunk):
                rows += 1
                mid = int(m.group(1))
                max_id = mid if max_id is None else max(max_id, mid)
                f = m.group(2)
                min_f = f if min_f is None or f < min_f else min_f
                max_f = f if max_f is None or f > max_f else max_f
            if table != "movimientos":
                rows += len(re.findall(r"\)\s*,\s*\(", chunk)) + (1 if chunk.strip().startswith("(") else 0)
                if table != "movimientos" and parts:
                    # recount simpler
                    pass
        if table != "movimientos":
            rows = sum(len(re.findall(r"\([^;]+?\)", block.split(";")[0])) for block in parts)
            # fix double count - use mariadb instead
            rows = None
        out[table] = {"rows": rows, "max_id": max_id, "fecha_min": min_f, "fecha_max": max_f}
    return out

def mariadb_counts():
    env = {}
    for line in (ROOT / ".env").read_text().splitlines():
        if "=" in line:
            k, v = line.split("=", 1)
            env[k.strip()] = v.strip()
    user, pw, db = env["MYSQL_USER"], env["MYSQL_PASSWORD"], "u207708227_pesquera"
    tables = ["movimientos", "movimientos_detalle", "embarcaciones", "embarcaciones2", "proveedores", "trabajadores"]
    out = {}
    for t in tables:
        q = f"SELECT COUNT(*) FROM {t}"
        r = subprocess.check_output(
            ["docker", "compose", "exec", "-T", "db", "mariadb", f"-u{user}", f"-p{pw}", db, "-N", "-e", q],
            text=True,
            cwd=ROOT,
        ).strip()
        out[t] = {"rows": int(r)}
        if t == "movimientos":
            stats = subprocess.check_output(
                [
                    "docker", "compose", "exec", "-T", "db", "mariadb", f"-u{user}", f"-p{pw}", db, "-N", "-e",
                    "SELECT MIN(fecha), MAX(fecha), MAX(id_movimiento), SUM(eliminado=0), SUM(eliminado=1) FROM movimientos",
                ],
                text=True,
                cwd=ROOT,
            ).strip().split("\t")
            out[t].update({
                "fecha_min": stats[0],
                "fecha_max": stats[1],
                "max_id": int(stats[2]),
                "activos": int(stats[3]),
                "eliminados": int(stats[4]),
            })
    return out

def pb_schema_and_counts():
    import json
    subprocess.check_call(["docker", "cp", "pocketbase-psw0x17s3rhdp8yav3n899yo-152834476221:/data/data.db", "/tmp/pb-data.db"])
    conn = sqlite3.connect("/tmp/pb-data.db")
    cur = conn.cursor()
    cur.execute("SELECT name, fields FROM _collections")
    cols = {}
    for name, fields_raw in cur.fetchall():
        fields = json.loads(fields_raw)
        cols[name] = [f.get("name") for f in fields if f.get("name")]
    counts = {}
    for name in cols:
        if name.startswith("_"):
            continue
        try:
            cur.execute(f'SELECT COUNT(*) FROM "{name}"')
            counts[name] = cur.fetchone()[0]
        except Exception:
            pass
    # extra stats for movimientos-like collections
    extras = {}
    for name in cols:
        if "movimiento" not in name.lower():
            continue
        flds = cols[name]
        date_f = next((f for f in flds if "fecha" in f.lower()), None)
        if date_f:
            try:
                cur.execute(f'SELECT MIN("{date_f}"), MAX("{date_f}"), COUNT(*) FROM "{name}"')
                mn, mx, c = cur.fetchone()
                extras[name] = {"fecha_min": mn, "fecha_max": mx, "rows": c}
            except Exception:
                pass
    return {"collections": cols, "counts": counts, "extras": extras}

print("=" * 60)
print("COMPARACION LEGACY SQL vs POCKETBASE (pescaderia.amjsoft.com)")
print("=" * 60)
print(MAP)

legacy = mariadb_counts()
pb = pb_schema_and_counts()

print("\n--- LEGACY MariaDB (mismo contenido que tu .sql) ---")
for t, v in legacy.items():
    line = f"  {t:20} {v['rows']:>6} filas"
    if t == "movimientos":
        line += f" | id max {v['max_id']} | fechas {v['fecha_min']} .. {v['fecha_max']} | activos {v['activos']} eliminados {v['eliminados']}"
    print(line)

print("\n--- POCKETBASE colecciones ---")
for name, fields in sorted(pb["collections"].items()):
    if name.startswith("_"):
        continue
    cnt = pb["counts"].get(name, "?")
    print(f"  {name:20} {cnt:>6} registros | campos: {', '.join(fields[:12])}{'...' if len(fields)>12 else ''}")

print("\n--- MAPEO DE TABLAS ---")
mapping = [
    ("movimientos", "movimientos ?"),
    ("movimientos_detalle", "movimientos_detalle ?"),
    ("embarcaciones", "embarcacion / acarreo"),
    ("embarcaciones2", "acarreo / acarreos2"),
    ("proveedores", "proveedores"),
    ("trabajadores", "trabajadores"),
    ("clasificaciones", "clasificaciones ?"),
    ("banco", "banco ?"),
]
for leg, neu in mapping:
    lc = legacy.get(leg, {}).get("rows", "-")
    # guess pb collection name
    guess = neu.split()[0].replace("?", "")
    pc = pb["counts"].get(guess, pb["counts"].get(leg, "?"))
    print(f"  {leg:22} ({lc:>6})  ->  {neu:25} PocketBase: {pc}")

# movimientos diff estimate
leg_m = legacy.get("movimientos", {})
pb_mov = None
for k in pb["counts"]:
    if "movimiento" in k.lower():
        pb_mov = (k, pb["counts"][k])
if pb_mov:
    diff = leg_m.get("rows", 0) - pb_mov[1]
    print(f"\n--- DELTA movimientos ---")
    print(f"  Legacy: {leg_m.get('rows')} | PocketBase ({pb_mov[0]}): {pb_mov[1]} | diferencia: {diff:+d}")
    for name, ex in pb.get("extras", {}).items():
        print(f"  PB {name}: fechas {ex.get('fecha_min')} .. {ex.get('fecha_max')} ({ex.get('rows')} filas)")
