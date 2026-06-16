<?php

require '../../../includes/db_connect.php';

session_start();
if (!isset($_SESSION["correo_usuario"])) {
    header("location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fecha = mysqli_real_escape_string($connect, $_POST['fecha']);
    $monto = mysqli_real_escape_string($connect, $_POST['monto']);
    $montoSinFormato = str_replace(['$', '.'], '', $monto);
    $montoCheque = (int)$montoSinFormato;
    $nro_cheque = mysqli_real_escape_string($connect, $_POST['nro_cheque']);
    $clasificacion = mysqli_real_escape_string($connect, $_POST['clasificacion']);
    $realizado = date('Y-m-d H:i:s');
    $realizado_por = $_SESSION["id_usuario"];

    $query = "INSERT INTO banco (fecha, monto, nro_cheque, clasificacion, estado, realizado, realizado_por) 
                   VALUES ('$fecha', $montoCheque, $nro_cheque, $clasificacion, 3, '$realizado', $realizado_por)";
    
    if (mysqli_query($connect, $query)) {
        header('Location: index.php');
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}
?>