<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorTiposIncidencia.php';

Api::iniciar();
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorTiposIncidencia();

try {
    if ($metodo === 'GET') {
        if (Api::parametro('todos') === '1') {
            Api::exigirRol(['Asistente', 'Coordinador']);
            Api::resultado($gestor->obtenerTodos(), 'Tipos de incidencia cargados.');
        }
        Api::resultado($gestor->obtenerActivos(), 'Tipos de incidencia cargados.');
    }

    Api::exigirRol(['Asistente', 'Coordinador']);
    Api::exigirToken();
    $nombre = Api::parametro('nombre');

    if ($metodo === 'DELETE') {
        Api::resultado($gestor->desactivar($nombre), 'Tipo de incidencia inactivado.');
    }

    $datos = Api::cuerpo();
    if ($metodo === 'POST') {
        if ($nombre !== null) Api::responder(400, 'error', 'El alta no lleva nombre en la URL.');
        Api::resultado($gestor->insertar(Api::campo($datos, 'nombre')), 'Tipo de incidencia creado.');
    }

    Api::resultado(
        $gestor->actualizar($nombre, Api::campo($datos, 'nombre'), Api::campo($datos, 'estado')),
        'Tipo de incidencia actualizado.'
    );
} catch (Throwable $e) {
    error_log('SGRSI API tipos de incidencia: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
