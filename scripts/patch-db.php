<?php
// Generado para Docker: host=db, credenciales desde entorno del contenedor web
$localhost = getenv('DB_HOST') ?: 'db';
$username = getenv('DB_USER') ?: 'pescaderia';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'u207708227_pesquera';

$connect = new mysqli($localhost, $username, $password, $dbname);
mysqli_set_charset($connect, 'utf8');

if ($connect->connect_error) {
    die('Connection Failed : ' . $connect->connect_error);
}
