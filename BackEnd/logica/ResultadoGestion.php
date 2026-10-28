<?php

class ResultadoGestion
{
    public static function ok($datos = null, $codigo = 200, $mensaje = '')
    {
        return ['ok' => true, 'codigo' => $codigo, 'mensaje' => $mensaje, 'datos' => $datos];
    }

    public static function fallo($codigo, $mensaje)
    {
        return ['ok' => false, 'codigo' => $codigo, 'mensaje' => $mensaje, 'datos' => null];
    }

    public static function ejecutar($operacion, $accion)
    {
        try {
            return self::ok($accion());
        } catch (Throwable $e) {
            error_log('SGRSI ' . $operacion . ': ' . $e->getMessage());
            return self::fallo(500, 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
        }
    }

    public static function procesar($operacion, $accion)
    {
        try {
            return $accion();
        } catch (Throwable $e) {
            error_log('SGRSI ' . $operacion . ': ' . $e->getMessage());
            return self::fallo(500, 'No se pudo completar la operación. Intentá de nuevo en unos minutos.');
        }
    }

    public static function campo($datos, $clave)
    {
        return isset($datos[$clave]) && is_scalar($datos[$clave])
            ? trim((string) $datos[$clave]) : '';
    }

    public static function valor($resultado)
    {
        if (!$resultado['ok']) {
            throw new RuntimeException($resultado['mensaje'], $resultado['codigo']);
        }
        return $resultado['datos'];
    }

    public static function idValido($id)
    {
        return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    public static function ciValida($ci)
    {
        return preg_match('/^[0-9]{8}$/', (string) $ci) === 1;
    }
}
