<?php

require_once __DIR__ . '/ResultadoGestion.php';
require_once __DIR__ . '/../dao/EquipoDAO.php';
require_once __DIR__ . '/../dao/SalonDAO.php';

class GestorInventario
{
    public function obtenerTodos()
    {
        return ResultadoGestion::ejecutar('inventario listar', function () { return (new EquipoDAO())->obtenerTodos(); });
    }

    public function obtenerPorId($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Equipo inválido.');
        return ResultadoGestion::ejecutar('inventario consultar', function () use ($id) { return (new EquipoDAO())->obtenerPorId($id); });
    }

    public function obtenerSalones()
    {
        return ResultadoGestion::ejecutar('inventario salones', function () { return (new SalonDAO())->obtenerTodos(); });
    }

    public function crearDesdeFormulario($datos)
    {
        return ResultadoGestion::procesar('inventario formulario de alta', function () use ($datos) {
            $nombre = ResultadoGestion::campo($datos, 'nombre');
            $salon = ResultadoGestion::campo($datos, 'salon');
            $extras = ResultadoGestion::campo($datos, 'extras');
            return $this->insertar(new Equipo($nombre, $extras !== '' ? $extras : null, $salon));
        });
    }

    public function actualizarDesdeFormulario($id, $datos)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Equipo inválido.');
        return ResultadoGestion::procesar('inventario formulario de edición', function () use ($id, $datos) {
            $nombre = ResultadoGestion::campo($datos, 'nombre');
            $salon = ResultadoGestion::campo($datos, 'salon');
            $extras = ResultadoGestion::campo($datos, 'extras');
            $estado = ResultadoGestion::campo($datos, 'estado');
            return $this->actualizar(new Equipo($nombre, $extras !== '' ? $extras : null, $salon, $estado, $id));
        });
    }

    public function insertar(Equipo $equipo)
    {
        $error = $this->validar($equipo);
        if ($error) return $error;
        return ResultadoGestion::ejecutar('inventario crear', function () use ($equipo) { return (new EquipoDAO())->insertar($equipo); });
    }

    public function actualizar(Equipo $equipo)
    {
        $error = $this->validar($equipo);
        if ($error) return $error;
        if (!ResultadoGestion::idValido($equipo->getIdEquipo())) return ResultadoGestion::fallo(400, 'Equipo inválido.');
        return ResultadoGestion::ejecutar('inventario actualizar', function () use ($equipo) { return (new EquipoDAO())->actualizar($equipo); });
    }

    public function darDeBaja($id)
    {
        if (!ResultadoGestion::idValido($id)) return ResultadoGestion::fallo(400, 'Equipo inválido.');
        return ResultadoGestion::ejecutar('inventario dar de baja', function () use ($id) {
            $dao = new EquipoDAO();
            $fila = $dao->obtenerPorId($id);
            if (!$fila) return false;
            $equipo = new Equipo($fila['nombre_equipo'], $fila['descripcion'], $fila['nombre_salon'], 'De baja', $id);
            return $dao->actualizar($equipo);
        });
    }

    private function validar(Equipo $equipo)
    {
        if (trim((string) $equipo->getNombreEquipo()) === '' || strlen($equipo->getNombreEquipo()) > 100) return ResultadoGestion::fallo(400, 'Nombre de equipo inválido.');
        if (!in_array($equipo->getEstadoEquipo(), ['Operativo', 'En reparación', 'En trámite de baja', 'De baja'], true)) return ResultadoGestion::fallo(400, 'Estado de equipo inválido.');
        if (trim((string) $equipo->getNombreSalon()) === '') return ResultadoGestion::fallo(400, 'Salón inválido.');
        $salon = ResultadoGestion::ejecutar('inventario validar salón', function () use ($equipo) { return (new SalonDAO())->obtenerPorNombre($equipo->getNombreSalon()); });
        if (!$salon['ok']) return $salon;
        if (!$salon['datos']) return ResultadoGestion::fallo(400, 'Salón inválido.');
        return null;
    }
}
