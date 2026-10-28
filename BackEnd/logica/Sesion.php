<?php

class Sesion
{
    private const LIMITE_INACTIVIDAD = 600;

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
        $_SESSION['ultima_actividad'] = time();
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
        if (!isset($_SESSION['ci'])) return false;
        $ultimaActividad = $_SESSION['ultima_actividad'] ?? null;
        if (!is_int($ultimaActividad) || time() - $ultimaActividad >= self::LIMITE_INACTIVIDAD) {
            self::cerrar();
            return false;
        }
        $_SESSION['ultima_actividad'] = time();
        return true;
    }

    public static function ci()
    {
        return self::hayUsuario() ? $_SESSION['ci'] : null;
    }

    public static function nombre()
    {
        return self::hayUsuario() ? $_SESSION['nombre'] : null;
    }

    public static function rol()
    {
        return self::hayUsuario() ? $_SESSION['rol'] : null;
    }

    public static function esCoordinador()
    {
        return self::rol() === 'Coordinador';
    }

    public static function esTecnico()
    {
        return self::rol() === 'Asistente' || self::rol() === 'Coordinador';
    }

    public static function tokenCancelarTicket()
    {
        return self::tokenApi();
    }

    public static function tokenApi()
    {
        self::iniciar();
        if (empty($_SESSION['csrf_api'])) {
            $_SESSION['csrf_api'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_api'];
    }

    public static function verificarTokenCancelarTicket($token)
    {
        return self::verificarTokenApi($token);
    }

    public static function verificarTokenApi($token)
    {
        self::iniciar();
        return isset($_SESSION['csrf_api'])
            && is_string($token)
            && hash_equals($_SESSION['csrf_api'], $token);
    }
}
