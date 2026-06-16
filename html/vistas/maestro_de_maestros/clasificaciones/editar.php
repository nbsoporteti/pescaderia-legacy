<?php
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_usuario = (int)$_POST['id_usuario'];
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);

    $query = "UPDATE clasificaciones SET nombre_clasificacion = '$nombre' WHERE id_clasificacion = $id_usuario";

    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>
