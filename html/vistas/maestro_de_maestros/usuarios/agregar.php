<?php

require __DIR__ . '/../../../includes/require_admin.php';
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($connect, $_POST['apellido']);
    $correo = mysqli_real_escape_string($connect, $_POST['correo']);
    $rut = mysqli_real_escape_string($connect, $_POST['rut']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // bcrypt (antes era md5)
    $id_rol = (int)$_POST['id_rol'];

    $query = "INSERT INTO usuarios (nombre, apellido, correo, rut, password, id_rol) 
                   VALUES ('$nombre', '$apellido', '$correo', '$rut', '$password', $id_rol)";
    
    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>