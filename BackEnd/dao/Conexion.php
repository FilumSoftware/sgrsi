<?php

class Conexion
{
    private static $instancia = null;
    private $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = 'mysql:host=' . $config['host']
             . ';port=' . $config['puerto']
             . ';dbname=' . $config['basedatos']
             . ';charset=' . $config['charset'];

        try {
            $this->pdo = new PDO($dsn, $config['usuario'], $config['password']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // El detalle va al log del servidor, al usuario nunca.
            error_log('SGRSI: fallo la conexion. ' . $e->getMessage());
            throw new Exception('No se pudo conectar con la base de datos.');
        }
    }

    public static function getInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new Conexion();
        }

        return self::$instancia;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    private function __clone()
    {
    }
}
