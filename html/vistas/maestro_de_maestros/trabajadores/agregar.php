<?php

require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);
    $rut = mysqli_real_escape_string($connect, $_POST['rut']);
    $telefono = mysqli_real_escape_string($connect, $_POST['telefono']);
    $domicilio = mysqli_real_escape_string($connect, $_POST['domicilio']);
    $embarcacion = (int)$_POST['embarcacion'];

    $query = "INSERT INTO trabajadores (nombre_trabajador, rut_trabajador, telefono_trabajador, domicilio_trabajador, embarcacion) 
                   VALUES ('$nombre', '$rut', '$telefono', '$domicilio', $embarcacion)";
        
    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>