<?php
// #5 cascada inversa: devuelve el/los proveedor(es) asociados a una embarcacion (embarcacion2).
// La relacion vive en proveedores.embarcacion (CSV de ids de embarcacion2).
require '../../includes/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$idEmbarcacion = isset($_POST['id_embarcacion2']) ? intval($_POST['id_embarcacion2']) : 0;
$resultados = [];

if ($idEmbarcacion > 0) {
    $sql = "SELECT id_proveedor, nombre_proveedor
            FROM proveedores
            WHERE estado = 1 AND eliminado = 0
              AND FIND_IN_SET(?, embarcacion) > 0
            ORDER BY nombre_proveedor ASC";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param('i', $idEmbarcacion);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $resultados[] = $row;
    }
    $stmt->close();
}

echo json_encode($resultados);
$connect->close();
?>
