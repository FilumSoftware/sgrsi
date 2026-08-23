<?php

require_once __DIR__ . '/Sesion.php';

class ControlAcceso
{
    public static function exigirSesion($rutaLogin)
    {
        if (!Sesion::hayUsuario()) {
            header('Location: ' . $rutaLogin . '?motivo=sesion');
            exit;
        }
    }

    public static function exigirRol($rolesPermitidos, $rutaLogin)
    {
        self::exigirSesion($rutaLogin);

        if (!in_array(Sesion::rol(), $rolesPermitidos, true)) {
            http_response_code(403);
            require __DIR__ . '/../../FrontEnd/paginas/error/403.php';
            exit;
        }
    }

    public static function pantallaInicial()
    {
        if (Sesion::esCoordinador()) {
            return 'FrontEnd/paginas/dashboard/dashboard.php';
        }

        if (Sesion::esTecnico()) {
            return 'FrontEnd/paginas/mesa-de-ayuda/mesa-de-ayuda.php';
        }

        return 'FrontEnd/paginas/uso-sala/planilla-uso-sala.php';
    }

    public static function puedeGestionarUsuarios()
    {
        return Sesion::esCoordinador();
    }

    public static function puedeAtender()
    {
        return Sesion::esTecnico();
    }
}
