<?php

class Prestamo
{
    private $idEquipo;
    private $ciSolicitante;
    private $ciEntrega;
    private $fechaDevolucionPrevista;
    private $observaciones;

    public function __construct($idEquipo, $ciSolicitante, $ciEntrega, $fechaDevolucionPrevista = null, $observaciones = null)
    {
        $this->idEquipo = $idEquipo;
        $this->ciSolicitante = $ciSolicitante;
        $this->ciEntrega = $ciEntrega;
        $this->fechaDevolucionPrevista = $fechaDevolucionPrevista;
        $this->observaciones = $observaciones;
    }

    public function getIdEquipo() { return $this->idEquipo; }
    public function getCiSolicitante() { return $this->ciSolicitante; }
    public function getCiEntrega() { return $this->ciEntrega; }
    public function getFechaDevolucionPrevista() { return $this->fechaDevolucionPrevista; }
    public function getObservaciones() { return $this->observaciones; }
}
