<?php
ob_start();
require '../../includes/db_connect.php';
require '../../includes/reportes_where.inc.php';
require '../../includes/WriteHTML.php';

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED);

if ((!empty($_POST['export_mode']) && $_POST['export_mode'] === 'filters') || !empty($_POST['ids_csv']) || !empty($_POST['id_movimiento'])) {

    $groupBy       = $_POST['group_by'] ?? 'auto';
    $F_proveedor   = isset($_POST['proveedor'])     ? (int)$_POST['proveedor']     : 0;
    $F_trabajador  = isset($_POST['trabajador'])    ? (int)$_POST['trabajador']    : 0;
    $F_emb         = isset($_POST['embarcacion'])   ? (int)$_POST['embarcacion']   : 0;
    $F_emb2        = isset($_POST['embarcacion2'])  ? (int)$_POST['embarcacion2']  : 0;
    $tipo_movimiento = $_POST['tipo_movimiento'] ?? '';
    $showGasto     = ((int)$F_emb > 0) || ($tipo_movimiento === '3');
    $F_tipo        = ($_POST['tipo_movimiento'] ?? '') !== '' ? (int)$_POST['tipo_movimiento'] : 0;
    $F_clasif      = isset($_POST['clasificacion']) ? (int)$_POST['clasificacion'] : 0;
    $F_inicio      = $_POST['fecha_inicio'] ?? '';
    $F_fin         = $_POST['fecha_fin']    ?? '';

    $contabilizarGastoEnSaldo = ((string)$tipo_movimiento === '3');

    if (!empty($_POST['export_mode']) && $_POST['export_mode'] === 'filters') {
        $built = reportes_build_where($connect, $_POST);
        $whereSql = reportes_where_sql($built);
        $sql = "SELECT m.*, DATE_FORMAT(m.fecha, '%d-%m-%Y') AS fecha " . $built['joins'] . " WHERE $whereSql ORDER BY m.fecha ASC, m.id_movimiento ASC";
        $rs = mysqli_query($connect, $sql);
        $movimientos = $rs ? mysqli_fetch_all($rs, MYSQLI_ASSOC) : [];
    } elseif (!empty($_POST['ids_csv'])) {
        $raw = preg_replace('/[^0-9,]/', '', $_POST['ids_csv']);
        $ids_movimiento = array_filter(array_map('intval', explode(',', $raw)));
        if (empty($ids_movimiento)) {
            exit('No se recibieron IDs válidos.');
        }
        $ids_sql = implode(',', $ids_movimiento);
        $sql = "SELECT movimientos.*, DATE_FORMAT(fecha, '%d-%m-%Y') AS fecha
            FROM movimientos
            WHERE id_movimiento IN ($ids_sql)";
        $rs  = mysqli_query($connect, $sql);
        $movimientos = mysqli_fetch_all($rs, MYSQLI_ASSOC);
    } else {
        $ids_movimiento = array_map('intval', (array)$_POST['id_movimiento']);
        if (empty($ids_movimiento)) {
            exit('No se recibieron IDs válidos.');
        }
        $ids_sql = implode(',', $ids_movimiento);
        $sql = "SELECT movimientos.*, DATE_FORMAT(fecha, '%d-%m-%Y') AS fecha
            FROM movimientos
            WHERE id_movimiento IN ($ids_sql)";
        $rs  = mysqli_query($connect, $sql);
        $movimientos = mysqli_fetch_all($rs, MYSQLI_ASSOC);
    }

    if (!$movimientos) { echo 'No se encontraron movimientos.'; exit; }

    // === PDF ===
    $pdf = new PDF_HTML();
    $pdf->AddPage('L');

    // Márgenes
    $leftMargin   = 8;
    $topMargin    = 10;
    $rightMargin  = 8;
    $bottomMargin = 15;

    $pdf->SetMargins($leftMargin, $topMargin, $rightMargin);
    $pdf->SetAutoPageBreak(true, $bottomMargin);

    // === Anchos DINÁMICOS que siempre caben en A4 Landscape ===
    $lineH = 6;

    $pageW    = $pdf->GetPageWidth();
    $usableW  = $pageW - $leftMargin - $rightMargin; // ancho útil

    $wFecha   = 22;
    $wMov     = 16;
    $wMonto   = 22;

    $numMontoCols = $showGasto ? 5 : 4;

    $wDetalle = $usableW - ($wFecha + $wMov + $numMontoCols * $wMonto);

    if ($wDetalle < 110) {
        $faltante = 110 - $wDetalle;
        $wDetalle = 110;
        $restaPorMonto = ceil($faltante / $numMontoCols);
        $wMonto = max(18, $wMonto - $restaPorMonto);
    }

    // ---- helpers ----
    $drawSeparator = function() use ($pdf, $leftMargin, $rightMargin) {
        $pdf->Ln(6);
        $x1 = $leftMargin;
        $x2 = $pdf->GetPageWidth() - $rightMargin;
        $y  = $pdf->GetY();
        $pdf->SetDrawColor(60,60,60);
        $pdf->SetLineWidth(0.6);
        $pdf->Line($x1, $y, $x2, $y);
        $pdf->SetLineWidth(0.2);
        $pdf->Ln(6);
    };

    $header = function() use ($pdf, $tipo_movimiento) {
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'INFORME DE MOVIMIENTOS', 0, 1, 'C');

        if ($tipo_movimiento !== '' && $tipo_movimiento !== null) {
            $map = [
                '1' => 'Ingresos',
                '2' => 'Egresos',
                '3' => 'Gastos',
                '4' => 'Pagos',
            ];
            $txt = $map[(string)$tipo_movimiento] ?? '—';
            $pdf->SetFont('Arial', 'B', 12);   // solo Arial
            $pdf->Cell(0, 6, '(' . $txt . ')', 0, 1, 'C');
            $pdf->Ln(6);
        } else {
            $pdf->Ln(6);
        }

        $pdf->SetFont('Arial', '', 9);
    };

    $tableHeader = function(bool $showGasto) use ($pdf, $wFecha, $wDetalle, $wMov, $wMonto) {
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($wFecha,   7, 'FECHA',   1, 0, 'C');
        $pdf->Cell($wDetalle, 7, 'DETALLE', 1, 0, 'L');
        $pdf->Cell($wMov,     7, 'N° MOV',  1, 0, 'C');
        $pdf->Cell($wMonto,   7, 'INGRESO', 1, 0, 'C');
        $pdf->Cell($wMonto,   7, 'EGRESO',  1, 0, 'C');
        $pdf->Cell($wMonto,   7, 'PAGO',    1, 0, 'C');
        if ($showGasto) {
            $pdf->Cell($wMonto, 7, 'GASTO', 1, 0, 'C');
        }
        $pdf->Cell($wMonto,   7, 'SALDO',   1, 1, 'C');
    };

    // Mini-total por GRUPO (solo monto en negrita; ancho = MONTO)
    $printGroupTotals = function($ing, $egr, $pago, $gasto) use ($pdf, $leftMargin, $wFecha, $wDetalle, $wMov, $wMonto) {
        $tableTotalW = $wFecha + $wDetalle + $wMov + 5*$wMonto;
        $soloGastos  = ($ing == 0 && $egr == 0 && $pago == 0 && $gasto > 0);

        $miniW = $wMonto;
        $x = $leftMargin + $tableTotalW - $miniW;

        $pdf->SetX($x);
        $pdf->SetFont('Arial','B',9);
        if ($soloGastos) {
            $pdf->Cell($miniW, 7, '$'.number_format($gasto, 0, ',', '.'), 1, 1, 'C');
        } else {
            $total = $ing - $egr - $pago;
            $pdf->Cell($miniW, 7, '$'.number_format($total, 0, ',', '.'), 1, 1, 'C');
        }
    };

    // Cuenta líneas
    function nbLines($pdf, $w, $txt) {
        if ($w <= 0) $w = $pdf->GetPageWidth();
        $innerPadding = 2;
        $max = max(1, $w - 2 * $innerPadding);
        $s = trim((string)$txt);
        if ($s === '') return 1;
        $paragraphs = preg_split("/\r\n|\r|\n/", $s);
        $lines = 0;
        foreach ($paragraphs as $p) {
            $words = preg_split('/\s+/', $p);
            $lineWidth = 0;
            foreach ($words as $i => $word) {
                $chunk  = ($i === 0 ? '' : ' ') . $word;
                $wChunk = $pdf->GetStringWidth($chunk);
                if ($lineWidth + $wChunk <= $max) {
                    $lineWidth += $wChunk;
                } else {
                    $lines++;
                    $lineWidth = $pdf->GetStringWidth($word);
                }
            }
            $lines++;
        }
        return max(1, $lines);
    }

    $spaceLeft = function() use ($pdf, $bottomMargin) {
        return $pdf->GetPageHeight() - $bottomMargin - $pdf->GetY();
    };
    $rowHeightFor = function($txt) use ($pdf, $wDetalle, $lineH) {
        return max(1, nbLines($pdf, $wDetalle, (string)$txt)) * $lineH;
    };

    $hTituloGrupo   = 5;
    $hGapTitulo     = 1;
    $hCabeceraTabla = 7;

    // Alturas para reservar espacio (evitar que el total quede solo)
    $hMiniTotales = 7;   // altura del total (una sola celda)
    $hSep         = 12;  // aprox. alto del separador: Ln + línea + Ln

    // Dibuja fila segura
    $safeRow = function(
        $fecha, $detalleTxt, $numMov,
        $ingreso, $egreso, $pago, $gasto, $saldo,
        bool $showGasto
    ) use ($pdf, $wFecha, $wDetalle, $wMov, $wMonto, $lineH, $tableHeader, $bottomMargin) {

        $nLines = max(1, nbLines($pdf, $wDetalle, $detalleTxt));
        $rowH   = $nLines * $lineH;
        $usableBottom = $pdf->GetPageHeight() - $bottomMargin;
        if ($pdf->GetY() + $rowH > $usableBottom) {
            $pdf->AddPage('L');
            $tableHeader($showGasto);
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Bordes fijos
        $pdf->Rect($x, $y, $wFecha, $rowH);                            // FECHA
        $pdf->Rect($x + $wFecha, $y, $wDetalle, $rowH);                // DETALLE
        $pdf->Rect($x + $wFecha + $wDetalle, $y, $wMov, $rowH);        // N° MOV

        // Construye columnas de montos en orden
        $cols = [
            ['key'=>'INGRESO','val'=>$ingreso],
            ['key'=>'EGRESO', 'val'=>$egreso],
            ['key'=>'PAGO',   'val'=>$pago],
        ];
        if ($showGasto) {
            $cols[] = ['key'=>'GASTO','val'=>$gasto];
        }
        $cols[] = ['key'=>'SALDO','val'=>$saldo];

        // Dibuja bordes de las columnas de monto
        $xCol = $x + $wFecha + $wDetalle + $wMov;
        foreach ($cols as $_) {
            $pdf->Rect($xCol, $y, $wMonto, $rowH);
            $xCol += $wMonto;
        }

        // Contenido
        $fmt = function($v){ return $v === '' ? '' : '$'.number_format($v, 0, ',', '.'); };

        $pdf->SetXY($x, $y);
        $pdf->Cell($wFecha, $rowH, $fecha, 0, 0, 'C');

        $pdf->SetXY($x + $wFecha, $y);
        $pdf->MultiCell($wDetalle, $lineH, (string)$detalleTxt, 0, 'L');

        $pdf->SetXY($x + $wFecha + $wDetalle, $y);
        $pdf->Cell($wMov, $rowH, $numMov, 0, 0, 'C');

        // Montos
        $xCol = $x + $wFecha + $wDetalle + $wMov;
        foreach ($cols as $_) {
            $val = $_['key'] === 'SALDO' ? $saldo : $_['val'];
            $pdf->SetXY($xCol, $y);
            $pdf->Cell($wMonto, $rowH, $fmt($val), 0, 0, 'C');
            $xCol += $wMonto;
        }

        $pdf->SetXY($x, $y + $rowH);
    };

    $header();

    // === Agrupación ===
    $agrupados = [];
    foreach ($movimientos as $m) {
        $nombre = '-';
        $tipoGrp = '';

        switch ($groupBy) {
            case 'trabajador':
                if ((int)$m['trabajador'] !== 0) {
                    $q = "SELECT nombre_trabajador AS n FROM trabajadores WHERE id_trabajador=".(int)$m['trabajador'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Trabajador no encontrado';
                } else {
                    $nombre = '—';
                }
                $tipoGrp = 'Trabajador';
                break;

            case 'proveedor':
                if ((int)$m['proveedor'] !== 0) {
                    $q = "SELECT nombre_proveedor AS n FROM proveedores WHERE id_proveedor=".(int)$m['proveedor'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Proveedor no encontrado';
                } else {
                    $nombre = '—';
                }
                $tipoGrp = 'Proveedor';
                break;

            case 'embarcacion': // Acarreo
                if ((int)$m['embarcacion'] !== 0) {
                    $q = "SELECT nombre_embarcacion AS n FROM embarcaciones WHERE id_embarcacion=".(int)$m['embarcacion'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Acarreo no encontrado';
                } else {
                    $nombre = '—';
                }
                $tipoGrp = 'Acarreo';
                break;

            case 'embarcacion2': // Embarcacion
                if ((int)$m['embarcacion2'] !== 0) {
                    $q = "SELECT nombre_embarcacion2 AS n FROM embarcaciones2 WHERE id_embarcacion2=".(int)$m['embarcacion2'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Embarcacion no encontrada';
                } else {
                    $nombre = '—';
                }
                $tipoGrp = 'Embarcacion';
                break;

            default: // AUTO: tu lógica anterior
                if ((int)$m['trabajador'] !== 0) {
                    $q = "SELECT nombre_trabajador AS n FROM trabajadores WHERE id_trabajador=".(int)$m['trabajador'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Trabajador no encontrado';
                    $tipoGrp = 'Trabajador';
                } elseif ((int)$m['proveedor'] !== 0) {
                    $q = "SELECT nombre_proveedor AS n FROM proveedores WHERE id_proveedor=".(int)$m['proveedor'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Proveedor no encontrado';
                    $tipoGrp = 'Proveedor';
                } elseif ((int)$m['embarcacion'] !== 0) {
                    $q = "SELECT nombre_embarcacion AS n FROM embarcaciones WHERE id_embarcacion=".(int)$m['embarcacion'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Acarreo no encontrado';
                    $tipoGrp = 'Acarreo';
                } elseif ((int)$m['embarcacion2'] !== 0) {
                    $q = "SELECT nombre_embarcacion2 AS n FROM embarcaciones2 WHERE id_embarcacion2=".(int)$m['embarcacion2'];
                    $r = mysqli_query($connect, $q);
                    $nombre = ($r && mysqli_num_rows($r)) ? mysqli_fetch_assoc($r)['n'] : 'Embarcacion no encontrada';
                    $tipoGrp = 'Embarcacion';
                } else {
                    $tipoGrp = '—';
                    $nombre  = '—';
                }
                break;
        }

        if (!isset($agrupados[$nombre])) {
            $agrupados[$nombre] = ['tipo' => $tipoGrp, 'movs' => []];
        }
        $agrupados[$nombre]['movs'][] = $m;
    }

    $totalIngreso = 0;
    $totalEgreso  = 0;
    $totalPago    = 0;
    $totalGasto   = 0;

    $isFirstGroup = true;
    foreach ($agrupados as $nombre => $info) {
        $firstMov = $info['movs'][0];
        $rsPeek = mysqli_query(
            $connect,
            "SELECT detalle_anticipo
            FROM movimientos_detalle
            WHERE id_movimiento = " . intval($firstMov['id_movimiento']) . "
            ORDER BY id_movimiento_detalle ASC
            LIMIT 1"
        );
        $firstDetailTxt = ($rsPeek && mysqli_num_rows($rsPeek))
            ? mysqli_fetch_assoc($rsPeek)['detalle_anticipo']
            : ' ';

        $needed = $hTituloGrupo
                + $hGapTitulo
                + $hCabeceraTabla
                + $rowHeightFor($firstDetailTxt)
                + $hMiniTotales
                + $hSep
                + 2; // pequeño colchón

        if ($spaceLeft() < $needed) {
            $pdf->AddPage('L');
        }

        $gIngreso = 0; $gEgreso = 0; $gPago = 0; $gGasto = 0;

        $pdf->SetFont('Arial', '', 9);

        // separar SIEMPRE entre grupos, excepto antes del primero
        if (!$isFirstGroup) {
            $pdf->Ln(4);
            $pdf->SetDrawColor(0,0,0);
            $pdf->SetLineWidth(0.2);
            $pdf->Line($leftMargin, $pdf->GetY(), $pdf->GetPageWidth() - $rightMargin, $pdf->GetY());
            $pdf->Ln(4);
        } else {
            $isFirstGroup = false; // a partir del siguiente grupo ya dibujamos separador
        }

        // ahora sí, imprimir etiqueta
        $etiqueta = '<b>'.$info['tipo'].'</b>: '.htmlspecialchars($nombre ?? '-', ENT_QUOTES, 'UTF-8');
        $pdf->WriteHTML($etiqueta);
        $pdf->Ln(6);

        // cabecera de tabla
        $tableHeader($showGasto);

        // === contar filas del grupo (para reservar espacio de la última + total + separador) ===
        $totalFilasGrupo = 0;
        foreach ($info['movs'] as $mTmp) {
            $rsTmp = mysqli_query($connect, "SELECT COUNT(*) c FROM movimientos_detalle WHERE id_movimiento=".(int)$mTmp['id_movimiento']);
            $rowTmp = mysqli_fetch_assoc($rsTmp);
            $totalFilasGrupo += (int)$rowTmp['c'];
        }
        $filasImpresas = 0;

        $saldo = 0;

        usort($info['movs'], function($a, $b) {
            // fecha ASC (dd-mm-YYYY)
            $da = DateTime::createFromFormat('d-m-Y', (string)$a['fecha']);
            $db = DateTime::createFromFormat('d-m-Y', (string)$b['fecha']);
            if (!$da) $da = new DateTime((string)$a['fecha']);
            if (!$db) $db = new DateTime((string)$b['fecha']);
            if ($da == $db) return 0;
            return ($da < $db) ? -1 : 1;
        });

        $saldo = 0; // reinicia por grupo

        foreach ($info['movs'] as $mov) {

            $rsDet = mysqli_query(
                $connect,
                "SELECT * FROM movimientos_detalle WHERE id_movimiento=".(int)$mov['id_movimiento']
            );
            $detalles = mysqli_fetch_all($rsDet, MYSQLI_ASSOC);

            foreach ($detalles as $d) {
                $valor = ($d['anticipo'] == null ? (float)$d['monto_cheque'] : (float)$d['anticipo']);

                // Inicializar columnas
                $colIngreso = '';
                $colEgreso  = '';
                $colPago    = '';
                $colGasto   = '';   // 👈 nueva

                switch ((int)$mov['tipo_movimiento']) {
                    case 1: // Ingreso
                        $colIngreso = $valor;
                        $saldo += $valor;
                        $totalIngreso += $valor;
                        break;

                    case 2: // Egreso
                        $colEgreso = $valor;
                        $saldo -= $valor;
                        $totalEgreso += $valor;
                        break;

                    case 4: // Pago
                        $colPago = $valor;
                        $saldo -= $valor;
                        $totalPago += $valor;
                        break;

                    case 3: // Gasto
                        $colGasto = $valor;
                        $totalGasto += $valor;
                        /* if ($contabilizarGastoEnSaldo) { */
                            // si el reporte es de Gastos, que el saldo vaya acumulando el gasto
                            $saldo -= $valor;   // (gasto disminuye el saldo)
                        /* } */
                        break;
                }

                // Dibujar fila
                $safeRow(
                    $mov['fecha'],
                    (string)$d['detalle_anticipo'],
                    $d['id_movimiento'],
                    $colIngreso,
                    $colEgreso,
                    $colPago,
                    $colGasto,
                    $saldo,
                    $showGasto
                );

            }
        }
    }

    if ($tipo_movimiento === '') {

        // --- RESERVAR ESPACIO ---
        // 2 filas (títulos y valores) de la primera tabla + salto + (opcional) 2 filas de la tabla de gastos + colchón
        $neededHeight = (8*2) + 6 + ($showGasto ? (8*2) : 0) + 10;
        $usableBottom = $pdf->GetPageHeight() - $bottomMargin;
        if ($pdf->GetY() + $neededHeight > $usableBottom) {
            $pdf->AddPage('L');
        }

        $pdf->Ln(8);

        // --- ANCHO TOTAL DE LA TABLA PRINCIPAL ---
        $colW = 45; // ancho uniforme por columna
        $tablaW = $colW * 4;
        $pageWidth = $pdf->GetPageWidth();
        $x = ($pageWidth - $tablaW) / 2;

        // --- TABLA 1: INGRESO / EGRESO / PAGOS / TOTAL ---
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetX($x);
        $pdf->Cell($colW, 8, 'TOTAL INGRESO', 1, 0, 'C');
        $pdf->Cell($colW, 8, 'TOTAL EGRESO', 1, 0, 'C');
        $pdf->Cell($colW, 8, 'TOTAL PAGOS',  1, 0, 'C');
        $pdf->Cell($colW, 8, 'TOTAL',        1, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetX($x);
        $pdf->Cell($colW, 8, '$'.number_format($totalIngreso, 0, ',', '.'), 1, 0, 'C');
        $pdf->Cell($colW, 8, '$'.number_format($totalEgreso,  0, ',', '.'), 1, 0, 'C');
        $pdf->Cell($colW, 8, '$'.number_format($totalPago,    0, ',', '.'), 1, 0, 'C');
        $resultado = $totalIngreso - $totalEgreso - $totalPago;
        $pdf->Cell($colW, 8, '$'.number_format($resultado,    0, ',', '.'), 1, 1, 'C');

        // --- SALTO ---
        if ($showGasto) {
            $pdf->Ln(6);

            // --- TABLA 2: GASTOS ---
            $tablaW2 = $colW * 2;
            $x2 = ($pageWidth - $tablaW2) / 2;

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetX($x2);
            $pdf->Cell($tablaW2, 8, 'TOTAL GASTOS', 1, 1, 'C');

            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX($x2);
            $pdf->Cell($tablaW2, 8, '$'.number_format($totalGasto, 0, ',', '.'), 1, 1, 'C');
        }

    } else {

        // Mapear nombre y suma a mostrar
        $mapNombre = ['1'=>'INGRESOS', '2'=>'EGRESOS', '3'=>'GASTOS', '4'=>'PAGOS'];
        $mapSuma   = [
            '1' => $totalIngreso,
            '2' => $totalEgreso,
            '3' => $totalGasto,
            '4' => $totalPago
        ];

        $nombre = $mapNombre[(string)$tipo_movimiento] ?? '—';
        $suma   = $mapSuma[(string)$tipo_movimiento]   ?? 0;

        // Texto del encabezado
        $label = 'TOTAL ' . $nombre;

        // Calcular ancho mínimo para el título (seguro y centrado)
        $pdf->SetFont('Arial', 'B', 10);
        $wLabel = $pdf->GetStringWidth($label) + 12;  // un poco de padding
        $tablaW = max(60, $wLabel);                   // al menos 60mm

        // Centrado y control de salto
        $x = ($pdf->GetPageWidth() - $tablaW) / 2;

        // calcula el fondo útil de la página para esta rama
        $neededHeight = 18;  // aprox. título + valor
        $usableBottom = $pdf->GetPageHeight() - $bottomMargin;
        if ($pdf->GetY() + $neededHeight > $usableBottom) {
            $pdf->AddPage('L');
        }

        // --- SALTO ---
        $pdf->Ln(6);

        // Título
        $pdf->SetX($x);
        $pdf->Cell($tablaW, 8, $label, 1, 1, 'C');

        // Valor
        $pdf->SetX($x);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($tablaW, 8, '$'.number_format($suma, 0, ',', '.'), 1, 1, 'C');

    }

    if (ob_get_length()) {
        ob_end_clean();
    }
    $pdf->Output('D', 'Informe_Movimientos.pdf');

} else {

    echo 'IDs de movimiento no proporcionados.';

}