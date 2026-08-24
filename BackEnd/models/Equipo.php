<?php

class Equipo
{
    private $idEquipo;
    private $nombreEquipo;
    private $descripcion;
    private $nombreSalon;
    private $estadoEquipo;

    public function __construct($nombreEquipo, $descripcion, $nombreSalon, $estadoEquipo = 'Operativo', $idEquipo = null)
    {
        $this->nombreEquipo = $nombreEquipo;
        $this->descripcion  = $descripcion;
        $this->nombreSalon  = $nombreSalon;
        $this->estadoEquipo = $estadoEquipo;
        $this->idEquipo     = $idEquipo;
    }

    public function getIdEquipo()     { return $this->idEquipo; }
    public function getNombreEquipo() { return $this->nombreEquipo; }
    public function getDescripcion()  { return $this->descripcion; }
    public function getNombreSalon()  { return $this->nombreSalon; }
    public function getEstadoEquipo() { return $this->estadoEquipo; }

    public function setNombreEquipo($nombreEquipo) { $this->nombreEquipo = $nombreEquipo; }
    public function setDescripcion($descripcion)   { $this->descripcion = $descripcion; }
    public function setNombreSalon($nombreSalon)   { $this->nombreSalon = $nombreSalon; }
    public function setEstadoEquipo($estado)       { $this->estadoEquipo = $estado; }

    public function estaOperativo()
    {
        return $this->estadoEquipo == 'Operativo';
    }

    public function estaDeBaja()
    {
        return $this->estadoEquipo == 'De baja' || $this->estadoEquipo == 'En trámite de baja';
    }
}
