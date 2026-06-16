<?php

    require '../../includes/db_connect.php';

    $sql = "SELECT * 
            FROM clasificaciones
            WHERE nombre_clasificacion LIKE '%Banco%'
            OR nombre_clasificacion LIKE '%BANCO%'
            OR nombre_clasificacion LIKE '%Otros%'
            AND estado = 1 AND eliminado = 0";
    $resultado = mysqli_query($connect, $sql) or die("Error: " . mysqli_error($connect));

    echo '<option selected value="">Seleccionar</option>';
    while ($row = mysqli_fetch_assoc($resultado)) {
        echo '<option value="'.$row['id_clasificacion'].'">'.$row['nombre_clasificacion'].'</option>';
    }

?>