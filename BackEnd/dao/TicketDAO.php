<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/../models/Ticket.php';

class TicketDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    private function consultaBase()
    {
        return 'SELECT t.id_ticket, t.fecha_hora_alta, t.tipo_de_defecto, t.detalle_defecto, t.descripcion,
                       TIMESTAMPDIFF(SECOND, t.fecha_hora_alta, NOW()) AS segundos_desde_alta,
                       t.prioridad, t.estado_ticket, t.diagnostico, t.solucion,
                       t.fecha_hora_cierre, t.ci_solicitante, t.ci_responsable,
                       e.id_equipo, e.nombre_equipo, e.nombre_salon,
                       s.nombre_usuario AS nombre_solicitante,
                       r.nombre_usuario AS nombre_responsable
                  FROM ticket t
                  JOIN equipo e ON e.id_equipo = t.id_equipo
                  JOIN usuario s ON s.ci = t.ci_solicitante
                  LEFT JOIN usuario r ON r.ci = t.ci_responsable';
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query($this->consultaBase() . ' ORDER BY t.fecha_hora_alta DESC');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($idTicket)
    {
        $stmt = $this->pdo->prepare($this->consultaBase() . ' WHERE t.id_ticket = :id');
        $stmt->execute([':id' => $idTicket]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorSolicitante($ci)
    {
        $stmt = $this->pdo->prepare(
            $this->consultaBase() . ' WHERE t.ci_solicitante = :ci ORDER BY t.fecha_hora_alta DESC'
        );
        $stmt->execute([':ci' => $ci]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorEstado($estado)
    {
        $stmt = $this->pdo->prepare(
            $this->consultaBase() . ' WHERE t.estado_ticket = :estado ORDER BY t.fecha_hora_alta DESC'
        );
        $stmt->execute([':estado' => $estado]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar(Ticket $ticket)
    {
        $this->pdo->beginTransaction();
        try {
            // El bloqueo del equipo serializa altas simultáneas para la misma PC.
            $equipo = $this->pdo->prepare('SELECT id_equipo FROM equipo WHERE id_equipo = :id FOR UPDATE');
            $equipo->execute([':id' => $ticket->getIdEquipo()]);
            if ($equipo->fetchColumn() === false) {
                $this->pdo->rollBack();
                return null;
            }

            $abierto = $this->pdo->prepare(
                "SELECT id_ticket FROM ticket
                  WHERE id_equipo = :equipo AND estado_ticket IN ('Pendiente', 'En proceso')
                  LIMIT 1"
            );
            $abierto->execute([':equipo' => $ticket->getIdEquipo()]);
            if ($abierto->fetchColumn() !== false) {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare(
                'INSERT INTO ticket (fecha_hora_alta, ci_solicitante, id_equipo, tipo_de_defecto,
                                     detalle_defecto, descripcion, prioridad, estado_ticket)
                 VALUES (:alta, :solicitante, :equipo, :defecto, :detalle, :descripcion, :prioridad, :estado)'
            );
            $stmt->execute([
                ':alta'        => $ticket->getFechaHoraAlta(),
                ':solicitante' => $ticket->getCiSolicitante(),
                ':equipo'      => $ticket->getIdEquipo(),
                ':defecto'     => $ticket->getTipoDeDefecto(),
                ':detalle'     => $ticket->getDetalleDefecto(),
                ':descripcion' => $ticket->getDescripcion(),
                ':prioridad'   => $ticket->getPrioridad(),
                ':estado'      => $ticket->getEstadoTicket()
            ]);
            $id = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function actualizar(Ticket $ticket)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE ticket
                SET id_equipo = :equipo,
                    tipo_de_defecto = :defecto,
                    detalle_defecto = :detalle,
                    descripcion = :descripcion,
                    prioridad = :prioridad,
                    estado_ticket = :estado,
                    ci_responsable = :responsable,
                    diagnostico = :diagnostico,
                    solucion = :solucion,
                    fecha_hora_cierre = :cierre
              WHERE id_ticket = :id
                AND estado_ticket <> 'Cancelado'"
        );

        $stmt->execute([
            ':equipo'      => $ticket->getIdEquipo(),
            ':defecto'     => $ticket->getTipoDeDefecto(),
            ':detalle'     => $ticket->getDetalleDefecto(),
            ':descripcion' => $ticket->getDescripcion(),
            ':prioridad'   => $ticket->getPrioridad(),
            ':estado'      => $ticket->getEstadoTicket(),
            ':responsable' => $ticket->getCiResponsable(),
            ':diagnostico' => $ticket->getDiagnostico(),
            ':solucion'    => $ticket->getSolucion(),
            ':cierre'      => $ticket->getFechaHoraCierre(),
            ':id'          => $ticket->getIdTicket()
        ]);

        return $stmt->rowCount() === 1;
    }

    public function eliminar($idTicket)
    {
        $stmt = $this->pdo->prepare('DELETE FROM ticket WHERE id_ticket = :id');

        return $stmt->execute([':id' => $idTicket]);
    }

    public function cancelarPendiente($idTicket, $ciSolicitante)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE ticket
                SET estado_ticket = 'Cancelado',
                    fecha_hora_cierre = GREATEST(NOW(), fecha_hora_alta)
              WHERE id_ticket = :id
                AND ci_solicitante = :ci
                AND estado_ticket = 'Pendiente'
                AND ci_responsable IS NULL"
        );
        $stmt->execute([':id' => $idTicket, ':ci' => $ciSolicitante]);

        return $stmt->rowCount() === 1;
    }

    public function contarPorEstado()
    {
        $stmt = $this->pdo->query(
            'SELECT estado_ticket, COUNT(*) AS cantidad
               FROM ticket
              GROUP BY estado_ticket'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function equiposMasFallados()
    {
        $stmt = $this->pdo->query(
            "SELECT e.id_equipo, e.nombre_equipo, e.nombre_salon,
                    COUNT(*) AS total_fallas
               FROM ticket t
               JOIN equipo e ON e.id_equipo = t.id_equipo
              WHERE t.estado_ticket <> 'Cancelado'
              GROUP BY e.id_equipo, e.nombre_equipo, e.nombre_salon
              ORDER BY total_fallas DESC, e.nombre_equipo
              LIMIT 5"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tiempoPromedioResolucion()
    {
        $stmt = $this->pdo->query(
            "SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, fecha_hora_alta, fecha_hora_cierre)) / 60, 1)
                    AS promedio_horas
               FROM ticket
              WHERE estado_ticket = 'Resuelto'
                AND fecha_hora_cierre IS NOT NULL"
        );
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila && $fila['promedio_horas'] !== null
            ? (float) $fila['promedio_horas'] : null;
    }
}
