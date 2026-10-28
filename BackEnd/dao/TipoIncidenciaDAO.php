<?php

require_once __DIR__ . '/Conexion.php';

class TipoIncidenciaDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    public function obtenerTodos()
    {
        return $this->pdo->query('SELECT nombre, estado FROM tipo_incidencia ORDER BY nombre')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerActivos()
    {
        return $this->pdo->query("SELECT nombre, estado FROM tipo_incidencia WHERE estado = 'Activo' ORDER BY nombre")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorNombre($nombre)
    {
        $stmt = $this->pdo->prepare('SELECT nombre, estado FROM tipo_incidencia WHERE nombre = :nombre');
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($nombre)
    {
        $stmt = $this->pdo->prepare("INSERT INTO tipo_incidencia (nombre, estado) VALUES (:nombre, 'Activo')");
        return $stmt->execute([':nombre' => $nombre]);
    }

    public function actualizar($nombreActual, $nombreNuevo, $estado)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tipo_incidencia SET nombre = :nuevo, estado = :estado WHERE nombre = :actual'
        );
        return $stmt->execute([':actual' => $nombreActual, ':nuevo' => $nombreNuevo, ':estado' => $estado]);
    }
}
