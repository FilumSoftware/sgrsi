<?php

require_once __DIR__ . '/Sesion.php';

class Idioma
{
    private const DISPONIBLES = ['es', 'en'];
    private const POR_DEFECTO = 'es';
    private const COOKIE = 'sgrsi_idioma';
    private const DIAS_COOKIE = 365;

    private static $codigo = null;
    private static $textos = null;

    public static function iniciar()
    {
        if (isset($_GET['idioma'])) {
            self::fijar($_GET['idioma']);
        }
    }

    public static function fijar($codigo)
    {
        if (!in_array($codigo, self::DISPONIBLES, true)) {
            return;
        }

        Sesion::iniciar();

        $_SESSION['idioma'] = $codigo;
        $_COOKIE[self::COOKIE] = $codigo;

        if (!headers_sent()) {
            setcookie(self::COOKIE, $codigo, time() + self::DIAS_COOKIE * 86400, '/');
        }

        self::$codigo = $codigo;
        self::$textos = null;
    }

    public static function actual()
    {
        if (self::$codigo !== null) {
            return self::$codigo;
        }

        Sesion::iniciar();

        $elegido = isset($_SESSION['idioma']) ? $_SESSION['idioma'] : null;

        if ($elegido === null && isset($_COOKIE[self::COOKIE])) {
            $elegido = $_COOKIE[self::COOKIE];
        }

        self::$codigo = in_array($elegido, self::DISPONIBLES, true) ? $elegido : self::POR_DEFECTO;

        return self::$codigo;
    }

    public static function alternativo()
    {
        return self::actual() === 'es' ? 'en' : 'es';
    }

    public static function t($clave)
    {
        if (self::$textos === null) {
            self::$textos = require __DIR__ . '/../idiomas/' . self::actual() . '.php';
        }

        return isset(self::$textos[$clave]) ? self::$textos[$clave] : $clave;
    }

    public static function e($clave)
    {
        return htmlspecialchars(self::t($clave), ENT_QUOTES, 'UTF-8');
    }

    public static function hrefAlternativo()
    {
        $parametros = $_GET;
        $parametros['idioma'] = self::alternativo();

        $enlace = basename($_SERVER['SCRIPT_NAME']) . '?' . http_build_query($parametros);

        return htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8');
    }

    public static function json()
    {
        if (self::$textos === null) {
            self::$textos = require __DIR__ . '/../idiomas/' . self::actual() . '.php';
        }

        return json_encode(self::$textos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
