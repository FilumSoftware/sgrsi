<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/../dao/PrestamoDAO.php';
require_once __DIR__ . '/../dao/UsuarioDAO.php';

class GestorPrestamos
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('préstamos listar', function () { return (new PrestamoDAO())->obtenerTodos(); });
    }

    public function obtenerPorSolicitante($ci)
    {
        if (!ResultadoGestion::ciValida($ci)) return ResultadoGestion::fallo(400, 'Cédula inválida.');
        return ResultadoGestion::ejecutar('préstamos listar por solicitante', function () use ($ci) {
            return (new PrestamoDAO())->obtenerPorSolicitante($ci);
        });
    }

    public function obtenerPorId($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Préstamo inválido.');
        return ResultadoGestion::ejecutar('préstamos consultar', function () use ($id) { return (new PrestamoDAO())->obtenerPorId($id); });
    }

    public function obtenerCatalogos()
    {
        return ResultadoGestion::ejecutar('préstamos catálogos', function () {
            $usuarios = array_values(array_filter((new UsuarioDAO())->obtenerTodos(), function ($usuario) {
                return $usuario['estado_cuenta'] === 'Activa';
            }));
            return [
                'equipos' => (new PrestamoDAO())->obtenerEquiposDisponibles(),
                'usuarios' => $usuarios
            ];
        });
    }

    public function registrar(Prestamo $prestamo)
    {
        if (!ResultadoGestion::idValido($prestamo->getIdEquipo())
            || !ResultadoGestion::ciValida($prestamo->getCiSolicitante())
            || !ResultadoGestion::ciValida($prestamo->getCiEntrega())) {
            return ResultadoGestion::fallo(400, 'Equipo o usuario inválido.');
        }
        $error = $this->validarCampos($prestamo->getFechaDevolucionPrevista(), $prestamo->getObservaciones());
        if ($error) return $error;
        if ($prestamo->getFechaDevolucionPrevista() !== null
            && $prestamo->getFechaDevolucionPrevista() < date('Y-m-d')) {
            return ResultadoGestion::fallo(400, 'La fecha prevista no puede ser anterior a hoy.');
        }
        $usuario = ResultadoGestion::ejecutar('préstamos validar solicitante', function () use ($prestamo) {
            return (new UsuarioDAO())->obtenerPorCi($prestamo->getCiSolicitante());
        });
        if (!$usuario['ok']) return $usuario;
        if (!$usuario['datos'] || $usuario['datos']['estado_cuenta'] !== 'Activa') {
            return ResultadoGestion::fallo(400, 'Elegí un usuario activo.');
        }
        $resultado = ResultadoGestion::ejecutar('préstamos crear', function () use ($prestamo) {
            return (new PrestamoDAO())->registrar($prestamo);
        });
        if ($resultado['ok'] && $resultado['datos'] === false) {
            return ResultadoGestion::fallo(409, 'El equipo no está disponible para préstamo.');
        }
        return $resultado;
    }

    public function actualizar($id, $fechaPrevista, $observaciones)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Préstamo inválido.');
        $error = $this->validarCampos($fechaPrevista, $observaciones);
        if ($error) return $error;
        $actual = $this->obtenerPorId($id);
        if (!$actual['ok']) return $actual;
        if (!$actual['datos']) return ResultadoGestion::fallo(404, 'No existe ese préstamo.');
        if ($actual['datos']['estado_prestamo'] !== 'Activo') return ResultadoGestion::fallo(409, 'El préstamo ya está cerrado.');
        if ($fechaPrevista !== null && $fechaPrevista < substr($actual['datos']['fecha_hora_prestamo'], 0, 10)) {
            return ResultadoGestion::fallo(400, 'La fecha prevista es anterior al préstamo.');
        }
        if ($actual['datos']['fecha_devolucion_prevista'] === $fechaPrevista
            && $actual['datos']['observaciones'] === $observaciones) {
            return ResultadoGestion::ok(['id_prestamo' => (int) $id], 200, 'Préstamo actualizado.');
        }
        $resultado = ResultadoGestion::ejecutar('préstamos actualizar', function () use ($id, $fechaPrevista, $observaciones) {
            return (new PrestamoDAO())->actualizar($id, $fechaPrevista, $observaciones);
        });
        if ($resultado['ok'] && !$resultado['datos']) return ResultadoGestion::fallo(409, 'El préstamo cambió mientras lo editabas.');
        if ($resultado['ok']) return ResultadoGestion::ok(['id_prestamo' => (int) $id], 200, 'Préstamo actualizado.');
        return $resultado;
    }

    public function devolver($id, $ci)
    {
        return $this->cerrar($id, $ci, 'devolver');
    }

    public function anular($id, $ci)
    {
        return $this->cerrar($id, $ci, 'anular');
    }

    private function cerrar($id, $ci, $accion)
    {
        if (!ResultadoGestion::idValido($id) || !ResultadoGestion::ciValida($ci)) {
            return ResultadoGestion::fallo(400, 'Préstamo o usuario inválido.');
        }
        $actual = $this->obtenerPorId($id);
        if (!$actual['ok']) return $actual;
        if (!$actual['datos']) return ResultadoGestion::fallo(404, 'No existe ese préstamo.');
        if ($actual['datos']['estado_prestamo'] !== 'Activo') return ResultadoGestion::fallo(409, 'El préstamo ya está cerrado.');
        $resultado = ResultadoGestion::ejecutar('préstamos ' . $accion, function () use ($id, $ci, $accion) {
            $dao = new PrestamoDAO();
            return $accion === 'devolver' ? $dao->devolver($id, $ci) : $dao->anular($id, $ci);
        });
        if ($resultado['ok'] && !$resultado['datos']) return ResultadoGestion::fallo(409, 'El préstamo cambió mientras lo editabas.');
        if ($resultado['ok']) {
            $mensaje = $accion === 'devolver' ? 'Devolución registrada.' : 'Préstamo anulado.';
            return ResultadoGestion::ok(['id_prestamo' => (int) $id], 200, $mensaje);
        }
        return $resultado;
    }

    private function validarCampos($fechaPrevista, $observaciones)
    {
        if ($fechaPrevista !== null) {
            $fecha = DateTime::createFromFormat('!Y-m-d', (string) $fechaPrevista);
            if (!$fecha || $fecha->format('Y-m-d') !== $fechaPrevista) {
                return ResultadoGestion::fallo(400, 'Fecha prevista inválida.');
            }
        }
        if ($observaciones !== null && strlen((string) $observaciones) > 500) {
            return ResultadoGestion::fallo(400, 'Las observaciones no pueden superar los 500 caracteres.');
        }
        return null;
    }
}
