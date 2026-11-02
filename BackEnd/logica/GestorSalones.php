<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/../dao/SalonDAO.php';

class GestorSalones
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('salones listar', function () {
            return (new SalonDAO())->obtenerTodos();
        });
    }

    public function obtenerPorNombre($nombre)
    {
        $nombre = trim((string) $nombre);
        if (!$this->nombreValido($nombre)) return ResultadoGestion::fallo(400, 'Nombre de salón inválido.');
        return ResultadoGestion::ejecutar('salones consultar', function () use ($nombre) {
            return (new SalonDAO())->obtenerPorNombre($nombre);
        });
    }

    public function insertar($nombre, $descripcion)
    {
        $nombre = trim((string) $nombre);
        $descripcion = trim((string) $descripcion);
        if (!$this->nombreValido($nombre)) return ResultadoGestion::fallo(400, 'El nombre del salón debe tener entre 2 y 30 caracteres.');
        if (!$this->descripcionValida($descripcion)) return ResultadoGestion::fallo(400, 'La descripción del salón admite hasta 500 caracteres.');

        return ResultadoGestion::procesar('salones crear', function () use ($nombre, $descripcion) {
            $dao = new SalonDAO();
            if ($dao->obtenerPorNombre($nombre)) return ResultadoGestion::fallo(409, 'Ya existe un salón con ese nombre.');
            try {
                $dao->insertar(new Salon($nombre, $descripcion !== '' ? $descripcion : null));
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') return ResultadoGestion::fallo(409, 'Ya existe un salón con ese nombre.');
                throw $e;
            }
            return ResultadoGestion::ok($this->fila($nombre, $descripcion), 201);
        });
    }

    public function actualizar($nombre, $descripcion)
    {
        $nombre = trim((string) $nombre);
        $descripcion = trim((string) $descripcion);
        if (!$this->nombreValido($nombre)) return ResultadoGestion::fallo(400, 'Nombre de salón inválido.');
        if (!$this->descripcionValida($descripcion)) return ResultadoGestion::fallo(400, 'La descripción del salón admite hasta 500 caracteres.');

        return ResultadoGestion::procesar('salones actualizar', function () use ($nombre, $descripcion) {
            $dao = new SalonDAO();
            if (!$dao->obtenerPorNombre($nombre)) return ResultadoGestion::fallo(404, 'No existe ese salón.');
            $dao->actualizar(new Salon($nombre, $descripcion !== '' ? $descripcion : null));
            return ResultadoGestion::ok($this->fila($nombre, $descripcion));
        });
    }

    public function eliminar($nombre)
    {
        $nombre = trim((string) $nombre);
        if (!$this->nombreValido($nombre)) return ResultadoGestion::fallo(400, 'Nombre de salón inválido.');

        return ResultadoGestion::procesar('salones eliminar', function () use ($nombre) {
            $dao = new SalonDAO();
            if (!$dao->obtenerPorNombre($nombre)) return ResultadoGestion::fallo(404, 'No existe ese salón.');
            try {
                $dao->eliminar($nombre);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    return ResultadoGestion::fallo(409, 'El salón tiene equipos, solicitudes o registros de uso asociados.');
                }
                throw $e;
            }
            return ResultadoGestion::ok(['nombre_salon' => $nombre]);
        });
    }

    private function fila($nombre, $descripcion)
    {
        return ['nombre_salon' => $nombre, 'descripcion' => $descripcion !== '' ? $descripcion : null];
    }

    private function nombreValido($nombre)
    {
        $largo = mb_strlen($nombre);
        return $largo >= 2 && $largo <= 30;
    }

    private function descripcionValida($descripcion)
    {
        return mb_strlen($descripcion) <= 500;
    }
}
