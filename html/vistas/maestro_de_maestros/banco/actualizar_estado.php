<?php

    require '../../../includes/db_connect.php';

    session_start();
    if (!isset($_SESSION["correo_usuario"])) {
        header("location: ../../login.php");
        exit;
    }

    // Verificar si se recibieron los datos necesarios
    if (isset($_POST['id_movimiento']) && isset($_POST['estado']) && isset($_POST['fecha_pago'])) {
        
        $id_movimiento = $_POST['id_movimiento'];
        $estado = $_POST['estado'];
        $fecha_pago = $_POST['fecha_pago'];
        $comentarios = isset($_POST['comentarios']) && $_POST['comentarios'] !== '' ? $_POST['comentarios'] : null;

        $realizado = date('Y-m-d H:i:s');
        $realizado_por = $_SESSION["id_usuario"];

        // Escapar las variables para prevenir inyecciones SQL
        $id_movimiento = mysqli_real_escape_string($connect, $id_movimiento);
        $estado = mysqli_real_escape_string($connect, $estado);
        $fecha_pago = mysqli_real_escape_string($connect, $fecha_pago);
        $comentarios = mysqli_real_escape_string($connect, $comentarios);

        // Actualizar el estado y la fecha de pago en la tabla banco
        $query = "INSERT INTO banco (id_movimiento_detalle, estado, fecha_cobro, comentario, realizado, realizado_por)
                        VALUES ('$id_movimiento', '$estado', '$fecha_pago', '$comentarios', '$realizado', $realizado_por)
                        ON DUPLICATE KEY UPDATE 
                        estado = '$estado', 
                        fecha_cobro = '$fecha_pago', 
                        comentario = '$comentarios'";

        // Ejecutar la consulta
        if (mysqli_query($connect, $query)) {
            echo 'Estado actualizado correctamente';
        } else {
            echo 'Error al actualizar el estado: ' . mysqli_error($connect);
        }
    } else {
        echo 'Faltan datos requeridos';
    }

    // Cerrar la conexión
    mysqli_close($connect);

?>
