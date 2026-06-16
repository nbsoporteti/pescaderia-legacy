<?php
require '../../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $resultados = [];
    $query = "SELECT id_embarcacion2, nombre_embarcacion2, estado FROM embarcaciones2 WHERE eliminado = 0 AND estado = 1";
    $result = mysqli_query($connect, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($fila = $result->fetch_assoc()) {
            $resultados[] = $fila;
        }
        header('Content-Type: application/json');
        echo json_encode($resultados);
    } else {
        echo json_encode(['error' => 'Embarcacion no encontrada']);
    }
    $connect->close();
}
?>
