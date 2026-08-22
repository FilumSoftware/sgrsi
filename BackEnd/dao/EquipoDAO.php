<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/../models/Equipo.php';

class EquipoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query(
            'SELECT id_equipo, nombre_equipo, categoria, descripcion, nombre_salon, estado_equipo
               FROM equipo
              ORDER BY nombre_salon, nombre_equipo'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($idEquipo)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_equipo, nombre_equipo, categoria, descripcion, nombre_salon, estado_equipo
               FROM equipo
              WHERE id_equipo = :id'
        );
        $stmt->execute([':id' => $idEquipo]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorSalon($nombreSalon)
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_equipo, nombre_equipo, categoria, estado_equipo
               FROM equipo
              WHERE nombre_salon = :salon
              ORDER BY nombre_equipo'
        );
        $stmt->execute([':salon' => $nombreSalon]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar(Equipo $equipo)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO equipo (nombre_equipo, categoria, descripcion, nombre_salon, estado_equipo)
             VALUES (:nombre, :categoria, :descripcion, :salon, :estado)'
        );
        $stmt->execute([
            ':nombre'      => $equipo->getNombreEquipo(),
            ':categoria'   => $equipo->getCategoria(),
            ':descripcion' => $equipo->getDescripcion(),
            ':salon'       => $equipo->getNombreSalon(),
            ':estado'      => $equipo->getEstadoEquipo()
        ]);

        return $this->pdo->lastInsertId();
    }

    public function actualizar(Equipo $equipo)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE equipo
                SET nombre_equipo = :nombre,
                    categoria = :categoria,
                    descripcion = :descripcion,
                    nombre_salon = :salon,
                    estado_equipo = :estado
              WHERE id_equipo = :id'
        );

        return $stmt->execute([
            ':nombre'      => $equipo->getNombreEquipo(),
            ':categoria'   => $equipo->getCategoria(),
            ':descripcion' => $equipo->getDescripcion(),
            ':salon'       => $equipo->getNombreSalon(),
            ':estado'      => $equipo->getEstadoEquipo(),
            ':id'          => $equipo->getIdEquipo()
        ]);
    }

    public function eliminar($idEquipo)
    {
        $stmt = $this->pdo->prepare('DELETE FROM equipo WHERE id_equipo = :id');

        return $stmt->execute([':id' => $idEquipo]);
    }
}
