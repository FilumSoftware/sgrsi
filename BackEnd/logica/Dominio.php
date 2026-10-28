<?php

class Dominio
{
    const ESTADOS_TICKET    = ['Pendiente', 'En proceso', 'Resuelto', 'Cancelado'];
    const ESTADOS_SOLICITUD = ['Pendiente', 'En proceso', 'Resuelta'];
    const PRIORIDADES       = ['Urgente', 'Prioritario', 'Normal'];
    const TURNOS            = ['Matutino', 'Vespertino', 'Nocturno'];
    const PLAZOS_TICKET_HORAS = ['Urgente' => 4, 'Prioritario' => 24, 'Normal' => 72];

    public static function ticketFueraDePlazo($ticket)
    {
        if (in_array($ticket['estado_ticket'], ['Resuelto', 'Cancelado'], true)) return false;
        $horas = self::PLAZOS_TICKET_HORAS[$ticket['prioridad']] ?? null;
        return $horas !== null && (int) $ticket['segundos_desde_alta'] > $horas * 3600;
    }

    public static function esEstadoTicket($valor)
    {
        return in_array($valor, self::ESTADOS_TICKET, true);
    }

    public static function esEstadoSolicitud($valor)
    {
        return in_array($valor, self::ESTADOS_SOLICITUD, true);
    }

    public static function esPrioridad($valor)
    {
        return in_array($valor, self::PRIORIDADES, true);
    }

    public static function esTurno($valor)
    {
        return in_array($valor, self::TURNOS, true);
    }

    public static function claseDelBadge($estado)
    {
        if ($estado === 'Cancelado') {
            return 'bg-secondary';
        }

        if ($estado === 'Resuelto' || $estado === 'Resuelta') {
            return 'bg-success';
        }

        if ($estado === 'En proceso') {
            return 'bg-info text-dark';
        }

        return 'bg-warning text-dark';
    }
}
