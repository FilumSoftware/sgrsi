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
        return ResultadoGestion::ejecutar('tickets listar', function () {
            return $this->agregarPlazos((new TicketDAO())->obtenerTodos());
        });
    }

    public function obtenerPorSolicitante($ci)
    {
        if (!ResultadoGestion::ciValida($ci)) return ResultadoGestion::fallo(400, 'Cédula inválida.');
        return ResultadoGestion::ejecutar('tickets listar por solicitante', function () use ($ci) {
            return $this->agregarPlazos((new TicketDAO())->obtenerPorSolicitante($ci));
        });
    }

    private function agregarPlazos($filas)
    {
        foreach ($filas as &$fila) {
            $fila['fuera_de_plazo'] = Dominio::ticketFueraDePlazo($fila);
        }
        unset($fila);
        return $filas;
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

    public function actualizarDesdeFormulario($id, $datos, $puedeAtender)
    {
        if (!$puedeAtender) return ResultadoGestion::fallo(403, 'No tenés permiso para modificar tickets.');
        return ResultadoGestion::procesar('tickets formulario de edición', function () use ($id, $datos) {
            $actual = $this->obtenerPorId($id);
            if (!$actual['ok']) return $actual;
            if (!$actual['datos']) return ResultadoGestion::fallo(404, 'No existe ese ticket.');
            $fila = $actual['datos'];
            if ($fila['estado_ticket'] === 'Cancelado') return ResultadoGestion::fallo(409, 'Un ticket cancelado no puede modificarse.');
            $estado = ResultadoGestion::campo($datos, 'estado');
            if ($estado === 'Cancelado') return ResultadoGestion::fallo(400, 'Usá la cancelación para ese estado.');
            $salon = ResultadoGestion::campo($datos, 'salon');
            $equipo = ResultadoGestion::campo($datos, 'equipo');
            $equipos = $this->obtenerEquiposPorSalon($salon);
            if (!$equipos['ok']) return $equipos;
            $ids = array_map(function ($item) { return (int) $item['id_equipo']; }, $equipos['datos']);
            if (!ResultadoGestion::idValido($equipo) || !in_array((int) $equipo, $ids, true)) {
                return ResultadoGestion::fallo(400, 'Elegí un equipo del salón seleccionado.');
            }
            $cierre = null;
            if ($estado === 'Resuelto') {
                $cierre = $fila['fecha_hora_cierre'] ?? null;
                if ($cierre === null) $cierre = date('Y-m-d H:i:s', max(time(), strtotime($fila['fecha_hora_alta'])));
            }
            $ticket = new Ticket(
                $fila['fecha_hora_alta'], $fila['ci_solicitante'], (int) $equipo,
                ResultadoGestion::campo($datos, 'defecto'), ResultadoGestion::campo($datos, 'motivo'),
                ResultadoGestion::campo($datos, 'prioridad'), $estado,
                ResultadoGestion::campo($datos, 'responsable') ?: null,
                ResultadoGestion::campo($datos, 'diagnostico') ?: null,
                ResultadoGestion::campo($datos, 'solucion') ?: null,
                $cierre, $fila['id_ticket']
            );
            $ticket->setDetalleDefecto(ResultadoGestion::campo($datos, 'detalle-defecto'));
            return $this->actualizar($ticket);
        });
    }

    public function insertar(Ticket $ticket)
    {
        $error = $this->validar($ticket);
        if ($error) return $error;
        if ($ticket->getEstadoTicket() !== 'Pendiente') return ResultadoGestion::fallo(400, 'Un ticket nuevo debe quedar pendiente.');
        $resultado = ResultadoGestion::ejecutar('tickets crear', function () use ($ticket) { return (new TicketDAO())->insertar($ticket); });
        if (!$resultado['ok']) return $resultado;
        if ($resultado['datos'] === null) return ResultadoGestion::fallo(400, 'Equipo inválido.');
        if ($resultado['datos'] === false) return ResultadoGestion::fallo(409, 'Ya hay un ticket abierto para esa PC.');
        return $resultado;
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
        $detalleDefecto = $ticket->getDetalleDefecto();
        if ($ticket->getTipoDeDefecto() === 'Otros' && ($detalleDefecto === null || mb_strlen($detalleDefecto) < 3 || mb_strlen($detalleDefecto) > 120)) return ResultadoGestion::fallo(400, 'Describí la falla en Otros (3 a 120 caracteres).');
        if ($ticket->getTipoDeDefecto() !== 'Otros' && $detalleDefecto !== null) return ResultadoGestion::fallo(400, 'El detalle de la falla solo corresponde a Otros.');
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
