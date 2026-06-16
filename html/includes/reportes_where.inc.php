<?php
/**
 * Condiciones WHERE compartidas entre fetch_reportes.php y generar_pdf_general.php
 * @return array{where: string[], joins: string}
 */
function reportes_build_where(mysqli $connect, array $params): array
{
    $proveedor = isset($params['proveedor']) ? (int)$params['proveedor'] : 0;
    $trabajador = isset($params['trabajador']) ? (int)$params['trabajador'] : 0;
    $embarcacion = isset($params['embarcacion']) ? (int)$params['embarcacion'] : 0;
    $embarcacion2 = isset($params['embarcacion2']) ? (int)$params['embarcacion2'] : 0;
    $tipo_movimiento = isset($params['tipo_movimiento']) ? (int)$params['tipo_movimiento'] : 0;
    $clasificacion = isset($params['clasificacion']) ? (int)$params['clasificacion'] : 0;
    $fecha_inicio = isset($params['fecha_inicio']) ? mysqli_real_escape_string($connect, (string)$params['fecha_inicio']) : '';
    $fecha_fin = isset($params['fecha_fin']) ? mysqli_real_escape_string($connect, (string)$params['fecha_fin']) : '';
    $search = isset($params['search']) ? mysqli_real_escape_string($connect, trim((string)$params['search'])) : '';

    $where = ['m.eliminado = 0'];

    if ($fecha_inicio && $fecha_fin) {
        $where[] = "m.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'";
    } elseif ($fecha_inicio) {
        $where[] = "m.fecha >= '$fecha_inicio'";
    } elseif ($fecha_fin) {
        $where[] = "m.fecha <= '$fecha_fin'";
    }

    if ($proveedor) {
        $where[] = "m.proveedor = $proveedor";
    }
    if ($trabajador) {
        $where[] = "m.trabajador = $trabajador";
    }
    if ($embarcacion) {
        $where[] = "m.embarcacion = $embarcacion";
    }
    if ($embarcacion2) {
        $where[] = "m.embarcacion2 = $embarcacion2";
    }
    if ($tipo_movimiento) {
        $where[] = "m.tipo_movimiento = $tipo_movimiento";
    }
    if ($clasificacion) {
        $where[] = "EXISTS (
        SELECT 1 FROM movimientos_detalle md_f
        WHERE md_f.id_movimiento = m.id_movimiento AND md_f.clasificacion = $clasificacion
    )";
    }

    if ($search !== '') {
        $where[] = "(
        CAST(m.id_movimiento AS CHAR) LIKE '%$search%'
        OR p.nombre_proveedor LIKE '%$search%'
        OR q.nombre_trabajador LIKE '%$search%'
        OR e.nombre_embarcacion LIKE '%$search%'
        OR f.nombre_embarcacion2 LIKE '%$search%'
    )";
    }

    $joins = "
    FROM movimientos m
    LEFT JOIN proveedores p ON m.proveedor = p.id_proveedor
    LEFT JOIN trabajadores q ON m.trabajador = q.id_trabajador
    LEFT JOIN embarcaciones e ON m.embarcacion = e.id_embarcacion
    LEFT JOIN embarcaciones2 f ON m.embarcacion2 = f.id_embarcacion2
    ";

    return ['where' => $where, 'joins' => $joins];
}

function reportes_where_sql(array $built): string
{
    return implode(' AND ', $built['where']);
}
