<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/Dominio.php';
require_once __DIR__ . '/../dao/UsoSalaDAO.php';
require_once __DIR__ . '/../dao/SalonDAO.php';
require_once __DIR__ . '/../dao/EquipoDAO.php';

class GestorUsoSala
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('uso de sala listar', function () { return (new UsoSalaDAO())->obtenerTodos(); });
    }

    public function obtenerPorSolicitante($ci)
    {
        if (!ResultadoGestion::ciValida($ci)) return ResultadoGestion::fallo(400, 'Cédula inválida.');
        return ResultadoGestion::ejecutar('uso de sala listar por solicitante', function () use ($ci) { return (new UsoSalaDAO())->obtenerPorSolicitante($ci); });
    }

    public function obtenerPorId($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Registro inválido.');
        return ResultadoGestion::ejecutar('uso de sala consultar', function () use ($id) { return (new UsoSalaDAO())->obtenerPorId($id); });
    }

    public function obtenerDetalle($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Registro inválido.');
        return ResultadoGestion::ejecutar('uso de sala detalle', function () use ($id) { return (new UsoSalaDAO())->obtenerDetalle($id); });
    }

    public function obtenerSalones()
    {
        return ResultadoGestion::ejecutar('uso de sala salones', function () { return (new SalonDAO())->obtenerTodos(); });
    }

    public function obtenerEquiposPorSalon($salon)
    {
        if (trim((string) $salon) === '') return ResultadoGestion::fallo(400, 'Salón inválido.');
        return ResultadoGestion::ejecutar('uso de sala equipos', function () use ($salon) { return (new EquipoDAO())->obtenerPorSalon($salon); });
    }

    public function existeSolapamiento($salon, $fecha, $inicio, $fin, $idExcluir = null)
    {
        if (trim((string) $salon) === '' || !$this->fechaValida($fecha) || !$this->horaValida($inicio) || !$this->horaValida($fin) || $fin <= $inicio || ($idExcluir !== null && !ResultadoGestion::idValido($idExcluir))) return ResultadoGestion::fallo(400, 'Horario inválido.');
        return ResultadoGestion::ejecutar('uso de sala disponibilidad', function () use ($salon, $fecha, $inicio, $fin, $idExcluir) { return (new UsoSalaDAO())->existeSolapamiento($salon, $fecha, $inicio, $fin, $idExcluir); });
    }

    public function crearDesdeFormulario($datos, $ci)
    {
        return ResultadoGestion::procesar('uso de sala formulario de alta', function () use ($datos, $ci) {
            $detalles = $this->detallesFormulario($datos);
            if (isset($detalles['ok'])) return $detalles;
            $uso = $this->usoFormulario($datos, $ci);
            return $this->insertar($uso, $detalles);
        });
    }

    public function actualizarDesdeFormulario($id, $datos, $ci, $puedeAtender)
    {
        return ResultadoGestion::procesar('uso de sala formulario de edición', function () use ($id, $datos, $ci, $puedeAtender) {
            $actual = $this->obtenerPorId($id);
            if (!$actual['ok']) return $actual;
            if (!$actual['datos']) return ResultadoGestion::fallo(404, 'No existe ese registro.');
            $fila = $actual['datos'];
            if (!$puedeAtender && $fila['ci_solicitante'] !== $ci) {
                return ResultadoGestion::fallo(403, 'No tenés permiso para modificar este registro.');
            }
            $detalles = $this->detallesFormulario($datos);
            if (isset($detalles['ok'])) return $detalles;
            return $this->actualizar($this->usoFormulario($datos, $fila['ci_solicitante'], $id), $detalles);
        });
    }

    public function eliminarDesdeFormulario($id, $esCoordinador)
    {
        if (!$esCoordinador) return ResultadoGestion::fallo(403, 'Solo el coordinador puede eliminar registros.');
        return $this->eliminar($id);
    }

    public function insertar(UsoSala $uso, $detalles)
    {
        $error = $this->validar($uso, $detalles);
        if ($error) return $error;
        $resultado = ResultadoGestion::ejecutar('uso de sala crear', function () use ($uso, $detalles) {
            $dao = new UsoSalaDAO();
            if ($dao->existeSolapamiento($uso->getNombreSalon(), $uso->getFecha(), $uso->getHoraInicio(), $uso->getHoraFin())) return false;
            return $dao->insertar($uso, $detalles);
        });
        if ($resultado['ok'] && $resultado['datos'] === false) return ResultadoGestion::fallo(409, 'La sala ya está ocupada en ese horario.');
        return $resultado;
    }

    public function actualizar(UsoSala $uso, $detalles)
    {
        $error = $this->validar($uso, $detalles);
        if ($error) return $error;
        if (!ResultadoGestion::idValido($uso->getIdUso())) return ResultadoGestion::fallo(400, 'Registro inválido.');
        $resultado = ResultadoGestion::ejecutar('uso de sala actualizar', function () use ($uso, $detalles) {
            $dao = new UsoSalaDAO();
            if ($dao->existeSolapamiento($uso->getNombreSalon(), $uso->getFecha(), $uso->getHoraInicio(), $uso->getHoraFin(), $uso->getIdUso())) return false;
            return $dao->actualizar($uso, $detalles);
        });
        if ($resultado['ok'] && $resultado['datos'] === false) return ResultadoGestion::fallo(409, 'La sala ya está ocupada en ese horario.');
        return $resultado;
    }

    public function eliminar($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Registro inválido.');
        return ResultadoGestion::ejecutar('uso de sala eliminar', function () use ($id) { return (new UsoSalaDAO())->eliminar($id); });
    }

    private function validar(UsoSala $uso, $detalles)
    {
        if (!$this->fechaValida($uso->getFecha()) || !$this->horaValida($uso->getHoraInicio()) || !$this->horaValida($uso->getHoraFin()) || !$uso->horarioEsValido()) return ResultadoGestion::fallo(400, 'Fecha u horario inválido.');
        if (!ResultadoGestion::ciValida($uso->getCiSolicitante()) || trim((string) $uso->getNombreSalon()) === '') return ResultadoGestion::fallo(400, 'Solicitante o salón inválido.');
        if (trim((string) $uso->getAsignatura()) === '' || strlen($uso->getAsignatura()) > 60 || trim((string) $uso->getGrupo()) === '' || strlen($uso->getGrupo()) > 10 || !Dominio::esTurno($uso->getTurno())) return ResultadoGestion::fallo(400, 'Datos de clase inválidos.');
        if (!is_array($detalles) || !$detalles) return ResultadoGestion::fallo(400, 'Asigná al menos una PC.');
        $ids = [];
        foreach ($detalles as $detalle) {
            if (!($detalle instanceof DetalleUso) || !ResultadoGestion::idValido($detalle->getIdEquipo()) || in_array($detalle->getIdEquipo(), $ids, true)) return ResultadoGestion::fallo(400, 'PC repetida o inválida.');
            $ids[] = $detalle->getIdEquipo();
        }
        $referencias = ResultadoGestion::ejecutar('uso de sala validar referencias', function () use ($uso) {
            $salon = (new SalonDAO())->obtenerPorNombre($uso->getNombreSalon());
            $equipos = (new EquipoDAO())->obtenerPorSalon($uso->getNombreSalon());
            return [$salon, $equipos];
        });
        if (!$referencias['ok']) return $referencias;
        if (!$referencias['datos'][0]) return ResultadoGestion::fallo(400, 'Salón inválido.');
        $equiposDelSalon = array_map(function ($equipo) { return (int) $equipo['id_equipo']; }, $referencias['datos'][1]);
        foreach ($ids as $id) {
            if (!in_array((int) $id, $equiposDelSalon, true)) return ResultadoGestion::fallo(400, 'Alguna PC no pertenece al salón seleccionado.');
        }
        return null;
    }

    private function usoFormulario($datos, $ci, $id = null)
    {
        $inicio = ResultadoGestion::campo($datos, 'entrada');
        $fin = ResultadoGestion::campo($datos, 'salida');
        return new UsoSala(
            ResultadoGestion::campo($datos, 'fecha'),
            preg_match('/^[0-9]{2}:[0-9]{2}$/', $inicio) ? $inicio . ':00' : $inicio,
            preg_match('/^[0-9]{2}:[0-9]{2}$/', $fin) ? $fin . ':00' : $fin,
            ResultadoGestion::campo($datos, 'salon'), $ci,
            ResultadoGestion::campo($datos, 'asignatura'), ResultadoGestion::campo($datos, 'grupo'),
            ResultadoGestion::campo($datos, 'turno'), $id
        );
    }

    private function detallesFormulario($datos)
    {
        $pcs = $datos['pc'] ?? [];
        $alumnos = $datos['alumno'] ?? [];
        if (!is_array($pcs) || !is_array($alumnos)) return ResultadoGestion::fallo(400, 'Lista de PC inválida.');
        $detalles = [];
        foreach ($pcs as $indice => $id) {
            if (!is_scalar($id)) return ResultadoGestion::fallo(400, 'PC inválida.');
            $id = trim((string) $id);
            if ($id === '') continue;
            if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'PC inválida.');
            $alumno = $alumnos[$indice] ?? '';
            if (!is_scalar($alumno)) return ResultadoGestion::fallo(400, 'Alumno inválido.');
            $alumno = trim((string) $alumno);
            $detalles[] = new DetalleUso((int) $id, $alumno !== '' ? $alumno : null);
        }
        return $detalles;
    }

    private function fechaValida($fecha)
    {
        $valor = DateTime::createFromFormat('!Y-m-d', (string) $fecha);
        return $valor && $valor->format('Y-m-d') === $fecha;
    }

    private function horaValida($hora)
    {
        return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', (string) $hora) === 1;
    }
}
