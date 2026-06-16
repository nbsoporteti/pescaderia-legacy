<?php

    require '../../includes/db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idProveedor = intval($_POST['id_proveedor']);
        $resultados = [];

        if ($idProveedor > 0) {
            // Primero, obtener el campo 'embarcacion' de la tabla 'proveedores'
            $queryProveedor = "SELECT embarcacion FROM proveedores WHERE id_proveedor = ?";
            $stmtProveedor = $connect->prepare($queryProveedor);
            $stmtProveedor->bind_param('i', $idProveedor);
            $stmtProveedor->execute();
            $resultProveedor = $stmtProveedor->get_result();
            $filaProveedor = $resultProveedor->fetch_assoc();
            $stmtProveedor->close();

            if ($filaProveedor && !empty($filaProveedor['embarcacion'])) {
                // Convertir los IDs separados por comas en un array
                $idsEmbarcacion = explode(',', $filaProveedor['embarcacion']);
                $placeholders = implode(',', array_fill(0, count($idsEmbarcacion), '?'));

                // Preparar consulta para obtener las embarcaciones correspondientes
                $queryEmbarcaciones = "SELECT id_embarcacion2, nombre_embarcacion2 FROM embarcaciones2 WHERE id_embarcacion2 IN ($placeholders) AND estado = 1 AND eliminado = 0";
                $stmtEmbarcaciones = $connect->prepare($queryEmbarcaciones);

                // Usar los IDs como parámetros
                $stmtEmbarcaciones->bind_param(str_repeat('i', count($idsEmbarcacion)), ...$idsEmbarcacion);
                $stmtEmbarcaciones->execute();
                $resultEmbarcaciones = $stmtEmbarcaciones->get_result();

                while ($fila = $resultEmbarcaciones->fetch_assoc()) {
                    $resultados[] = $fila;
                }
                $stmtEmbarcaciones->close();
            }
        }

        // Retornar JSON
        header('Content-Type: application/json');
        echo json_encode($resultados);
    }

    $connect->close();

?>