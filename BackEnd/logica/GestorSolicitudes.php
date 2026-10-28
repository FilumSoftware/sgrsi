<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/Dominio.php';
require_once __DIR__ . '/../dao/SolicitudDAO.php';
require_once __DIR__ . '/../dao/SalonDAO.php';
require_once __DIR__ . '/../dao/UsuarioDAO.php';

class GestorSolicitudes
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('solicitudes listar', function () { return (new SolicitudDAO())->obtenerTodos(); });
    }

    public function obtenerPorSolicitante($ci)
    {
        if (!ResultadoGestion::ciValida($ci)) return ResultadoGestion::fallo(400, 'Cédula inválida.');
        return ResultadoGestion::ejecutar('solicitudes listar por solicitante', function () use ($ci) { return (new SolicitudDAO())->obtenerPorSolicitante($ci); });
    }

    public function obtenerPorId($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Solicitud inválida.');
        return ResultadoGestion::ejecutar('solicitudes consultar', function () use ($id) { return (new SolicitudDAO())->obtenerPorId($id); });
    }

    public function obtenerSalones()
    {
        return ResultadoGestion::ejecutar('solicitudes salones', function () { return (new SalonDAO())->obtenerTodos(); });
    }

    public function obtenerTecnicos()
    {
        return ResultadoGestion::ejecutar('solicitudes técnicos', function () { return (new UsuarioDAO())->obtenerTecnicos(); });
    }

    public function actualizarDesdeFormulario($id, $datos, $puedeAtender)
    {
        if (!$puedeAtender) return ResultadoGestion::fallo(403, 'No tenés permiso para modificar solicitudes.');
        return ResultadoGestion::procesar('solicitudes formulario de edición', function () use ($id, $datos) {
            $actual = $this->obtenerPorId($id);
            if (!$actual['ok']) return $actual;
            if (!$actual['datos']) return ResultadoGestion::fallo(404, 'No existe esa solicitud.');
            $fila = $actual['datos'];
            $estado = ResultadoGestion::campo($datos, 'estado');
            $cierre = null;
            if ($estado === 'Resuelta') {
                $cierre = $fila['fecha_hora_cierre'] ?? null;
                if ($cierre === null) {
                    $cierre = date('Y-m-d H:i:s', max(time(), strtotime($fila['fecha_hora_alta'])));
                }
            }
            return $this->actualizar(new Solicitud(
                $fila['fecha_hora_alta'], $fila['ci_solicitante'],
                ResultadoGestion::campo($datos, 'salon'), ResultadoGestion::campo($datos, 'motivo'),
                ResultadoGestion::campo($datos, 'prioridad'), $estado,
                ResultadoGestion::campo($datos, 'responsable') ?: null, $cierre, $fila['id_solicitud']
            ));
        });
    }

    public function insertar(Solicitud $solicitud)
    {
        $error = $this->validar($solicitud);
        if ($error) return $error;
        return ResultadoGestion::ejecutar('solicitudes crear', function () use ($solicitud) { return (new SolicitudDAO())->insertar($solicitud); });
    }

    public function actualizar(Solicitud $solicitud)
    {
        $error = $this->validar($solicitud);
        if ($error) return $error;
        if (!ResultadoGestion::idValido($solicitud->getIdSolicitud())) return ResultadoGestion::fallo(400, 'Solicitud inválida.');
        return ResultadoGestion::ejecutar('solicitudes actualizar', function () use ($solicitud) { return (new SolicitudDAO())->actualizar($solicitud); });
    }

    public function eliminar($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Solicitud inválida.');
        return ResultadoGestion::ejecutar('solicitudes eliminar', function () use ($id) { return (new SolicitudDAO())->eliminar($id); });
    }

    private function validar(Solicitud $solicitud)
    {
        if (!ResultadoGestion::ciValida($solicitud->getCiSolicitante())) return ResultadoGestion::fallo(400, 'Solicitante inválido.');
        if (trim((string) $solicitud->getNombreSalon()) === '') return ResultadoGestion::fallo(400, 'Salón inválido.');
        if (strlen(trim((string) $solicitud->getDescripcion())) < 10) return ResultadoGestion::fallo(400, 'La descripción debe tener al menos 10 caracteres.');
        if (!Dominio::esPrioridad($solicitud->getPrioridad()) || !Dominio::esEstadoSolicitud($solicitud->getEstadoSolicitud())) return ResultadoGestion::fallo(400, 'Prioridad o estado inválido.');
        if ($solicitud->getEstadoSolicitud() === 'Resuelta' && !ResultadoGestion::ciValida($solicitud->getCiResponsable())) return ResultadoGestion::fallo(400, 'Una solicitud resuelta requiere responsable.');
        $referencias = ResultadoGestion::ejecutar('solicitudes validar referencias', function () use ($solicitud) {
            $salon = (new SalonDAO())->obtenerPorNombre($solicitud->getNombreSalon());
            $responsable = $solicitud->getCiResponsable() === null ? null : (new UsuarioDAO())->obtenerPorCi($solicitud->getCiResponsable());
            return [$salon, $responsable];
        });
        if (!$referencias['ok']) return $referencias;
        if (!$referencias['datos'][0]) return ResultadoGestion::fallo(400, 'Salón inválido.');
        if ($solicitud->getCiResponsable() !== null && (!$referencias['datos'][1] || !in_array($referencias['datos'][1]['tipo_de_usuario'], ['Asistente', 'Coordinador'], true))) return ResultadoGestion::fallo(400, 'Responsable inválido.');
        return null;
    }
}
