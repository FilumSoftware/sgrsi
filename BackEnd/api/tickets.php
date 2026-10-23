<?php

require_once __DIR__ . '/../logica/Sesion.php';
require_once __DIR__ . '/../logica/GestorTickets.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder($codigo, $estado, $mensaje, $datos = null)
{
    http_response_code($codigo);
    echo json_encode(
        ['status' => $estado, 'message' => $mensaje, 'data' => $datos],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

if (!Sesion::hayUsuario()) {
    responder(401, 'error', 'Iniciá sesión para continuar.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('Allow: DELETE');
    responder(405, 'error', 'Método no admitido en esta operación.');
}

$token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
if (!Sesion::verificarTokenCancelarTicket($token)) {
    responder(403, 'error', 'La sesión de esta página venció. Volvé a abrir el ticket.');
}

try {
    $servicio = new GestorTickets();
    $resultado = $servicio->cancelar(isset($_GET['id']) ? $_GET['id'] : null, Sesion::ci());
    responder(
        $resultado['codigo'],
        $resultado['ok'] ? 'success' : 'error',
        $resultado['mensaje'],
        $resultado['datos']
    );
} catch (Exception $e) {
    error_log('SGRSI API tickets cancelar: ' . $e->getMessage());
    responder(500, 'error', 'No se pudo cancelar el ticket. Intentá de nuevo más tarde.');
}
