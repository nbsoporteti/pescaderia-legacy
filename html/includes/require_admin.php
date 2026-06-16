<?php
/**
 * Guard de administrador. Incluir al inicio de endpoints sensibles:
 *   require __DIR__ . '/../../../includes/require_admin.php';
 * Exige sesion iniciada + rol administrador (id_rol == 1).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pesEsPost = (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');

if (!isset($_SESSION['correo_usuario'])) {
    if ($pesEsPost) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Sesion no iniciada.']);
    } else {
        header('Location: ../../../login.php');
    }
    exit;
}

if ((int)($_SESSION['id_rol'] ?? 0) !== 1) {
    http_response_code(403);
    if ($pesEsPost) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => 'Acceso denegado: se requiere administrador.']);
    } else {
        echo 'Acceso denegado: se requiere rol de administrador.';
    }
    exit;
}
