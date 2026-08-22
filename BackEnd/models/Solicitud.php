<?php

class Solicitud
{
    private $idSolicitud;
    private $fechaHoraAlta;
    private $ciSolicitante;
    private $nombreSalon;
    private $descripcion;
    private $prioridad;
    private $estadoSolicitud;
    private $ciResponsable;
    private $fechaHoraCierre;

    public function __construct($fechaHoraAlta, $ciSolicitante, $nombreSalon, $descripcion,
                                $prioridad = 'Normal', $estadoSolicitud = 'Pendiente',
                                $ciResponsable = null, $fechaHoraCierre = null, $idSolicitud = null)
    {
        $this->fechaHoraAlta   = $fechaHoraAlta;
        $this->ciSolicitante   = $ciSolicitante;
        $this->nombreSalon     = $nombreSalon;
        $this->descripcion     = $descripcion;
        $this->prioridad       = $prioridad;
        $this->estadoSolicitud = $estadoSolicitud;
        $this->ciResponsable   = $ciResponsable;
        $this->fechaHoraCierre = $fechaHoraCierre;
        $this->idSolicitud     = $idSolicitud;
    }

    public function getIdSolicitud()     { return $this->idSolicitud; }
    public function getFechaHoraAlta()   { return $this->fechaHoraAlta; }
    public function getCiSolicitante()   { return $this->ciSolicitante; }
    public function getNombreSalon()     { return $this->nombreSalon; }
    public function getDescripcion()     { return $this->descripcion; }
    public function getPrioridad()       { return $this->prioridad; }
    public function getEstadoSolicitud() { return $this->estadoSolicitud; }
    public function getCiResponsable()   { return $this->ciResponsable; }
    public function getFechaHoraCierre() { return $this->fechaHoraCierre; }

    public function setNombreSalon($nombreSalon)  { $this->nombreSalon = $nombreSalon; }
    public function setDescripcion($descripcion)  { $this->descripcion = $descripcion; }
    public function setPrioridad($prioridad)      { $this->prioridad = $prioridad; }
    public function setEstadoSolicitud($estado)   { $this->estadoSolicitud = $estado; }
    public function setCiResponsable($ci)         { $this->ciResponsable = $ci; }
    public function setFechaHoraCierre($fecha)    { $this->fechaHoraCierre = $fecha; }

    public function estaResuelta()
    {
        return $this->estadoSolicitud == 'Resuelta';
    }

    public function tieneResponsable()
    {
        return $this->ciResponsable != null;
    }
}
