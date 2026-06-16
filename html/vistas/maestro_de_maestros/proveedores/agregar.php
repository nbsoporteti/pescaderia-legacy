<?php

require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);
    $rut = mysqli_real_escape_string($connect, $_POST['rut']);
    $telefono = mysqli_real_escape_string($connect, $_POST['telefono']);
    $domicilio = mysqli_real_escape_string($connect, $_POST['domicilio']);

    $embarcaciones = $_POST['embarcacion'];
    $embarcaciones_str = implode(',', array_map('intval', $embarcaciones));

    $query = "INSERT INTO proveedores (nombre_proveedor, rut_proveedor, telefono_proveedor, domicilio_proveedor, embarcacion) 
                   VALUES ('$nombre', '$rut', '$telefono', '$domicilio', '$embarcaciones_str')";

    // Ejecutar la consulta
    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
    
}
?>