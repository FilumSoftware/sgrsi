<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/../models/Solicitud.php';

class SolicitudDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    private function consultaBase()
    {
        return 'SELECT c.id_solicitud, c.fecha_hora_alta, c.nombre_salon, c.descripcion,
                       c.prioridad, c.estado_solicitud, c.fecha_hora_cierre,
                       c.ci_solicitante, c.ci_responsable,
                       s.nombre_usuario AS nombre_solicitante,
                       r.nombre_usuario AS nombre_responsable
                  FROM solicitud c
                  JOIN usuario s ON s.ci = c.ci_solicitante
                  LEFT JOIN usuario r ON r.ci = c.ci_responsable';
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query($this->consultaBase() . ' ORDER BY c.fecha_hora_alta DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($idSolicitud)
    {
        $stmt = $this->pdo->prepare($this->consultaBase() . ' WHERE c.id_solicitud = :id');
        $stmt->execute([':id' => $idSolicitud]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorSolicitante($ci)
    {
        $stmt = $this->pdo->prepare(
            $this->consultaBase() . ' WHERE c.ci_solicitante = :ci ORDER BY c.fecha_hora_alta DESC'
        );
        $stmt->execute([':ci' => $ci]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar(Solicitud $solicitud)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO solicitud (fecha_hora_alta, ci_solicitante, nombre_salon,
                                    descripcion, prioridad, estado_solicitud)
             VALUES (:alta, :solicitante, :salon, :descripcion, :prioridad, :estado)'
        );
        $stmt->execute([
            ':alta'        => $solicitud->getFechaHoraAlta(),
            ':solicitante' => $solicitud->getCiSolicitante(),
            ':salon'       => $solicitud->getNombreSalon(),
            ':descripcion' => $solicitud->getDescripcion(),
            ':prioridad'   => $solicitud->getPrioridad(),
            ':estado'      => $solicitud->getEstadoSolicitud()
        ]);

        return $this->pdo->lastInsertId();
    }

    public function actualizar(Solicitud $solicitud)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE solicitud
                SET nombre_salon = :salon,
                    descripcion = :descripcion,
                    prioridad = :prioridad,
                    estado_solicitud = :estado,
                    ci_responsable = :responsable,
                    fecha_hora_cierre = :cierre
              WHERE id_solicitud = :id'
        );

        return $stmt->execute([
            ':salon'       => $solicitud->getNombreSalon(),
            ':descripcion' => $solicitud->getDescripcion(),
            ':prioridad'   => $solicitud->getPrioridad(),
            ':estado'      => $solicitud->getEstadoSolicitud(),
            ':responsable' => $solicitud->getCiResponsable(),
            ':cierre'      => $solicitud->getFechaHoraCierre(),
            ':id'          => $solicitud->getIdSolicitud()
        ]);
    }

    public function eliminar($idSolicitud)
    {
        $stmt = $this->pdo->prepare('DELETE FROM solicitud WHERE id_solicitud = :id');

        return $stmt->execute([':id' => $idSolicitud]);
    }

    public function contarPorEstado()
    {
        $stmt = $this->pdo->query(
            'SELECT estado_solicitud, COUNT(*) AS cantidad
               FROM solicitud
              GROUP BY estado_solicitud'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
