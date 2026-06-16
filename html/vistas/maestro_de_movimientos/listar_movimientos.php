<?php
session_start();
if (!isset($_SESSION['correo_usuario'])) {
    http_response_code(403);
    exit;
}
/**
 * DataTables server-side para Ver movimientos.
 */
require '../../includes/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start = isset($_POST['start']) ? max(0, (int)$_POST['start']) : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 25;
if ($length < 1 || $length > 200) {
    $length = 25;
}

$search = '';
if (!empty($_POST['search']['value'])) {
    $search = mysqli_real_escape_string($connect, trim($_POST['search']['value']));
}

$orderCol = 'm.id_movimiento';
$orderDir = 'DESC';
$colMap = [
    0 => 'm.id_movimiento',
    1 => 'm.tipo_movimiento',
    2 => 'm.fecha',
    3 => 'r.nombre_embarcacion',
    4 => 's.nombre_embarcacion2',
    5 => 'p.nombre_proveedor',
    6 => 'q.nombre_trabajador',
    7 => 'm.total',
];
if (!empty($_POST['order'][0]['column']) && isset($colMap[(int)$_POST['order'][0]['column']])) {
    $orderCol = $colMap[(int)$_POST['order'][0]['column']];
    $orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
}

$where = 'm.eliminado = 0';
if ($search !== '') {
    $where .= " AND (
        CAST(m.id_movimiento AS CHAR) LIKE '%$search%'
        OR p.nombre_proveedor LIKE '%$search%'
        OR q.nombre_trabajador LIKE '%$search%'
        OR r.nombre_embarcacion LIKE '%$search%'
        OR s.nombre_embarcacion2 LIKE '%$search%'
    )";
}

$from = "
    FROM movimientos m
    LEFT JOIN proveedores p ON m.proveedor = p.id_proveedor
    LEFT JOIN trabajadores q ON m.trabajador = q.id_trabajador
    LEFT JOIN embarcaciones r ON m.embarcacion = r.id_embarcacion
    LEFT JOIN embarcaciones2 s ON m.embarcacion2 = s.id_embarcacion2
    WHERE $where
";

$countSql = "SELECT COUNT(*) AS c $from";
$countRes = mysqli_query($connect, $countSql);
$recordsFiltered = (int)mysqli_fetch_assoc($countRes)['c'];

$totalRes = mysqli_query($connect, "SELECT COUNT(*) AS c FROM movimientos WHERE eliminado = 0");
$recordsTotal = (int)mysqli_fetch_assoc($totalRes)['c'];

$sql = "SELECT
    m.id_movimiento,
    DATE_FORMAT(m.fecha, '%d-%m-%Y') AS fecha,
    m.total,
    CASE m.tipo_movimiento
        WHEN 1 THEN 'Ingreso'
        WHEN 2 THEN 'Egreso'
        WHEN 3 THEN 'Gasto'
        ELSE 'Otro'
    END AS tipo_movimiento,
    COALESCE(p.nombre_proveedor, '-') AS nombre_proveedor,
    COALESCE(q.nombre_trabajador, '-') AS nombre_trabajador,
    COALESCE(r.nombre_embarcacion, '-') AS nombre_embarcacion,
    COALESCE(s.nombre_embarcacion2, '-') AS nombre_embarcacion2
    $from
    ORDER BY $orderCol $orderDir
    LIMIT $start, $length";

$result = mysqli_query($connect, $sql);
$data = [];
$idRol = (int)($_SESSION['id_rol'] ?? 0);

while ($row = mysqli_fetch_assoc($result)) {
    $id = (int)$row['id_movimiento'];
    $totalFmt = '$' . number_format((float)$row['total'], 0, ',', '.');
    $tipoLabel = $row['tipo_movimiento'];
    $badgeClass = 'pes-badge-otro';
    if ($tipoLabel === 'Ingreso') {
        $badgeClass = 'pes-badge-ingreso';
    } elseif ($tipoLabel === 'Egreso') {
        $badgeClass = 'pes-badge-egreso';
    } elseif ($tipoLabel === 'Gasto') {
        $badgeClass = 'pes-badge-gasto';
    }
    $tipoHtml = '<span class="pes-badge ' . $badgeClass . '">' . htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') . '</span>';

    $acciones = '<div class="d-flex justify-content-center pes-actions">'
        . '<a href="generar_pdf.php?id_movimiento=' . $id . '" target="_blank" class="btn btn-primary btn-sm mr-2">Ver</a>';
    if ((int)$idRol === 1) {
        $acciones .= '<a target="_blank" href="editar.php?id_movimiento=' . $id . '" class="btn btn-warning btn-sm mr-2">Editar</a>'
            . '<a href="#" class="btn btn-danger btn-sm btnEliminar" data-id="' . $id . '"><i class="fa fa-times" aria-hidden="true"></i></a>';
    }
    $acciones .= '</div>';

    $data[] = [
        'id_movimiento' => $id,
        'tipo_movimiento' => $tipoHtml,
        'fecha' => $row['fecha'],
        'nombre_embarcacion' => $row['nombre_embarcacion'],
        'nombre_embarcacion2' => $row['nombre_embarcacion2'],
        'nombre_proveedor' => $row['nombre_proveedor'],
        'nombre_trabajador' => $row['nombre_trabajador'],
        'total_fmt' => $totalFmt,
        'acciones' => $acciones,
    ];
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data,
]);
