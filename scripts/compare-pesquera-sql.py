#!/usr/bin/env python3
"""Analiza el dump SQL legacy para comparar con PocketBase (solo lectura)."""
import re
from pathlib import Path
from collections import defaultdict

sql_path = Path(r"c:\Users\Nico\Downloads\u207708227_pesquera (9).sql")
text = sql_path.read_text(encoding="utf-8", errors="replace")

TABLES = [
    "movimientos",
    "movimientos_detalle",
    "embarcaciones",
    "embarcaciones2",
    "proveedores",
    "trabajadores",
    "clasificaciones",
    "usuarios",
    "banco",
]

def count_inserts(table):
    pattern = rf"INSERT INTO `{table}`"
    blocks = text.split(pattern)[1:]
    rows = 0
    max_id = None
    id_col = {
        "movimientos": "id_movimiento",
        "movimientos_detalle": "id_movimiento_detalle",
        "embarcaciones": "id_embarcacion",
        "embarcaciones2": "id_embarcacion2",
        "proveedores": "id_proveedor",
        "trabajadores": "id_trabajador",
    }.get(table)
    dates = []
    for block in blocks:
        chunk = block.split(";")[0]
        tuples = re.findall(r"\(([^)]+(?:\([^)]*\)[^)]*)*)\)", chunk)
        for t in tuples:
            rows += 1
            parts = [p.strip().strip("'") for p in re.split(r",(?=(?:[^']*'[^']*')*[^']*$)", t)]
            if table == "movimientos" and len(parts) >= 2:
                dates.append(parts[1])  # fecha
                try:
                    mid = int(parts[0])
                    max_id = mid if max_id is None else max(max_id, mid)
                except ValueError:
                    pass
    return rows, max_id, (min(dates), max(dates)) if dates else (None, None)

print("=== SQL LEGACY (dump local) ===\n")
for t in TABLES:
    if t not in text:
        continue
    rows, max_id, dr = count_inserts(t)
    extra = ""
    if t == "movimientos":
        extra = f" | ids hasta {max_id} | fechas {dr[0]} .. {dr[1]}"
    print(f"{t:22} {rows:>6} filas{extra}")

print("\n=== MAPEO (según indicación) ===")
print("  embarcaciones   (SQL)  ->  embarcacion / acarreo (sistema nuevo)")
print("  embarcaciones2  (SQL)  ->  acarreo / acarreos2 (sistema nuevo)")
