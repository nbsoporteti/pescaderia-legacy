<?php

require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);

    $query = "INSERT INTO embarcaciones (nombre_embarcacion) VALUES ('$nombre')";
    
    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>