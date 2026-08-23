<?php

require_once __DIR__ . '/../dao/UsuarioDAO.php';
require_once __DIR__ . '/Sesion.php';

class Autenticador
{
    const LARGO_MINIMO_CLAVE = 8;

    private $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    public function ingresar($ci, $clave)
    {
        $ci = trim((string) $ci);

        if (!$this->cedulaValida($ci) || $clave === '') {
            return $this->fallo(400, 'Revisá la cédula y la contraseña.');
        }

        $fila = $this->usuarioDAO->obtenerParaLogin($ci);

        if (!$fila || !password_verify($clave, $fila['contrasena'])) {
            return $this->fallo(401, 'Cédula o contraseña incorrecta.');
        }

        if ($fila['estado_cuenta'] !== 'Activa') {
            return $this->fallo(403, 'La cuenta está inactiva. Pedile al coordinador que la habilite.');
        }

        Sesion::abrir($fila);

        return ['ok' => true, 'codigo' => 200, 'mensaje' => ''];
    }

    public function registrar($ci, $nombre, $clave, $claveRepetida)
    {
        $ci     = trim((string) $ci);
        $nombre = trim((string) $nombre);

        if (!$this->cedulaValida($ci)) {
            return $this->fallo(400, 'La cédula son ocho dígitos, sin puntos ni guiones.');
        }

        if ($nombre === '') {
            return $this->fallo(400, 'Completá el nombre.');
        }

        if (strlen($clave) < self::LARGO_MINIMO_CLAVE) {
            return $this->fallo(400, 'La contraseña necesita al menos ' . self::LARGO_MINIMO_CLAVE . ' caracteres.');
        }

        if ($clave !== $claveRepetida) {
            return $this->fallo(400, 'Las contraseñas no coinciden.');
        }

        if ($this->usuarioDAO->obtenerPorCi($ci)) {
            return $this->fallo(409, 'Ya hay una cuenta registrada con esa cédula.');
        }

        $usuario = new Usuario($ci, $nombre, 'Solicitante', 'Inactiva');

        if (!$this->usuarioDAO->insertar($usuario, $clave)) {
            return $this->fallo(500, 'No se pudo crear la cuenta. Probá de nuevo.');
        }

        return [
            'ok'      => true,
            'codigo'  => 201,
            'mensaje' => 'Cuenta creada. Queda pendiente de habilitación por el coordinador.'
        ];
    }

    public function salir()
    {
        Sesion::cerrar();
    }

    private function cedulaValida($ci)
    {
        return preg_match('/^[0-9]{8}$/', $ci) === 1;
    }

    private function fallo($codigo, $mensaje)
    {
        return ['ok' => false, 'codigo' => $codigo, 'mensaje' => $mensaje];
    }
}
