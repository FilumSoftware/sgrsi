<?php

class DetalleUso
{
    private $idUso;
    private $idEquipo;
    private $nombreAlumno;

    public function __construct($idEquipo, $nombreAlumno = null, $idUso = null)
    {
        $this->idEquipo     = $idEquipo;
        $this->nombreAlumno = $nombreAlumno;
        $this->idUso        = $idUso;
    }

    public function getIdUso()        { return $this->idUso; }
    public function getIdEquipo()     { return $this->idEquipo; }
    public function getNombreAlumno() { return $this->nombreAlumno; }

    public function setIdUso($idUso)               { $this->idUso = $idUso; }
    public function setNombreAlumno($nombreAlumno) { $this->nombreAlumno = $nombreAlumno; }
}
