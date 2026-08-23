<?php

require_once __DIR__ . '/../dao/TicketDAO.php';
require_once __DIR__ . '/../dao/SolicitudDAO.php';
require_once __DIR__ . '/../dao/EquipoDAO.php';
require_once __DIR__ . '/../dao/UsoSalaDAO.php';

class Resumen
{
    private $ticketDAO;
    private $solicitudDAO;
    private $equipoDAO;
    private $usoSalaDAO;

    public function __construct()
    {
        $this->ticketDAO    = new TicketDAO();
        $this->solicitudDAO = new SolicitudDAO();
        $this->equipoDAO    = new EquipoDAO();
        $this->usoSalaDAO   = new UsoSalaDAO();
    }

    public function ticketsAbiertos()
    {
        return $this->sumar(
            $this->ticketDAO->contarPorEstado(), 'estado_ticket', ['Pendiente', 'En proceso']
        );
    }

    public function solicitudesPendientes()
    {
        return $this->sumar(
            $this->solicitudDAO->contarPorEstado(), 'estado_solicitud', ['Pendiente']
        );
    }

    public function equiposOperativos()
    {
        return $this->sumar(
            $this->equipoDAO->contarPorEstado(), 'estado_equipo', ['Operativo']
        );
    }

    public function registrosDeLaboratorio()
    {
        return $this->usoSalaDAO->contar();
    }

    public function ultimosTickets($cuantos, $ci = null)
    {
        $tickets = $ci === null
            ? $this->ticketDAO->obtenerTodos()
            : $this->ticketDAO->obtenerPorSolicitante($ci);

        $ultimos = [];

        foreach ($tickets as $ticket) {
            if (count($ultimos) >= $cuantos) {
                break;
            }

            $ultimos[] = $ticket;
        }

        return $ultimos;
    }

    private function sumar($filas, $columna, $estados)
    {
        $total = 0;

        foreach ($filas as $fila) {
            if (in_array($fila[$columna], $estados, true)) {
                $total = $total + (int) $fila['cantidad'];
            }
        }

        return $total;
    }
}
