<?php

require_once __DIR__ . '/../logica/Sesion.php';
require_once __DIR__ . '/../logica/Resumen.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responderMetricas($codigo, $estado, $mensaje, $datos = null)
{
    http_response_code($codigo);
    echo json_encode(
        ['status' => $estado, 'message' => $mensaje, 'data' => $datos],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

if (!Sesion::hayUsuario()) {
    responderMetricas(401, 'error', 'Iniciá sesión para continuar.');
}

if (!Sesion::esCoordinador()) {
    responderMetricas(403, 'error', 'Sólo el coordinador puede consultar las métricas.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    responderMetricas(405, 'error', 'Método no admitido en esta operación.');
}

try {
    $resumen = new Resumen();
    $metricas = $resumen->metricasExtendidas();
    responderMetricas(200, 'success', 'Métricas actualizadas.', $metricas);
} catch (Exception $e) {
    error_log('SGRSI API métricas: ' . $e->getMessage());
    responderMetricas(500, 'error', 'No se pudieron cargar las métricas. Intentá de nuevo más tarde.');
}
