<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/../dao/TipoIncidenciaDAO.php';

class GestorTiposIncidencia
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('tipos de incidencia listar', function () {
            return (new TipoIncidenciaDAO())->obtenerTodos();
        });
    }

    public function obtenerActivos()
    {
        return ResultadoGestion::ejecutar('tipos de incidencia activos', function () {
            return (new TipoIncidenciaDAO())->obtenerActivos();
        });
    }

    public function insertar($nombre)
    {
        $nombre = trim((string) $nombre);
        if (!$this->nombreValido($nombre)) return ResultadoGestion::fallo(400, 'El nombre debe tener entre 3 y 80 caracteres.');
        return ResultadoGestion::procesar('tipos de incidencia crear', function () use ($nombre) {
            $dao = new TipoIncidenciaDAO();
            if ($dao->obtenerPorNombre($nombre)) return ResultadoGestion::fallo(409, 'Ya existe ese tipo de incidencia.');
            try {
                $dao->insertar($nombre);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') return ResultadoGestion::fallo(409, 'Ya existe ese tipo de incidencia.');
                throw $e;
            }
            return ResultadoGestion::ok(['nombre' => $nombre, 'estado' => 'Activo'], 201);
        });
    }

    public function actualizar($actual, $nuevo, $estado)
    {
        $actual = trim((string) $actual);
        $nuevo = trim((string) $nuevo);
        if (!$this->nombreValido($actual) || !$this->nombreValido($nuevo) || !in_array($estado, ['Activo', 'Inactivo'], true)) {
            return ResultadoGestion::fallo(400, 'Nombre o estado inválido.');
        }
        if ($actual === 'Otros' && ($nuevo !== 'Otros' || $estado !== 'Activo')) {
            return ResultadoGestion::fallo(409, 'La opción Otros debe permanecer activa.');
        }
        return ResultadoGestion::procesar('tipos de incidencia actualizar', function () use ($actual, $nuevo, $estado) {
            $dao = new TipoIncidenciaDAO();
            $fila = $dao->obtenerPorNombre($actual);
            if (!$fila) return ResultadoGestion::fallo(404, 'No existe ese tipo de incidencia.');
            if ($actual !== $nuevo && $dao->obtenerPorNombre($nuevo)) {
                return ResultadoGestion::fallo(409, 'Ya existe ese tipo de incidencia.');
            }
            try {
                $dao->actualizar($actual, $nuevo, $estado);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') return ResultadoGestion::fallo(409, 'Ya existe ese tipo de incidencia.');
                throw $e;
            }
            return ResultadoGestion::ok(['nombre' => $nuevo, 'estado' => $estado]);
        });
    }

    public function desactivar($nombre)
    {
        return $this->actualizar($nombre, $nombre, 'Inactivo');
    }

    private function nombreValido($nombre)
    {
        $largo = mb_strlen($nombre);
        return $largo >= 3 && $largo <= 80;
    }
}
