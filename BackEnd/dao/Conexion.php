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
            $this->pdo = new PDO($dsn, $config['usuario'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // El detalle va al log del servidor; al usuario nunca.
            error_log('SGRSI: falló la conexión a la base. ' . $e->getMessage());
            throw new RuntimeException('No se pudo conectar con la base de datos.');
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

    // Con el constructor privado, clonar y deserializar son las dos vías que
    // quedan para fabricar una segunda instancia. Se cierran las dos.
    private function __clone()
    {
    }

    public function __wakeup()
    {
        throw new RuntimeException('No se permite deserializar la conexión.');
    }
}
