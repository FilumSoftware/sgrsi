<?php

require_once __DIR__ . '/Conexion.php';

class SalonDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query(
            'SELECT nombre_salon, descripcion FROM salon ORDER BY nombre_salon'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorNombre($nombreSalon)
    {
        $stmt = $this->pdo->prepare(
            'SELECT nombre_salon, descripcion FROM salon WHERE nombre_salon = :nombre'
        );
        $stmt->execute([':nombre' => $nombreSalon]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
