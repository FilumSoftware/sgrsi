<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorSalones.php';

Api::iniciar();
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorSalones();
$nombre = Api::parametro('nombre');

try {
    if ($metodo === 'GET') {
        Api::exigirRol(['Asistente', 'Coordinador']);
        if ($nombre === null) Api::resultado($gestor->obtenerTodos(), 'Salones cargados.');
        $resultado = $gestor->obtenerPorNombre($nombre);
        if (!$resultado['ok']) Api::resultado($resultado);
        if (!$resultado['datos']) Api::responder(404, 'error', 'No existe ese salón.');
        Api::resultado($resultado, 'Salón cargado.');
    }

    Api::exigirRol(['Coordinador']);
    Api::exigirToken();

    if ($metodo === 'DELETE') {
        Api::resultado($gestor->eliminar($nombre), 'Salón eliminado.');
    }

    $datos = Api::cuerpo();

    if ($metodo === 'POST') {
        if ($nombre !== null) Api::responder(400, 'error', 'El alta no lleva nombre en la URL.');
        Api::resultado(
            $gestor->insertar(Api::campo($datos, 'nombre_salon'), Api::campo($datos, 'descripcion')),
            'Salón registrado.'
        );
    }

    Api::resultado($gestor->actualizar($nombre, Api::campo($datos, 'descripcion')), 'Salón actualizado.');
} catch (Throwable $e) {
    error_log('SGRSI API salones: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
