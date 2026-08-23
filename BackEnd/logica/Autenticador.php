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
