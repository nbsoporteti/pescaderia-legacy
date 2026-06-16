<?php
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = (int)$_POST['id_usuario'];
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($connect, $_POST['apellido']);
    $correo = mysqli_real_escape_string($connect, $_POST['correo']);
    $rut = mysqli_real_escape_string($connect, $_POST['rut']);
    $id_rol = (int)$_POST['id_rol'];
    $password = $_POST['password'];

    if($password == ""){
        $query = "  UPDATE usuarios 
                    SET nombre = '$nombre', apellido = '$apellido', correo = '$correo', rut = '$rut', 
                        id_rol = $id_rol
                    WHERE id_usuario = $id_usuario";
    }else{
        $password = md5($password);
        $query = "  UPDATE usuarios 
                    SET nombre = '$nombre', apellido = '$apellido', correo = '$correo', rut = '$rut', 
                        id_rol = $id_rol, password = '$password'
                    WHERE id_usuario = $id_usuario";
    }

    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>
