<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorPrestamos.php';

Api::iniciar();
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorPrestamos();
$id = Api::parametro('id');

try {
    if ($metodo === 'GET') {
        if (Api::parametro('catalogos') === '1') {
            Api::exigirRol(['Asistente', 'Coordinador']);
            Api::resultado($gestor->obtenerCatalogos(), 'Catálogos cargados.');
        }
        if ($id === null) {
            Api::resultado(
                Sesion::esTecnico() ? $gestor->obtenerTodos() : $gestor->obtenerPorSolicitante(Sesion::ci()),
                'Préstamos cargados.'
            );
        }
        $resultado = $gestor->obtenerPorId($id);
        if (!$resultado['ok']) Api::resultado($resultado);
        if (!$resultado['datos']) Api::responder(404, 'error', 'No existe ese préstamo.');
        if (!Sesion::esTecnico() && $resultado['datos']['ci_solicitante'] !== Sesion::ci()) {
            Api::responder(403, 'error', 'No tenés permiso para ver este préstamo.');
        }
        Api::resultado($resultado, 'Préstamo cargado.');
    }

    Api::exigirRol(['Asistente', 'Coordinador']);
    Api::exigirToken();

    if ($metodo === 'DELETE') {
        Api::exigirRol(['Coordinador']);
        Api::resultado($gestor->anular($id, Sesion::ci()));
    }

    $datos = Api::cuerpo();

    if ($metodo === 'POST') {
        if ($id !== null) Api::responder(400, 'error', 'El alta no lleva identificador.');
        $fecha = Api::campo($datos, 'fecha_devolucion_prevista');
        $observaciones = Api::campo($datos, 'observaciones');
        $prestamo = new Prestamo(
            Api::campo($datos, 'id_equipo'),
            Api::campo($datos, 'ci_solicitante'),
            Sesion::ci(),
            $fecha !== '' ? $fecha : null,
            $observaciones !== '' ? $observaciones : null
        );
        $resultado = $gestor->registrar($prestamo);
        if (!$resultado['ok']) Api::resultado($resultado);
        Api::responder(201, 'success', 'Préstamo registrado.', ['id_prestamo' => (int) $resultado['datos']]);
    }

    $accion = Api::campo($datos, 'accion', 'actualizar');
    if ($accion === 'devolver') Api::resultado($gestor->devolver($id, Sesion::ci()));
    if ($accion !== 'actualizar') Api::responder(400, 'error', 'Acción inválida.');
    $actual = $gestor->obtenerPorId($id);
    if (!$actual['ok']) Api::resultado($actual);
    if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese préstamo.');
    $fecha = Api::campo($datos, 'fecha_devolucion_prevista', $actual['datos']['fecha_devolucion_prevista']);
    $observaciones = Api::campo($datos, 'observaciones', $actual['datos']['observaciones']);
    Api::resultado(
        $gestor->actualizar($id, $fecha !== '' ? $fecha : null, $observaciones !== '' ? $observaciones : null)
    );
} catch (Throwable $e) {
    error_log('SGRSI API préstamos: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
