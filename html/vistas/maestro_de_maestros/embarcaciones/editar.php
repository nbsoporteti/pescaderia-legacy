<?php
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_usuario = (int)$_POST['id_usuario'];
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);

    $query = "UPDATE embarcaciones2 SET nombre_embarcacion2 = '$nombre' WHERE id_embarcacion2 = $id_usuario";

    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>
