<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/../models/Prestamo.php';

class PrestamoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    private function consultaBase()
    {
        return "SELECT p.id_prestamo, p.id_equipo, e.nombre_equipo, e.nombre_salon,
                       p.ci_solicitante, u.nombre_usuario AS nombre_solicitante,
                       p.ci_entrega, en.nombre_usuario AS nombre_entrega,
                       p.fecha_hora_prestamo, p.fecha_devolucion_prevista,
                       p.fecha_hora_devolucion, p.ci_recepcion,
                       p.fecha_hora_anulacion, p.ci_anulacion, p.observaciones,
                       CASE WHEN p.fecha_hora_anulacion IS NOT NULL THEN 'Anulado'
                            WHEN p.fecha_hora_devolucion IS NOT NULL THEN 'Devuelto'
                            ELSE 'Activo' END AS estado_prestamo
                  FROM prestamo p
                  JOIN equipo e ON e.id_equipo = p.id_equipo
                  JOIN usuario u ON u.ci = p.ci_solicitante
                  JOIN usuario en ON en.ci = p.ci_entrega";
    }

    public function obtenerTodos()
    {
        return $this->pdo->query($this->consultaBase() . ' ORDER BY p.fecha_hora_prestamo DESC, p.id_prestamo DESC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorSolicitante($ci)
    {
        $stmt = $this->pdo->prepare($this->consultaBase() . ' WHERE p.ci_solicitante = :ci ORDER BY p.fecha_hora_prestamo DESC');
        $stmt->execute([':ci' => $ci]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare($this->consultaBase() . ' WHERE p.id_prestamo = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerEquiposDisponibles()
    {
        $stmt = $this->pdo->query(
            "SELECT e.id_equipo, e.nombre_equipo, e.nombre_salon
               FROM equipo e
              WHERE e.estado_equipo = 'Operativo'
                AND NOT EXISTS (
                    SELECT 1 FROM prestamo p
                     WHERE p.id_equipo = e.id_equipo
                       AND p.fecha_hora_devolucion IS NULL
                       AND p.fecha_hora_anulacion IS NULL
                )
              ORDER BY e.nombre_salon, e.nombre_equipo"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrar(Prestamo $prestamo)
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT estado_equipo FROM equipo WHERE id_equipo = :id FOR UPDATE');
            $stmt->execute([':id' => $prestamo->getIdEquipo()]);
            $equipo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$equipo || $equipo['estado_equipo'] !== 'Operativo') {
                $this->pdo->rollBack();
                return false;
            }
            $stmt = $this->pdo->prepare(
                'SELECT id_prestamo FROM prestamo
                  WHERE id_equipo = :id
                    AND fecha_hora_devolucion IS NULL
                    AND fecha_hora_anulacion IS NULL
                  LIMIT 1'
            );
            $stmt->execute([':id' => $prestamo->getIdEquipo()]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->pdo->rollBack();
                return false;
            }
            $stmt = $this->pdo->prepare(
                'INSERT INTO prestamo (id_equipo, ci_solicitante, ci_entrega,
                                      fecha_hora_prestamo, fecha_devolucion_prevista, observaciones)
                 VALUES (:equipo, :solicitante, :entrega, NOW(), :prevista, :observaciones)'
            );
            $stmt->execute([
                ':equipo' => $prestamo->getIdEquipo(),
                ':solicitante' => $prestamo->getCiSolicitante(),
                ':entrega' => $prestamo->getCiEntrega(),
                ':prevista' => $prestamo->getFechaDevolucionPrevista(),
                ':observaciones' => $prestamo->getObservaciones()
            ]);
            $id = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function actualizar($id, $fechaPrevista, $observaciones)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE prestamo
                SET fecha_devolucion_prevista = :prevista, observaciones = :observaciones
              WHERE id_prestamo = :id
                AND fecha_hora_devolucion IS NULL
                AND fecha_hora_anulacion IS NULL'
        );
        $stmt->execute([':prevista' => $fechaPrevista, ':observaciones' => $observaciones, ':id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function devolver($id, $ci)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE prestamo
                SET fecha_hora_devolucion = GREATEST(NOW(), fecha_hora_prestamo), ci_recepcion = :ci
              WHERE id_prestamo = :id
                AND fecha_hora_devolucion IS NULL
                AND fecha_hora_anulacion IS NULL'
        );
        $stmt->execute([':ci' => $ci, ':id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function anular($id, $ci)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE prestamo
                SET fecha_hora_anulacion = GREATEST(NOW(), fecha_hora_prestamo), ci_anulacion = :ci
              WHERE id_prestamo = :id
                AND fecha_hora_devolucion IS NULL
                AND fecha_hora_anulacion IS NULL'
        );
        $stmt->execute([':ci' => $ci, ':id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
