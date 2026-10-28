<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/../dao/UsuarioDAO.php';

class GestorUsuarios
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('usuarios listar', function () { return (new UsuarioDAO())->obtenerTodos(); });
    }

    public function obtenerPorCi($ci)
    {
        if (!ResultadoGestion::ciValida($ci)) return ResultadoGestion::fallo(400, 'Cédula inválida.');
        return ResultadoGestion::ejecutar('usuarios consultar', function () use ($ci) { return (new UsuarioDAO())->obtenerPorCi($ci); });
    }

    public function obtenerTecnicos()
    {
        return ResultadoGestion::ejecutar('usuarios técnicos', function () { return (new UsuarioDAO())->obtenerTecnicos(); });
    }

    public function crearDesdeFormulario($datos)
    {
        return ResultadoGestion::procesar('usuarios formulario de alta', function () use ($datos) {
            $ci = ResultadoGestion::campo($datos, 'ci');
            $nombre = ResultadoGestion::campo($datos, 'nombre');
            $rol = ResultadoGestion::campo($datos, 'tipo-usuario');
            $clave = isset($datos['password']) && is_string($datos['password']) ? $datos['password'] : '';
            $confirmacion = isset($datos['confirmar-password']) && is_string($datos['confirmar-password']) ? $datos['confirmar-password'] : '';
            if ($clave !== $confirmacion) return ResultadoGestion::fallo(400, 'Las contraseñas no coinciden.');
            return $this->insertar(new Usuario($ci, $nombre, $rol, 'Activa'), $clave);
        });
    }

    public function actualizarDesdeFormulario($ci, $datos, $ciActual)
    {
        return ResultadoGestion::procesar('usuarios formulario de edición', function () use ($ci, $datos, $ciActual) {
            $actual = $this->obtenerPorCi($ci);
            if (!$actual['ok']) return $actual;
            if (!$actual['datos']) return ResultadoGestion::fallo(404, 'No existe ese usuario.');
            $fila = $actual['datos'];
            $nombre = ResultadoGestion::campo($datos, 'nombre');
            $rol = $ci === $ciActual ? $fila['tipo_de_usuario'] : ResultadoGestion::campo($datos, 'tipo-usuario');
            $estado = $ci === $ciActual ? $fila['estado_cuenta'] : ResultadoGestion::campo($datos, 'estado');
            return $this->actualizar(new Usuario($ci, $nombre, $rol, $estado));
        });
    }

    public function cambiarEstadoDesdeFormulario($ci, $estado, $ciActual)
    {
        if ($ci === $ciActual) return ResultadoGestion::fallo(409, 'No podés cambiar el estado de tu propia cuenta.');
        return $this->cambiarEstado($ci, $estado);
    }

    public function insertar(Usuario $usuario, $clave)
    {
        $error = $this->validar($usuario);
        if ($error) return $error;
        if (strlen((string) $clave) < 8) return ResultadoGestion::fallo(400, 'La contraseña debe tener al menos 8 caracteres.');
        $actual = $this->obtenerPorCi($usuario->getCi());
        if (!$actual['ok']) return $actual;
        if ($actual['datos']) return ResultadoGestion::fallo(409, 'Ya hay una cuenta registrada con esa cédula.');
        return ResultadoGestion::ejecutar('usuarios crear', function () use ($usuario, $clave) { return (new UsuarioDAO())->insertar($usuario, $clave); });
    }

    public function actualizar(Usuario $usuario)
    {
        $error = $this->validar($usuario);
        if ($error) return $error;
        return ResultadoGestion::ejecutar('usuarios actualizar', function () use ($usuario) { return (new UsuarioDAO())->actualizar($usuario); });
    }

    public function cambiarEstado($ci, $estado)
    {
        if (!ResultadoGestion::ciValida($ci) || !in_array($estado, ['Activa', 'Inactiva'], true)) return ResultadoGestion::fallo(400, 'Usuario o estado inválido.');
        return ResultadoGestion::ejecutar('usuarios cambiar estado', function () use ($ci, $estado) { return (new UsuarioDAO())->cambiarEstado($ci, $estado); });
    }

    private function validar(Usuario $usuario)
    {
        if (!ResultadoGestion::ciValida($usuario->getCi())) return ResultadoGestion::fallo(400, 'Cédula inválida.');
        if (trim((string) $usuario->getNombreUsuario()) === '' || strlen($usuario->getNombreUsuario()) > 60) return ResultadoGestion::fallo(400, 'Nombre inválido.');
        if (!in_array($usuario->getTipoDeUsuario(), ['Solicitante', 'Asistente', 'Coordinador'], true)) return ResultadoGestion::fallo(400, 'Rol inválido.');
        if (!in_array($usuario->getEstadoCuenta(), ['Activa', 'Inactiva'], true)) return ResultadoGestion::fallo(400, 'Estado inválido.');
        return null;
    }
}
