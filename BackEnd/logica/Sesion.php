<?php

class Sesion
{
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function abrir($usuario)
    {
        self::iniciar();
        session_regenerate_id(true);

        $_SESSION['ci']     = $usuario['ci'];
        $_SESSION['nombre'] = $usuario['nombre_usuario'];
        $_SESSION['rol']    = $usuario['tipo_de_usuario'];
    }

    public static function cerrar()
    {
        self::iniciar();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']
            );
        }

        session_destroy();
    }

    public static function hayUsuario()
    {
        self::iniciar();

        return isset($_SESSION['ci']);
    }

    public static function ci()
    {
        self::iniciar();

        return isset($_SESSION['ci']) ? $_SESSION['ci'] : null;
    }

    public static function nombre()
    {
        self::iniciar();

        return isset($_SESSION['nombre']) ? $_SESSION['nombre'] : null;
    }

    public static function rol()
    {
        self::iniciar();

        return isset($_SESSION['rol']) ? $_SESSION['rol'] : null;
    }

    public static function esCoordinador()
    {
        return self::rol() === 'Coordinador';
    }

    public static function esTecnico()
    {
        return self::rol() === 'Asistente' || self::rol() === 'Coordinador';
    }
}
