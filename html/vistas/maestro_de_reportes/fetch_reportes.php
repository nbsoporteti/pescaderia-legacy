<?php
require '../../includes/db_connect.php';
require '../../includes/reportes_where.inc.php';
header('Content-Type: application/json; charset=utf-8');

$draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start = isset($_POST['start']) ? max(0, (int)$_POST['start']) : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 25;
if ($length < 1 || $length > 200) {
    $length = 25;
}

$search = '';
if (!empty($_POST['search']['value'])) {
    $search = trim($_POST['search']['value']);
}

$built = reportes_build_where($connect, array_merge($_POST, ['search' => $search]));
$whereSql = reportes_where_sql($built);
$from = $built['joins'] . " WHERE $whereSql";

$countRes = mysqli_query($connect, "SELECT COUNT(*) AS c $from");
if (!$countRes) {
    echo json_encode(['error' => mysqli_error($connect)]);
    exit;
}
$recordsFiltered = (int)mysqli_fetch_assoc($countRes)['c'];

$totalRes = mysqli_query($connect, 'SELECT COUNT(*) AS c FROM movimientos WHERE eliminado = 0');
$recordsTotal = (int)mysqli_fetch_assoc($totalRes)['c'];

$totales = [
    'general' => 0.0,
    'ingreso' => 0.0,
    'egreso' => 0.0,
    'gastos' => 0.0,
    'pagos' => 0.0,
    'saldo' => 0.0,
    'count' => $recordsFiltered,
];
$sumSql = "SELECT m.tipo_movimiento AS tipo, COALESCE(SUM(m.total), 0) AS suma $from GROUP BY m.tipo_movimiento";
$sumRes = mysqli_query($connect, $sumSql);
if ($sumRes) {
    while ($r = mysqli_fetch_assoc($sumRes)) {
        $suma = (float)$r['suma'];
        $totales['general'] += $suma;
        switch ((int)$r['tipo']) {
            case 1: $totales['ingreso'] += $suma; break;
            case 2: $totales['egreso'] += $suma; break;
            case 3: $totales['gastos'] += $suma; break;
            case 4: $totales['pagos'] += $suma; break;
        }
    }
    $totales['saldo'] = $totales['ingreso'] - ($totales['egreso'] + $totales['gastos'] + $totales['pagos']);
}

$orderCol = 'm.fecha';
$orderDir = 'DESC';
$colMap = [
    0 => 'm.id_movimiento',
    1 => 'm.fecha',
    2 => 'p.nombre_proveedor',
    3 => 'q.nombre_trabajador',
    4 => 'e.nombre_embarcacion',
    5 => 'f.nombre_embarcacion2',
    6 => 'clasificacion',
    7 => 'm.tipo_movimiento',
    8 => 'm.total',
];
if (!empty($_POST['order'][0]['column']) && isset($colMap[(int)$_POST['order'][0]['column']])) {
    $orderCol = $colMap[(int)$_POST['order'][0]['column']];
    $orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'asc') ? 'ASC' : 'DESC';
}

$clasSub = "(SELECT GROUP_CONCAT(DISTINCT c2.nombre_clasificacion ORDER BY c2.nombre_clasificacion SEPARATOR ', ')
    FROM movimientos_detalle md2
    INNER JOIN clasificaciones c2 ON md2.clasificacion = c2.id_clasificacion
    WHERE md2.id_movimiento = m.id_movimiento)";

$sql = "SELECT
    m.id_movimiento,
    m.fecha,
    COALESCE(p.nombre_proveedor, '-') AS proveedor,
    COALESCE(q.nombre_trabajador, '-') AS trabajador,
    COALESCE(e.nombre_embarcacion, '-') AS embarcacion,
    COALESCE(f.nombre_embarcacion2, '-') AS embarcacion2,
    COALESCE($clasSub, '-') AS clasificacion,
    CASE m.tipo_movimiento
        WHEN 1 THEN 'Ingreso'
        WHEN 2 THEN 'Egreso'
        WHEN 3 THEN 'Gastos'
        WHEN 4 THEN 'Pagos'
        ELSE 'Desconocido'
    END AS tipo_movimiento,
    m.total
    $from
    ORDER BY $orderCol $orderDir
    LIMIT $start, $length";

$result = mysqli_query($connect, $sql);
if (!$result) {
    echo json_encode(['error' => mysqli_error($connect)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => $data,
    'totales' => $totales,
]);
