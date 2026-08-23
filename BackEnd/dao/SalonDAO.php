<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/../models/Salon.php';

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

    public function insertar(Salon $salon)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO salon (nombre_salon, descripcion)
             VALUES (:nombre, :descripcion)'
        );

        return $stmt->execute([
            ':nombre'      => $salon->getNombreSalon(),
            ':descripcion' => $salon->getDescripcion()
        ]);
    }

    public function actualizar(Salon $salon)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE salon SET descripcion = :descripcion WHERE nombre_salon = :nombre'
        );

        return $stmt->execute([
            ':descripcion' => $salon->getDescripcion(),
            ':nombre'      => $salon->getNombreSalon()
        ]);
    }

    public function eliminar($nombreSalon)
    {
        $stmt = $this->pdo->prepare('DELETE FROM salon WHERE nombre_salon = :nombre');

        return $stmt->execute([':nombre' => $nombreSalon]);
    }
}
