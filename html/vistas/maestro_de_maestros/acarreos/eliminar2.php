<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_usuario'])) {
        $id_usuario = $_POST['id_usuario'];

        // Aquí deberías realizar la conexión a tu base de datos y ejecutar la consulta para eliminar el usuario
        // Reemplaza 'tu_conexion' con el nombre de tu conexión a la base de datos
        include_once("../../../includes/db_connect.php");

        $query = "UPDATE embarcaciones SET eliminado = 1 WHERE id_embarcacion = $id_usuario";

        if (mysqli_query($connect, $query)) {
            echo "Acarreo eliminado correctamente";
        } else {
            echo "Error al eliminar acarreo: " . mysqli_error($connect);
        }

        // Cierra la conexión
        mysqli_close($connect);
    } else {
        echo "Acceso denegado";
    }
?>