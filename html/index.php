<?php
session_start();
if (!isset($_SESSION['correo_usuario'])) {
    header('Location: login.php');
    exit;
}
header('Location: home.php');
exit;
