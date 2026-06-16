<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_usuario'])) {
        $id_usuario = $_POST['id_usuario'];

        // Aquí deberías realizar la conexión a tu base de datos y ejecutar la consulta para eliminar el usuario
        // Reemplaza 'tu_conexion' con el nombre de tu conexión a la base de datos
        include_once("../../../includes/db_connect.php");

        $query = "UPDATE embarcaciones2 SET estado = 2 WHERE id_embarcacion2 = $id_usuario";

        if (mysqli_query($connect, $query)) {
            echo "Embarcacion desactivada correctamente";
        } else {
            echo "Error al desactivar embarcacion: " . mysqli_error($connect);
        }

        // Cierra la conexión
        mysqli_close($connect);
    } else {
        echo "Acceso denegado";
    }
?>