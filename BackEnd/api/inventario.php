<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorInventario.php';

Api::iniciar();
Api::exigirRol(['Asistente', 'Coordinador']);
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorInventario();
$id = Api::parametro('id');

try {
    if ($metodo === 'GET') {
        if ($id === null) Api::resultado($gestor->obtenerTodos(), 'Inventario cargado.');
        $resultado = $gestor->obtenerPorId($id);
        if (!$resultado['ok']) Api::resultado($resultado);
        if (!$resultado['datos']) Api::responder(404, 'error', 'No existe ese equipo.');
        Api::resultado($resultado, 'Equipo cargado.');
    }

    Api::exigirToken();

    if ($metodo === 'DELETE') {
        $resultado = $gestor->darDeBaja($id);
        if (!$resultado['ok']) Api::resultado($resultado);
        if (!$resultado['datos']) Api::responder(404, 'error', 'No existe ese equipo.');
        Api::responder(200, 'success', 'Equipo dado de baja.', ['id_equipo' => (int) $id, 'estado_equipo' => 'De baja']);
    }

    $datos = Api::cuerpo();
    if ($metodo === 'POST') {
        if ($id !== null) Api::responder(400, 'error', 'El alta no lleva identificador.');
        $equipo = new Equipo(
            Api::campo($datos, 'nombre_equipo'),
            Api::campo($datos, 'descripcion') ?: null,
            Api::campo($datos, 'nombre_salon'),
            Api::campo($datos, 'estado_equipo', 'Operativo')
        );
        $resultado = $gestor->insertar($equipo);
        if (!$resultado['ok']) Api::resultado($resultado);
        Api::responder(201, 'success', 'Equipo registrado.', ['id_equipo' => (int) $resultado['datos']]);
    }

    $actual = $gestor->obtenerPorId($id);
    if (!$actual['ok']) Api::resultado($actual);
    if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese equipo.');
    $fila = $actual['datos'];
    $equipo = new Equipo(
        Api::campo($datos, 'nombre_equipo', $fila['nombre_equipo']),
        Api::campo($datos, 'descripcion', $fila['descripcion']) ?: null,
        Api::campo($datos, 'nombre_salon', $fila['nombre_salon']),
        Api::campo($datos, 'estado_equipo', $fila['estado_equipo']),
        $fila['id_equipo']
    );
    Api::resultado($gestor->actualizar($equipo), 'Equipo actualizado.');
} catch (Throwable $e) {
    error_log('SGRSI API inventario: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
