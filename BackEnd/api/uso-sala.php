<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorUsoSala.php';

function horaApi($valor)
{
    return preg_match('/^[0-9]{2}:[0-9]{2}$/', $valor) === 1 ? $valor . ':00' : $valor;
}

function detallesApi($filas)
{
    if (!is_array($filas) || !array_is_list($filas)) {
        Api::responder(400, 'error', 'Lista de equipos inválida.');
    }
    $detalles = [];
    foreach ($filas as $fila) {
        if (!is_array($fila)) Api::responder(400, 'error', 'Equipo asignado inválido.');
        $alumno = Api::campo($fila, 'nombre_alumno');
        $detalles[] = new DetalleUso(Api::campo($fila, 'id_equipo'), $alumno !== '' ? $alumno : null);
    }
    return $detalles;
}

Api::iniciar();
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorUsoSala();
$id = Api::parametro('id');

try {
    if ($metodo === 'GET') {
        if ($id === null) {
            Api::resultado(
                Sesion::esTecnico() ? $gestor->obtenerTodos() : $gestor->obtenerPorSolicitante(Sesion::ci()),
                'Usos de sala cargados.'
            );
        }
        $resultado = $gestor->obtenerPorId($id);
        if (!$resultado['ok']) Api::resultado($resultado);
        if (!$resultado['datos']) Api::responder(404, 'error', 'No existe ese registro.');
        if (!Sesion::esTecnico() && $resultado['datos']['ci_solicitante'] !== Sesion::ci()) {
            Api::responder(403, 'error', 'No tenés permiso para ver este registro.');
        }
        $detalle = $gestor->obtenerDetalle($id);
        if (!$detalle['ok']) Api::resultado($detalle);
        $fila = $resultado['datos'];
        $fila['equipos'] = $detalle['datos'];
        Api::responder(200, 'success', 'Uso de sala cargado.', $fila);
    }

    Api::exigirToken();

    if ($metodo === 'DELETE') {
        Api::exigirRol(['Coordinador']);
        $actual = $gestor->obtenerPorId($id);
        if (!$actual['ok']) Api::resultado($actual);
        if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese registro.');
        Api::resultado($gestor->eliminar($id), 'Registro eliminado.');
    }

    $datos = Api::cuerpo();
    if ($metodo === 'POST') {
        if ($id !== null) Api::responder(400, 'error', 'El alta no lleva identificador.');
        $uso = new UsoSala(
            Api::campo($datos, 'fecha'),
            horaApi(Api::campo($datos, 'hora_inicio')),
            horaApi(Api::campo($datos, 'hora_fin')),
            Api::campo($datos, 'nombre_salon'),
            Sesion::ci(),
            Api::campo($datos, 'asignatura'),
            Api::campo($datos, 'grupo'),
            Api::campo($datos, 'turno')
        );
        $resultado = $gestor->insertar($uso, detallesApi(isset($datos['equipos']) ? $datos['equipos'] : null));
        if (!$resultado['ok']) Api::resultado($resultado);
        Api::responder(201, 'success', 'Uso de sala registrado.', ['id_uso' => (int) $resultado['datos']]);
    }

    $actual = $gestor->obtenerPorId($id);
    if (!$actual['ok']) Api::resultado($actual);
    if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese registro.');
    $fila = $actual['datos'];
    if (!Sesion::esTecnico() && $fila['ci_solicitante'] !== Sesion::ci()) {
        Api::responder(403, 'error', 'No tenés permiso para modificar este registro.');
    }
    $detallesActuales = $gestor->obtenerDetalle($id);
    if (!$detallesActuales['ok']) Api::resultado($detallesActuales);
    $uso = new UsoSala(
        Api::campo($datos, 'fecha', $fila['fecha']),
        horaApi(Api::campo($datos, 'hora_inicio', $fila['hora_inicio'])),
        horaApi(Api::campo($datos, 'hora_fin', $fila['hora_fin'])),
        Api::campo($datos, 'nombre_salon', $fila['nombre_salon']),
        $fila['ci_solicitante'],
        Api::campo($datos, 'asignatura', $fila['asignatura']),
        Api::campo($datos, 'grupo', $fila['grupo']),
        Api::campo($datos, 'turno', $fila['turno']),
        $fila['id_uso']
    );
    $detalle = array_key_exists('equipos', $datos) ? $datos['equipos'] : $detallesActuales['datos'];
    Api::resultado($gestor->actualizar($uso, detallesApi($detalle)), 'Uso de sala actualizado.');
} catch (Throwable $e) {
    error_log('SGRSI API uso de sala: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
