<?php
require __DIR__ . '/../../../includes/require_admin.php';
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = (int)$_POST['id_usuario'];

    $query = "SELECT id_usuario, nombre, apellido, correo, rut, id_rol, estado, password 
              FROM usuarios WHERE id_usuario = $id_usuario";

    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        echo json_encode(mysqli_fetch_assoc($result));
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
}
?>
