<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorTickets.php';

Api::iniciar();
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorTickets();
$id = Api::parametro('id');

try {
    if ($metodo === 'GET') {
        if ($id !== null) {
            $resultado = $gestor->obtenerPorId($id);
            if (!$resultado['ok']) Api::resultado($resultado);
            if (!$resultado['datos']) Api::responder(404, 'error', 'No existe ese ticket.');
            if (!Sesion::esTecnico() && $resultado['datos']['ci_solicitante'] !== Sesion::ci()) {
                Api::responder(403, 'error', 'No tenés permiso para ver este ticket.');
            }
            Api::resultado($resultado, 'Ticket cargado.');
        }
        Api::resultado(
            Sesion::esTecnico() ? $gestor->obtenerTodos() : $gestor->obtenerPorSolicitante(Sesion::ci()),
            'Tickets cargados.'
        );
    }

    Api::exigirToken();

    if ($metodo === 'DELETE') {
        if (Api::parametro('accion') === 'eliminar') {
            Api::exigirRol(['Coordinador']);
            $actual = $gestor->obtenerPorId($id);
            if (!$actual['ok']) Api::resultado($actual);
            if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese ticket.');
            Api::resultado($gestor->eliminar($id), 'Ticket eliminado.');
        }
        Api::resultado($gestor->cancelar($id, Sesion::ci()));
    }

    $datos = Api::cuerpo();

    if ($metodo === 'POST') {
        if ($id !== null) Api::responder(400, 'error', 'El alta no lleva identificador.');
        $ticket = new Ticket(
            date('Y-m-d H:i:s'),
            Sesion::ci(),
            Api::campo($datos, 'id_equipo'),
            Api::campo($datos, 'tipo_de_defecto'),
            Api::campo($datos, 'descripcion'),
            Api::campo($datos, 'prioridad', 'Normal')
        );
        $resultado = $gestor->insertar($ticket);
        if (!$resultado['ok']) Api::resultado($resultado);
        Api::responder(201, 'success', 'Ticket registrado.', ['id_ticket' => (int) $resultado['datos']]);
    }

    Api::exigirRol(['Asistente', 'Coordinador']);
    $actual = $gestor->obtenerPorId($id);
    if (!$actual['ok']) Api::resultado($actual);
    if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese ticket.');
    $fila = $actual['datos'];
    if ($fila['estado_ticket'] === 'Cancelado') {
        Api::responder(409, 'error', 'Un ticket cancelado no puede modificarse.');
    }

    $estado = Api::campo($datos, 'estado_ticket', $fila['estado_ticket']);
    if ($estado === 'Cancelado') Api::responder(400, 'error', 'Usá la cancelación para ese estado.');
    $responsable = Api::campo($datos, 'ci_responsable', $fila['ci_responsable']);
    $cierre = null;
    if ($estado === 'Resuelto') {
        $cierre = $fila['fecha_hora_cierre'];
        if (!$cierre) $cierre = date('Y-m-d H:i:s', max(time(), strtotime($fila['fecha_hora_alta'])));
    }
    $ticket = new Ticket(
        $fila['fecha_hora_alta'],
        $fila['ci_solicitante'],
        Api::campo($datos, 'id_equipo', $fila['id_equipo']),
        Api::campo($datos, 'tipo_de_defecto', $fila['tipo_de_defecto']),
        Api::campo($datos, 'descripcion', $fila['descripcion']),
        Api::campo($datos, 'prioridad', $fila['prioridad']),
        $estado,
        $responsable !== '' ? $responsable : null,
        Api::campo($datos, 'diagnostico', $fila['diagnostico']) ?: null,
        Api::campo($datos, 'solucion', $fila['solucion']) ?: null,
        $cierre,
        $fila['id_ticket']
    );
    Api::resultado($gestor->actualizar($ticket), 'Ticket actualizado.');
} catch (Throwable $e) {
    error_log('SGRSI API tickets: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
