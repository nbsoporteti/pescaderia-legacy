<?php
ob_start();
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
require '../../includes/db_connect.php';
require('../../includes/WriteHTML.php');

if (isset($_GET['id_movimiento'])) {
    $id_movimiento = intval($_GET['id_movimiento']);

    $query = "SELECT * FROM movimientos WHERE id_movimiento = $id_movimiento";
    $result = mysqli_query($connect, $query);
    $movimiento = mysqli_fetch_assoc($result);

    $embarcacionQuery = "SELECT nombre_embarcacion FROM embarcaciones WHERE id_embarcacion = " . $movimiento['embarcacion'];
    $result = mysqli_query($connect, $embarcacionQuery);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $embarcacionNombre = $row['nombre_embarcacion'];
    } else {
        $embarcacionNombre = 'Embarcacion no encontrada';
    }

    if($movimiento["tipo_movimiento"] != 3){
        $embarcacionQuery2 = "SELECT nombre_embarcacion2 FROM embarcaciones2 WHERE id_embarcacion2 = " . $movimiento['embarcacion2'];
        $result2 = mysqli_query($connect, $embarcacionQuery2);

        if ($result2 && mysqli_num_rows($result2) > 0) {
            $row2 = mysqli_fetch_assoc($result2);
            $embarcacionNombre2 = $row2['nombre_embarcacion2'];
        } else {
            $embarcacionNombre2 = 'Embarcacion no encontrada';
        }
    }

    

    if ($movimiento['trabajador'] == 0) {
        $trabajadorNombre = '-';
    } else {
        // Consulta para obtener el nombre del trabajador desde la tabla 'trabajadores'
        $trabajadorQuery = "SELECT nombre_trabajador FROM trabajadores WHERE id_trabajador = " . $movimiento['trabajador'];
        $result = mysqli_query($connect, $trabajadorQuery);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $trabajadorNombre = $row['nombre_trabajador'];
        } else {
            $trabajadorNombre = 'Trabajador no encontrado';
        }
    }

    if ($movimiento['proveedor'] == 0) {
        $proveedorNombre = '-';
    } else {
        $proveedorQuery = "SELECT nombre_proveedor FROM proveedores WHERE id_proveedor = " . $movimiento['proveedor'];
        $result = mysqli_query($connect, $proveedorQuery);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $proveedorNombre = $row['nombre_proveedor'];
        } else {
            $proveedorNombre = 'Proveedor no encontrado';
        }
    }

    if ($movimiento['tipo_movimiento'] == 1) {
        $tipoMovimiento = 'Ingreso';
    } elseif ($movimiento['tipo_movimiento'] == 2) {
        $tipoMovimiento = 'Egreso';
    } elseif ($movimiento['tipo_movimiento'] == 3) {
        $tipoMovimiento = 'Gasto';
    } elseif ($movimiento['tipo_movimiento'] == 4) {
        $tipoMovimiento = 'Pago';
    } else {
        $tipoMovimiento = 'Desconocido'; // Opcional para manejar casos no previstos
    }    

    $query2 = "SELECT * FROM movimientos_detalle WHERE id_movimiento = $id_movimiento";
    $result2 = mysqli_query($connect, $query2);
    $detalles = mysqli_fetch_all($result2, MYSQLI_ASSOC);

    $pdf = new PDF_HTML();
    $pdf->AddPage();

    // Sección 0: Título
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 10, 'DETALLE DE MOVIMIENTO N°' . $id_movimiento, 0, 1, 'C');
    $pdf->Ln(10);

    // Sección 1: Información General
    $pdf->SetFont('Arial', '', 14);
    $pdf->WriteHTML('<b>Tipo de movimiento</b>: ' . $tipoMovimiento . '<br>');
    $pdf->Ln();
    $pdf->WriteHTML('<b>Fecha</b>: ' . $movimiento['fecha'] . '<br>');
    $pdf->Ln();
    $pdf->WriteHTML('<b>Acarreo</b>: ' . $embarcacionNombre . '<br>');
    if($proveedorNombre != "-"){
        $pdf->Ln();
        $pdf->WriteHTML('<b>Proveedor</b>: ' . $proveedorNombre . '<br>');
        if($movimiento["tipo_movimiento"] != 3){
            $pdf->Ln();
            $pdf->WriteHTML('<b>Embarcacion</b>: ' . $embarcacionNombre2 . '<br>');
        }
    }
    if($trabajadorNombre != "-"){
        $pdf->Ln();
        $pdf->WriteHTML('<b>Trabajador</b>: ' . $trabajadorNombre);
    }
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 16);
    $pdf->WriteHTML('<p align="center"><u><b>DETALLE</b></u></p>');

    // Sección 2: Detalles
    $pdf->SetFont('Arial', '', 12);

    $chequesPendientes = false;
    $clasificacionImpreso = false;

    foreach ($detalles as $index => $detalle) {

        if ($index === 0) {
            $pdf->WriteHTML('<hr>');
            $pdf->Ln(2);
        }

        $id_clasificacion = $detalle['clasificacion'];

        if ($detalle['clasificacion'] != 0) {
            $queryClasificacion = "SELECT nombre_clasificacion FROM clasificaciones WHERE id_clasificacion = $id_clasificacion";
            $resultClasificacion = mysqli_query($connect, $queryClasificacion);

            if ($resultClasificacion && mysqli_num_rows($resultClasificacion) > 0) {
                $rowClasificacion = mysqli_fetch_assoc($resultClasificacion);
                $nombreClasificacion = $rowClasificacion['nombre_clasificacion'];
            } else {
                $nombreClasificacion = 'Clasificacion no encontrada';
            }
        } else {
            $nombreClasificacion = '-';
        }

        if ($detalle['cantidad_cheques'] > 0) {
            if (!$chequesPendientes) {
                // Inicia un bloque para cheques
                $chequesPendientes = true;

                // Imprime la clasificación solo al inicio del bloque de cheques
                if (!$clasificacionImpreso) {
                    $pdf->Ln(1);
                    $pdf->WriteHTML('<b>Clasificacion</b>: ' . $nombreClasificacion . "<br>");
                    $pdf->WriteHTML('<b>Detalle</b>: ' . $detalle['detalle_anticipo']. "<br><br>");
                    $clasificacionImpreso = true;
                }
            }

            // Procesa cada cheque
            $fecha_cheque = DateTime::createFromFormat('Y-m-d', $detalle['fecha_cheque'])->format('d-m-Y');
            $valor_cheque = '$' . number_format($detalle['monto_cheque'], 0, ',', '.');

            $pdf->WriteHTML('<b>Fecha cheque</b>: ' . $fecha_cheque . "<br>");
            $pdf->Ln(1);
            $pdf->WriteHTML('<b>Monto cheque</b>: ' . $valor_cheque . "<br>");
            $pdf->Ln();
        } else {
            // Si hay anticipos, imprime los cheques pendientes antes de procesar
            if ($chequesPendientes) {
                $chequesPendientes = false; // Marca que el bloque de cheques terminó
                $pdf->WriteHTML('<hr>'); // Separa el bloque de cheques del siguiente contenido
                $clasificacionImpreso = false; // Resetea la impresión de clasificación
            }

            // Procesa e imprime el anticipo
            $valor_anticipo = '$' . number_format($detalle['anticipo'], 0, ',', '.');
            $pdf->Ln(2);
            $pdf->WriteHTML('<b>Anticipo</b>: ' . $valor_anticipo. "<br>");
            $pdf->Ln(1);
            $pdf->WriteHTML('<b>Detalle</b>: ' . $detalle['detalle_anticipo']. "<br>");
            $pdf->Ln(1);

            // Imprime la clasificación para el anticipo
            $pdf->WriteHTML('<b>Clasificacion</b>: ' . $nombreClasificacion . "<br>");
        }

        // Solo agrega `<hr>` si este no es el último registro
        if (!$chequesPendientes && $index < count($detalles) - 1) {
            $pdf->WriteHTML('<hr>');
            $pdf->Ln(4);
        }
    }

    // Al final del bucle, siempre agrega un `<hr>` después del último registro
    if ($index < count($detalles)) {
        $pdf->WriteHTML('<hr>');
        $pdf->Ln(4);
    }

    $total_total = '$' . number_format($movimiento['total'], 0, ',', '.');

    // Sección 3: Total
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 14);

    $pageWidth = $pdf->GetPageWidth();
    $leftMargin = $pdf->GetX();
    $contentWidth = $pageWidth - $leftMargin;
    $text = '<b>Total movimiento</b>: ' . $total_total;
    $textWidth = $pdf->GetStringWidth(strip_tags($text));
    $xPosition = $pageWidth - $textWidth - 12;
    $pdf->SetX($xPosition);
    $pdf->WriteHTML($text);

    // Descargar PDF
    if (ob_get_length()) { ob_end_clean(); }
    $pdf->Output('D', 'Detalle_Movimiento_' . $id_movimiento . '.pdf');
} else {
    echo "ID de movimiento no proporcionado.";
}
