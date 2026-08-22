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

    // El salon sale del equipo, no se guarda en ticket.
    private function consultaBase()
    {
        return 'SELECT t.id_ticket, t.fecha_hora_alta, t.tipo_de_defecto, t.descripcion,
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
        $stmt = $this->pdo->prepare(
            'INSERT INTO ticket (fecha_hora_alta, ci_solicitante, id_equipo, tipo_de_defecto,
                                 descripcion, prioridad, estado_ticket)
             VALUES (:alta, :solicitante, :equipo, :defecto, :descripcion, :prioridad, :estado)'
        );
        $stmt->execute([
            ':alta'        => $ticket->getFechaHoraAlta(),
            ':solicitante' => $ticket->getCiSolicitante(),
            ':equipo'      => $ticket->getIdEquipo(),
            ':defecto'     => $ticket->getTipoDeDefecto(),
            ':descripcion' => $ticket->getDescripcion(),
            ':prioridad'   => $ticket->getPrioridad(),
            ':estado'      => $ticket->getEstadoTicket()
        ]);

        return $this->pdo->lastInsertId();
    }

    public function actualizar(Ticket $ticket)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE ticket
                SET id_equipo = :equipo,
                    tipo_de_defecto = :defecto,
                    descripcion = :descripcion,
                    prioridad = :prioridad,
                    estado_ticket = :estado,
                    ci_responsable = :responsable,
                    diagnostico = :diagnostico,
                    solucion = :solucion,
                    fecha_hora_cierre = :cierre
              WHERE id_ticket = :id'
        );

        return $stmt->execute([
            ':equipo'      => $ticket->getIdEquipo(),
            ':defecto'     => $ticket->getTipoDeDefecto(),
            ':descripcion' => $ticket->getDescripcion(),
            ':prioridad'   => $ticket->getPrioridad(),
            ':estado'      => $ticket->getEstadoTicket(),
            ':responsable' => $ticket->getCiResponsable(),
            ':diagnostico' => $ticket->getDiagnostico(),
            ':solucion'    => $ticket->getSolucion(),
            ':cierre'      => $ticket->getFechaHoraCierre(),
            ':id'          => $ticket->getIdTicket()
        ]);
    }

    public function eliminar($idTicket)
    {
        $stmt = $this->pdo->prepare('DELETE FROM ticket WHERE id_ticket = :id');

        return $stmt->execute([':id' => $idTicket]);
    }

    // Para el dashboard.
    public function contarPorEstado()
    {
        $stmt = $this->pdo->query(
            'SELECT estado_ticket, COUNT(*) AS cantidad
               FROM ticket
              GROUP BY estado_ticket'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
