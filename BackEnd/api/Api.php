<?php

require_once __DIR__ . '/../logica/Sesion.php';

class Api
{
    public static function iniciar()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (!Sesion::hayUsuario()) {
            self::responder(401, 'error', 'Iniciá sesión para continuar.');
        }
    }

    public static function metodo($permitidos)
    {
        $metodo = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if (!in_array($metodo, $permitidos, true)) {
            header('Allow: ' . implode(', ', $permitidos));
            self::responder(405, 'error', 'Método no admitido en esta operación.');
        }
        return $metodo;
    }

    public static function exigirRol($roles)
    {
        if (!in_array(Sesion::rol(), $roles, true)) {
            self::responder(403, 'error', 'No tenés permiso para esta operación.');
        }
    }

    public static function exigirToken()
    {
        $token = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
        if (!Sesion::verificarTokenApi($token)) {
            self::responder(403, 'error', 'La sesión de esta página venció. Volvé a abrirla.');
        }
    }

    public static function cuerpo()
    {
        if (strpos(isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '', 'application/json') !== 0) {
            self::responder(400, 'error', 'El contenido debe ser JSON.');
        }
        $datos = json_decode(file_get_contents('php://input'), true);
        if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE) {
            self::responder(400, 'error', 'JSON inválido.');
        }
        return $datos;
    }

    public static function parametro($clave)
    {
        if (!isset($_GET[$clave])) return null;
        if (!is_scalar($_GET[$clave])) self::responder(400, 'error', 'Parámetro inválido: ' . $clave . '.');
        return trim((string) $_GET[$clave]);
    }

    public static function campo($datos, $clave, $valor = '')
    {
        if (!array_key_exists($clave, $datos)) return $valor;
        if (!is_scalar($datos[$clave]) && $datos[$clave] !== null) {
            self::responder(400, 'error', 'Campo inválido: ' . $clave . '.');
        }
        return trim((string) $datos[$clave]);
    }

    public static function resultado($resultado, $mensaje = 'Operación completada.')
    {
        self::responder(
            $resultado['codigo'],
            $resultado['ok'] ? 'success' : 'error',
            $resultado['mensaje'] !== '' ? $resultado['mensaje'] : $mensaje,
            $resultado['datos']
        );
    }

    public static function responder($codigo, $estado, $mensaje, $datos = null)
    {
        http_response_code($codigo);
        echo json_encode(
            ['status' => $estado, 'message' => $mensaje, 'data' => $datos],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }
}
