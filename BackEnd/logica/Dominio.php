<?php

class Dominio
{
    const ESTADOS_TICKET   = ['Pendiente', 'En proceso', 'Resuelto'];
    const PRIORIDADES      = ['Urgente', 'Prioritario', 'Normal'];
    const TIPOS_DE_DEFECTO = ['No enciende', 'Sin imagen', 'Sin red', 'Sin audio',
                              'Periférico dañado', 'Software', 'Otros'];

    public static function esEstadoTicket($valor)
    {
        return in_array($valor, self::ESTADOS_TICKET, true);
    }

    public static function esPrioridad($valor)
    {
        return in_array($valor, self::PRIORIDADES, true);
    }

    public static function esTipoDeDefecto($valor)
    {
        return in_array($valor, self::TIPOS_DE_DEFECTO, true);
    }

    public static function claseDelBadge($estado)
    {
        if ($estado === 'Resuelto') {
            return 'bg-success';
        }

        if ($estado === 'En proceso') {
            return 'bg-info text-dark';
        }

        return 'bg-warning text-dark';
    }
}
