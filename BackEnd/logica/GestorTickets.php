<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/Dominio.php';
require_once __DIR__ . '/../dao/TicketDAO.php';
require_once __DIR__ . '/../dao/SalonDAO.php';
require_once __DIR__ . '/../dao/EquipoDAO.php';
require_once __DIR__ . '/../dao/UsuarioDAO.php';

class GestorTickets
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('tickets listar', function () { return (new TicketDAO())->obtenerTodos(); });
    }

    public function obtenerPorSolicitante($ci)
    {
        if (!ResultadoGestion::ciValida($ci)) return ResultadoGestion::fallo(400, 'Cédula inválida.');
        return ResultadoGestion::ejecutar('tickets listar por solicitante', function () use ($ci) { return (new TicketDAO())->obtenerPorSolicitante($ci); });
    }

    public function obtenerPorId($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Ticket inválido.');
        return ResultadoGestion::ejecutar('tickets consultar', function () use ($id) { return (new TicketDAO())->obtenerPorId($id); });
    }

    public function obtenerSalones()
    {
        return ResultadoGestion::ejecutar('tickets salones', function () { return (new SalonDAO())->obtenerTodos(); });
    }

    public function obtenerEquiposPorSalon($salon)
    {
        if (trim((string) $salon) === '') return ResultadoGestion::fallo(400, 'Salón inválido.');
        return ResultadoGestion::ejecutar('tickets equipos', function () use ($salon) { return (new EquipoDAO())->obtenerPorSalon($salon); });
    }

    public function obtenerTecnicos()
    {
        return ResultadoGestion::ejecutar('tickets técnicos', function () { return (new UsuarioDAO())->obtenerTecnicos(); });
    }

    public function insertar(Ticket $ticket)
    {
        $error = $this->validar($ticket);
        if ($error) return $error;
        if ($ticket->getEstadoTicket() !== 'Pendiente') return ResultadoGestion::fallo(400, 'Un ticket nuevo debe quedar pendiente.');
        return ResultadoGestion::ejecutar('tickets crear', function () use ($ticket) { return (new TicketDAO())->insertar($ticket); });
    }

    public function actualizar(Ticket $ticket)
    {
        $error = $this->validar($ticket);
        if ($error) return $error;
        if (!ResultadoGestion::idValido($ticket->getIdTicket()) || $ticket->getEstadoTicket() === 'Cancelado') return ResultadoGestion::fallo(400, 'Ticket o estado inválido.');
        $resultado = ResultadoGestion::ejecutar('tickets actualizar', function () use ($ticket) { return (new TicketDAO())->actualizar($ticket); });
        if ($resultado['ok'] && !$resultado['datos']) return ResultadoGestion::fallo(409, 'El ticket fue cancelado o modificado.');
        return $resultado;
    }

    public function eliminar($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Ticket inválido.');
        return ResultadoGestion::ejecutar('tickets eliminar', function () use ($id) { return (new TicketDAO())->eliminar($id); });
    }

    public function cancelar($id, $ci)
    {
        if (!ResultadoGestion::idValido($id) || !ResultadoGestion::ciValida($ci)) return ResultadoGestion::fallo(400, 'Identificador de ticket inválido.');
        try {
            $dao = new TicketDAO();
            if ($dao->cancelarPendiente($id, $ci)) return ResultadoGestion::ok(['id_ticket' => (int) $id, 'estado_ticket' => 'Cancelado'], 200, 'El ticket se canceló.');
            $ticket = $dao->obtenerPorId($id);
            if (!$ticket) return ResultadoGestion::fallo(404, 'No existe ese ticket.');
            if ($ticket['ci_solicitante'] !== $ci) return ResultadoGestion::fallo(403, 'No podés cancelar un ticket de otra persona.');
            return ResultadoGestion::fallo(409, 'El ticket ya fue asignado o dejó de estar pendiente.');
        } catch (Throwable $e) {
            error_log('SGRSI tickets cancelar: ' . $e->getMessage());
            return ResultadoGestion::fallo(500, 'No se pudo cancelar el ticket. Intentá de nuevo en unos minutos.');
        }
    }

    private function validar(Ticket $ticket)
    {
        if (!ResultadoGestion::ciValida($ticket->getCiSolicitante()) || !ResultadoGestion::idValido($ticket->getIdEquipo())) return ResultadoGestion::fallo(400, 'Solicitante o equipo inválido.');
        if (!Dominio::esTipoDeDefecto($ticket->getTipoDeDefecto()) || !Dominio::esPrioridad($ticket->getPrioridad()) || !Dominio::esEstadoTicket($ticket->getEstadoTicket())) return ResultadoGestion::fallo(400, 'Tipo, prioridad o estado inválido.');
        if (strlen(trim((string) $ticket->getDescripcion())) < 10) return ResultadoGestion::fallo(400, 'La descripción debe tener al menos 10 caracteres.');
        if ($ticket->getEstadoTicket() === 'Resuelto' && !ResultadoGestion::ciValida($ticket->getCiResponsable())) return ResultadoGestion::fallo(400, 'Un ticket resuelto requiere responsable.');
        $referencias = ResultadoGestion::ejecutar('tickets validar referencias', function () use ($ticket) {
            $equipo = (new EquipoDAO())->obtenerPorId($ticket->getIdEquipo());
            $responsable = $ticket->getCiResponsable() === null ? null : (new UsuarioDAO())->obtenerPorCi($ticket->getCiResponsable());
            return [$equipo, $responsable];
        });
        if (!$referencias['ok']) return $referencias;
        if (!$referencias['datos'][0]) return ResultadoGestion::fallo(400, 'Equipo inválido.');
        if ($ticket->getCiResponsable() !== null && (!$referencias['datos'][1] || !in_array($referencias['datos'][1]['tipo_de_usuario'], ['Asistente', 'Coordinador'], true))) return ResultadoGestion::fallo(400, 'Responsable inválido.');
        return null;
    }
}
