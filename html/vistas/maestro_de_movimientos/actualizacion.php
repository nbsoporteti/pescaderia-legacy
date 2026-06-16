<?php
    require '../../includes/db_connect.php';

    session_start();
    if (!isset($_SESSION["correo_usuario"])) {
        header("location: ../../login.php");
        exit;
    }

    $realizado_por = $_SESSION["id_usuario"];
    $fecha_actual = date('Y-m-d H:i:s');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formData = $_POST;

        $id_movimiento = $formData['id_movimiento'];
        $fecha = $formData['fecha'];

        // Validacion de fecha: evita anios imposibles (ej. 0025, 2055) y fechas inexistentes.
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        $anioMax = (int)date('Y') + 1;
        if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha
            || (int)$fechaObj->format('Y') < 2020 || (int)$fechaObj->format('Y') > $anioMax) {
            echo json_encode([
                'status' => 'error',
                'message' => "Fecha del movimiento invalida. Debe ser una fecha real entre 2020 y $anioMax."
            ]);
            exit;
        }

        $embarcacion = $formData['embarcacion'];

        $proveedor = isset($formData['proveedor']) && $formData['proveedor'] !== '' ? $formData['proveedor'] : 'NULL';
        $embarcacion2 = isset($formData['embarcacion2']) && $formData['embarcacion2'] !== '' ? $formData['embarcacion2'] : 'NULL';
        $trabajador = isset($formData['trabajador']) && $formData['trabajador'] !== '' ? $formData['trabajador'] : 'NULL';

        $total = $formData['total_final'];

        // Actualizar movimiento general
        $query = "UPDATE movimientos 
                SET fecha = '$fecha', embarcacion = $embarcacion, proveedor = $proveedor, embarcacion2 = $embarcacion2, 
                    trabajador = $trabajador, total = $total, realizado = '$fecha_actual', realizado_por = $realizado_por 
                WHERE id_movimiento = $id_movimiento";

        if (mysqli_query($connect, $query)) {
            // Eliminar detalles antiguos para evitar datos duplicados
            $queryDelete = "DELETE FROM movimientos_detalle WHERE id_movimiento = $id_movimiento";
            mysqli_query($connect, $queryDelete);

            $updateSuccess = true;
            for ($i = 0; $i < count($formData['cheques_cantidad']); $i++) {
                $chequesCantidad = $formData['cheques_cantidad'][$i];
                $anticipo = $formData['anticipo'][$i];
                $detalleAnticipo = $formData['detalle_anticipo'][$i];
                $clasificacion = $formData['clasificacion'][$i];

                if ($chequesCantidad > 0) {
                    for ($j = 0; $j < $chequesCantidad; $j++) {
                        $fechaCheque = $formData['fecha_cheque_' . $i][$j];
                        $montoCheque = $formData['total_cheque_' . $i][$j];
                        $nroCheque = $formData['nro_cheque_' . $i][$j];

                        $query2 = "INSERT INTO movimientos_detalle (id_movimiento, cantidad_cheques, anticipo, detalle_anticipo, clasificacion, fecha_cheque, monto_cheque, nro_cheque) 
                                        VALUES ($id_movimiento, $chequesCantidad, NULL, '$detalleAnticipo', $clasificacion, '$fechaCheque', $montoCheque, $nroCheque)";

                        if (!mysqli_query($connect, $query2)) {
                            $updateSuccess = false;
                            echo "Error al insertar cheque $j en la fila $i: " . mysqli_error($connect) . "\n";
                        }
                    }
                } else {
                    $query2 = "INSERT INTO movimientos_detalle (id_movimiento, cantidad_cheques, anticipo, detalle_anticipo, clasificacion, fecha_cheque, monto_cheque, nro_cheque) 
                                    VALUES ($id_movimiento, 0, $anticipo, '$detalleAnticipo', $clasificacion, NULL, NULL, NULL)";

                    if (!mysqli_query($connect, $query2)) {
                        $updateSuccess = false;
                        echo "Error al insertar fila sin cheques en la fila $i: " . mysqli_error($connect) . "\n";
                    }
                }
            }

            if ($updateSuccess) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Movimiento actualizado correctamente.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Hubo un error al actualizar los detalles del movimiento.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al actualizar el movimiento: ' . mysqli_error($connect)
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Método de solicitud no permitido.'
        ]);
    }
?>
