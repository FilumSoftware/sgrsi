<?php

class UsoSala
{
    private $idUso;
    private $fecha;
    private $horaInicio;
    private $horaFin;
    private $nombreSalon;
    private $ciSolicitante;
    private $asignatura;
    private $grupo;
    private $turno;

    public function __construct($fecha, $horaInicio, $horaFin, $nombreSalon, $ciSolicitante,
                                $asignatura, $grupo, $turno, $idUso = null)
    {
        $this->fecha         = $fecha;
        $this->horaInicio    = $horaInicio;
        $this->horaFin       = $horaFin;
        $this->nombreSalon   = $nombreSalon;
        $this->ciSolicitante = $ciSolicitante;
        $this->asignatura    = $asignatura;
        $this->grupo         = $grupo;
        $this->turno         = $turno;
        $this->idUso         = $idUso;
    }

    public function getIdUso()         { return $this->idUso; }
    public function getFecha()         { return $this->fecha; }
    public function getHoraInicio()    { return $this->horaInicio; }
    public function getHoraFin()       { return $this->horaFin; }
    public function getNombreSalon()   { return $this->nombreSalon; }
    public function getCiSolicitante() { return $this->ciSolicitante; }
    public function getAsignatura()    { return $this->asignatura; }
    public function getGrupo()         { return $this->grupo; }
    public function getTurno()         { return $this->turno; }

    public function setFecha($fecha)             { $this->fecha = $fecha; }
    public function setHoraInicio($hora)         { $this->horaInicio = $hora; }
    public function setHoraFin($hora)            { $this->horaFin = $hora; }
    public function setNombreSalon($salon)       { $this->nombreSalon = $salon; }
    public function setAsignatura($asignatura)   { $this->asignatura = $asignatura; }
    public function setGrupo($grupo)             { $this->grupo = $grupo; }
    public function setTurno($turno)             { $this->turno = $turno; }

    // La base tambien lo valida, pero conviene avisar antes de llegar ahi.
    public function horarioEsValido()
    {
        return $this->horaFin > $this->horaInicio;
    }
}
