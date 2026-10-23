<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorSolicitudes.php';

Api::iniciar();
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorSolicitudes();
$id = Api::parametro('id');

try {
    if ($metodo === 'GET') {
        if ($id === null) {
            Api::resultado(
                Sesion::esTecnico() ? $gestor->obtenerTodos() : $gestor->obtenerPorSolicitante(Sesion::ci()),
                'Solicitudes cargadas.'
            );
        }
        $resultado = $gestor->obtenerPorId($id);
        if (!$resultado['ok']) Api::resultado($resultado);
        if (!$resultado['datos']) Api::responder(404, 'error', 'No existe esa solicitud.');
        if (!Sesion::esTecnico() && $resultado['datos']['ci_solicitante'] !== Sesion::ci()) {
            Api::responder(403, 'error', 'No tenés permiso para ver esta solicitud.');
        }
        Api::resultado($resultado, 'Solicitud cargada.');
    }

    Api::exigirToken();

    if ($metodo === 'DELETE') {
        Api::exigirRol(['Coordinador']);
        $actual = $gestor->obtenerPorId($id);
        if (!$actual['ok']) Api::resultado($actual);
        if (!$actual['datos']) Api::responder(404, 'error', 'No existe esa solicitud.');
        Api::resultado($gestor->eliminar($id), 'Solicitud eliminada.');
    }

    $datos = Api::cuerpo();
    if ($metodo === 'POST') {
        if ($id !== null) Api::responder(400, 'error', 'El alta no lleva identificador.');
        $solicitud = new Solicitud(
            date('Y-m-d H:i:s'),
            Sesion::ci(),
            Api::campo($datos, 'nombre_salon'),
            Api::campo($datos, 'descripcion'),
            Api::campo($datos, 'prioridad', 'Normal')
        );
        $resultado = $gestor->insertar($solicitud);
        if (!$resultado['ok']) Api::resultado($resultado);
        Api::responder(201, 'success', 'Solicitud registrada.', ['id_solicitud' => (int) $resultado['datos']]);
    }

    Api::exigirRol(['Asistente', 'Coordinador']);
    $actual = $gestor->obtenerPorId($id);
    if (!$actual['ok']) Api::resultado($actual);
    if (!$actual['datos']) Api::responder(404, 'error', 'No existe esa solicitud.');
    $fila = $actual['datos'];
    $estado = Api::campo($datos, 'estado_solicitud', $fila['estado_solicitud']);
    $responsable = Api::campo($datos, 'ci_responsable', $fila['ci_responsable']);
    $cierre = null;
    if ($estado === 'Resuelta') {
        $cierre = $fila['fecha_hora_cierre'];
        if (!$cierre) $cierre = date('Y-m-d H:i:s', max(time(), strtotime($fila['fecha_hora_alta'])));
    }
    $solicitud = new Solicitud(
        $fila['fecha_hora_alta'],
        $fila['ci_solicitante'],
        Api::campo($datos, 'nombre_salon', $fila['nombre_salon']),
        Api::campo($datos, 'descripcion', $fila['descripcion']),
        Api::campo($datos, 'prioridad', $fila['prioridad']),
        $estado,
        $responsable !== '' ? $responsable : null,
        $cierre,
        $fila['id_solicitud']
    );
    Api::resultado($gestor->actualizar($solicitud), 'Solicitud actualizada.');
} catch (Throwable $e) {
    error_log('SGRSI API solicitudes: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
