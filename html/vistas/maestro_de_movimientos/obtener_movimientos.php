<?php

    require '../../includes/db_connect.php'; // Conexión a la BD

    $id_movimiento = $_GET['id_movimiento']; // Recibe el ID del movimiento desde AJAX

    // Obtener los movimientos
    $sql = "SELECT md.*, m.total 
            FROM movimientos_detalle md
            INNER JOIN movimientos m ON md.id_movimiento = m.id_movimiento
            WHERE md.id_movimiento = $id_movimiento";
    $resultado = mysqli_query($connect, $sql) or die("Error: " . mysqli_error($connect));

    $movimientos = [];

    // Obtener clasificaciones
    $sql_clasificacion = "SELECT id_clasificacion, nombre_clasificacion FROM clasificaciones";
    $resultado_clasificacion = mysqli_query($connect, $sql_clasificacion) or die("Error: " . mysqli_error($connect));

    $clasificaciones = [];
    while ($row_clasificacion = mysqli_fetch_assoc($resultado_clasificacion)) {
        $clasificaciones[] = $row_clasificacion;
    }

    while ($row = mysqli_fetch_assoc($resultado)) {
        $movimiento = $row;

        // Si nro_cheque es null, reemplazar por vacío
        if (is_null($movimiento['nro_cheque'])) {
            $movimiento['nro_cheque'] = "";
        }

        $clasificacion_options = '';
        
        // Generar opciones de clasificación
        foreach ($clasificaciones as $clasificacion) {
            $selected = ($clasificacion['id_clasificacion'] == $movimiento['clasificacion']) ? 'selected' : '';
            $clasificacion_options .= "<option value=\"{$clasificacion['id_clasificacion']}\" $selected>{$clasificacion['nombre_clasificacion']}</option>";
        }
        
        // Añadir el HTML para el select dinámicamente
        $movimiento['clasificacion_options'] = $clasificacion_options;
        
        $movimientos[] = $movimiento;
    }

    echo json_encode($movimientos);

?>
