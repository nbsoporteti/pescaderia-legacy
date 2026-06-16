<?php
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = (int)$_POST['id_usuario'];
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);
    $rut = mysqli_real_escape_string($connect, $_POST['rut']);
    $telefono = mysqli_real_escape_string($connect, $_POST['telefono']);
    $domicilio = mysqli_real_escape_string($connect, $_POST['domicilio']);
    $embarcacion = (int)$_POST['embarcacion'];

    $query = "  UPDATE trabajadores 
                SET nombre_trabajador = '$nombre', rut_trabajador = '$rut', telefono_trabajador = '$telefono', domicilio_trabajador = '$domicilio', embarcacion = $embarcacion
                WHERE id_trabajador = $id_usuario";

    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>
