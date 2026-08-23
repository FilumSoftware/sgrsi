<?php

class Ticket
{
    private $idTicket;
    private $fechaHoraAlta;
    private $ciSolicitante;
    private $idEquipo;
    private $tipoDeDefecto;
    private $descripcion;
    private $prioridad;
    private $estadoTicket;
    private $ciResponsable;
    private $diagnostico;
    private $solucion;
    private $fechaHoraCierre;

    public function __construct($fechaHoraAlta, $ciSolicitante, $idEquipo, $tipoDeDefecto,
                                $descripcion = null, $prioridad = 'Normal', $estadoTicket = 'Pendiente',
                                $ciResponsable = null, $diagnostico = null, $solucion = null,
                                $fechaHoraCierre = null, $idTicket = null)
    {
        $this->fechaHoraAlta   = $fechaHoraAlta;
        $this->ciSolicitante   = $ciSolicitante;
        $this->idEquipo        = $idEquipo;
        $this->tipoDeDefecto   = $tipoDeDefecto;
        $this->descripcion     = $descripcion;
        $this->prioridad       = $prioridad;
        $this->estadoTicket    = $estadoTicket;
        $this->ciResponsable   = $ciResponsable;
        $this->diagnostico     = $diagnostico;
        $this->solucion        = $solucion;
        $this->fechaHoraCierre = $fechaHoraCierre;
        $this->idTicket        = $idTicket;
    }

    public function getIdTicket()        { return $this->idTicket; }
    public function getFechaHoraAlta()   { return $this->fechaHoraAlta; }
    public function getCiSolicitante()   { return $this->ciSolicitante; }
    public function getIdEquipo()        { return $this->idEquipo; }
    public function getTipoDeDefecto()   { return $this->tipoDeDefecto; }
    public function getDescripcion()     { return $this->descripcion; }
    public function getPrioridad()       { return $this->prioridad; }
    public function getEstadoTicket()    { return $this->estadoTicket; }
    public function getCiResponsable()   { return $this->ciResponsable; }
    public function getDiagnostico()     { return $this->diagnostico; }
    public function getSolucion()        { return $this->solucion; }
    public function getFechaHoraCierre() { return $this->fechaHoraCierre; }

    public function setTipoDeDefecto($tipo)     { $this->tipoDeDefecto = $tipo; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setPrioridad($prioridad)     { $this->prioridad = $prioridad; }
    public function setEstadoTicket($estado)     { $this->estadoTicket = $estado; }
    public function setCiResponsable($ci)        { $this->ciResponsable = $ci; }
    public function setDiagnostico($diagnostico) { $this->diagnostico = $diagnostico; }
    public function setSolucion($solucion)       { $this->solucion = $solucion; }
    public function setFechaHoraCierre($fecha)   { $this->fechaHoraCierre = $fecha; }

    public function estaResuelto()
    {
        return $this->estadoTicket == 'Resuelto';
    }

    public function tieneResponsable()
    {
        return $this->ciResponsable != null;
    }

    // La base exige responsable y fecha de cierre para marcarlo Resuelto.
    public function sePuedeCerrar()
    {
        return $this->tieneResponsable() && $this->fechaHoraCierre != null;
    }
}
