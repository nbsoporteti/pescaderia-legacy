#!/usr/bin/env python3
"""Parche idempotente para generar_pdf_general.php (reporte PDF legado)."""
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
path = ROOT / "html/vistas/maestro_de_reportes/generar_pdf_general.php"
text = path.read_text(encoding="utf-8", errors="replace")
changed = []

# 1) Buffer de salida + no mostrar errores en pantalla (FPDF exige salida limpia)
header_old = """<?php
require '../../includes/db_connect.php';
require '../../includes/WriteHTML.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);"""

header_new = """<?php
ob_start();
require '../../includes/db_connect.php';
require '../../includes/WriteHTML.php';

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED);"""

if header_old in text:
    text = text.replace(header_old, header_new, 1)
    changed.append("header ob_start + display_errors off")
elif "ob_start();" not in text.split("require", 1)[0]:
    text = text.replace("<?php\n", "<?php\nob_start();\n", 1)
    text = text.replace("ini_set('display_errors', 1);", "ini_set('display_errors', '0');")
    text = text.replace("ini_set('display_startup_errors', 1);", "ini_set('display_startup_errors', '0');")
    if "ini_set('log_errors', '1');" not in text:
        text = text.replace(
            "error_reporting(E_ALL & ~E_DEPRECATED);",
            "ini_set('log_errors', '1');\nerror_reporting(E_ALL & ~E_DEPRECATED);",
            1,
        )
    changed.append("header ob_start (fallback)")

# 2) $tipo_movimiento antes de $showGasto
tipo_old = """    $F_emb2        = isset($_POST['embarcacion2'])  ? (int)$_POST['embarcacion2']  : 0;
    $showGasto     = ((int)$F_emb > 0) || ($tipo_movimiento === '3');"""

tipo_new = """    $F_emb2        = isset($_POST['embarcacion2'])  ? (int)$_POST['embarcacion2']  : 0;
    $tipo_movimiento = $_POST['tipo_movimiento'] ?? '';
    $showGasto     = ((int)$F_emb > 0) || ($tipo_movimiento === '3');"""

if tipo_old in text:
    text = text.replace(tipo_old, tipo_new, 1)
    changed.append("tipo_movimiento order")

dup = """    $ids_sql = implode(',', $ids_movimiento);
    $tipo_movimiento = $_POST['tipo_movimiento'] ?? '';
    $contabilizarGastoEnSaldo"""

fix = """    $ids_sql = implode(',', $ids_movimiento);
    $contabilizarGastoEnSaldo"""

if dup in text:
    text = text.replace(dup, fix, 1)
    changed.append("remove duplicate tipo_movimiento")

# 3) Limpiar buffer justo antes de enviar el PDF
output_old = "    $pdf->Output('D', 'Informe_Movimientos.pdf');"
output_new = """    if (ob_get_length()) {
        ob_end_clean();
    }
    $pdf->Output('D', 'Informe_Movimientos.pdf');"""

if output_old in text and output_new not in text:
    text = text.replace(output_old, output_new, 1)
    changed.append("clean buffer before Output")

if not changed:
    print("OK: generar_pdf_general.php already patched")
else:
    path.write_text(text, encoding="utf-8")
    print("OK: generar_pdf_general.php patched:", ", ".join(changed))
