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

    public function metricasExtendidas()
    {
        $porMes = [];
        foreach ($this->solicitudDAO->solicitudesPorMes() as $fila) {
            $porMes[$fila['periodo']] = (int) $fila['cantidad'];
        }

        $meses = [];
        $mesActual = new DateTimeImmutable('first day of this month');
        for ($i = 5; $i >= 0; $i--) {
            $periodo = $mesActual->modify('-' . $i . ' months')->format('Y-m');
            $meses[] = ['periodo' => $periodo,
                        'cantidad' => isset($porMes[$periodo]) ? $porMes[$periodo] : 0];
        }

        $equipos = [];
        foreach ($this->ticketDAO->equiposMasFallados() as $fila) {
            $fila['id_equipo'] = (int) $fila['id_equipo'];
            $fila['total_fallas'] = (int) $fila['total_fallas'];
            $equipos[] = $fila;
        }

        return [
            'equipos_mas_fallados' => $equipos,
            'tiempo_promedio_resolucion_horas' => $this->ticketDAO->tiempoPromedioResolucion(),
            'solicitudes_por_mes' => $meses,
        ];
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
