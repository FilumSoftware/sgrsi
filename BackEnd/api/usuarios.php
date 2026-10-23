<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorUsuarios.php';

Api::iniciar();
$metodo = Api::metodo(['GET', 'POST', 'PUT', 'DELETE']);
$gestor = new GestorUsuarios();
$ci = Api::parametro('ci');

try {
    if ($metodo === 'GET') {
        if ($ci === null) {
            Api::exigirRol(['Coordinador']);
            Api::resultado($gestor->obtenerTodos(), 'Usuarios cargados.');
        }
        if (!Sesion::esCoordinador() && $ci !== Sesion::ci()) {
            Api::responder(403, 'error', 'No tenés permiso para ver este usuario.');
        }
        $resultado = $gestor->obtenerPorCi($ci);
        if (!$resultado['ok']) Api::resultado($resultado);
        if (!$resultado['datos']) Api::responder(404, 'error', 'No existe ese usuario.');
        Api::resultado($resultado, 'Usuario cargado.');
    }

    Api::exigirToken();

    if ($metodo === 'DELETE') {
        Api::exigirRol(['Coordinador']);
        if ($ci === Sesion::ci()) Api::responder(409, 'error', 'No podés inhabilitar tu propia cuenta.');
        $actual = $gestor->obtenerPorCi($ci);
        if (!$actual['ok']) Api::resultado($actual);
        if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese usuario.');
        Api::resultado($gestor->cambiarEstado($ci, 'Inactiva'), 'Usuario inhabilitado.');
    }

    $datos = Api::cuerpo();
    if ($metodo === 'POST') {
        Api::exigirRol(['Coordinador']);
        if ($ci !== null) Api::responder(400, 'error', 'El alta no lleva cédula en la URL.');
        $usuario = new Usuario(
            Api::campo($datos, 'ci'),
            Api::campo($datos, 'nombre_usuario'),
            Api::campo($datos, 'tipo_de_usuario'),
            'Activa'
        );
        $resultado = $gestor->insertar($usuario, Api::campo($datos, 'contrasena'));
        if (!$resultado['ok']) Api::resultado($resultado);
        Api::responder(201, 'success', 'Usuario registrado.', ['ci' => $usuario->getCi()]);
    }

    if (!Sesion::esCoordinador() && $ci !== Sesion::ci()) {
        Api::responder(403, 'error', 'No tenés permiso para modificar este usuario.');
    }
    $actual = $gestor->obtenerPorCi($ci);
    if (!$actual['ok']) Api::resultado($actual);
    if (!$actual['datos']) Api::responder(404, 'error', 'No existe ese usuario.');
    $fila = $actual['datos'];
    $rol = Sesion::esCoordinador()
        ? Api::campo($datos, 'tipo_de_usuario', $fila['tipo_de_usuario'])
        : $fila['tipo_de_usuario'];
    $estado = Sesion::esCoordinador()
        ? Api::campo($datos, 'estado_cuenta', $fila['estado_cuenta'])
        : $fila['estado_cuenta'];
    if ($ci === Sesion::ci() && ($rol !== $fila['tipo_de_usuario'] || $estado !== $fila['estado_cuenta'])) {
        Api::responder(409, 'error', 'No podés cambiar tu propio rol ni tu estado.');
    }
    $usuario = new Usuario(
        $fila['ci'],
        Api::campo($datos, 'nombre_usuario', $fila['nombre_usuario']),
        $rol,
        $estado
    );
    Api::resultado($gestor->actualizar($usuario), 'Usuario actualizado.');
} catch (Throwable $e) {
    error_log('SGRSI API usuarios: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
}
