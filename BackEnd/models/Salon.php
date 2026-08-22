<?php

class Salon
{
    private $nombreSalon;
    private $descripcion;

    public function __construct($nombreSalon, $descripcion = null)
    {
        $this->nombreSalon = $nombreSalon;
        $this->descripcion = $descripcion;
    }

    public function getNombreSalon() { return $this->nombreSalon; }
    public function getDescripcion() { return $this->descripcion; }

    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
}
