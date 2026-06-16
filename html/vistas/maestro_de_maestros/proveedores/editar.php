<?php
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = (int)$_POST['id_usuario'];
    $nombre = mysqli_real_escape_string($connect, $_POST['nombre']);
    $rut = mysqli_real_escape_string($connect, $_POST['rut']);
    $telefono = mysqli_real_escape_string($connect, $_POST['telefono']);
    $domicilio = mysqli_real_escape_string($connect, $_POST['domicilio']);

    // Obtener las embarcaciones seleccionadas, que pueden ser varias
    $embarcaciones = $_POST['embarcacion'];  // Esto es un array con los valores seleccionados
    $embarcaciones_str = implode(',', array_map('intval', $embarcaciones));  // Convierte a cadena separada por comas

    // Query para actualizar los datos
    $query = "  UPDATE proveedores 
                SET nombre_proveedor = '$nombre', rut_proveedor = '$rut', telefono_proveedor = '$telefono', domicilio_proveedor = '$domicilio', embarcacion = '$embarcaciones_str'
                WHERE id_proveedor = $id_usuario";

    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>
