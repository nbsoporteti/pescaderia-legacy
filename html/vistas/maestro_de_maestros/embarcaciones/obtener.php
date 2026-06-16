<?php
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = (int)$_POST['id_usuario'];

    $query = "SELECT id_embarcacion2, nombre_embarcacion2 FROM embarcaciones2 WHERE id_embarcacion2 = $id_usuario";

    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        echo json_encode(mysqli_fetch_assoc($result));
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
}
?>
