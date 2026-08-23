<?php

class Usuario
{
    private $ci;
    private $nombreUsuario;
    private $tipoDeUsuario;
    private $estadoCuenta;

    public function __construct($ci, $nombreUsuario, $tipoDeUsuario, $estadoCuenta = 'Activa')
    {
        $this->ci            = $ci;
        $this->nombreUsuario = $nombreUsuario;
        $this->tipoDeUsuario = $tipoDeUsuario;
        $this->estadoCuenta  = $estadoCuenta;
    }

    public function getCi()            { return $this->ci; }
    public function getNombreUsuario() { return $this->nombreUsuario; }
    public function getTipoDeUsuario() { return $this->tipoDeUsuario; }
    public function getEstadoCuenta()  { return $this->estadoCuenta; }

    public function setNombreUsuario($nombreUsuario) { $this->nombreUsuario = $nombreUsuario; }
    public function setTipoDeUsuario($tipo)          { $this->tipoDeUsuario = $tipo; }
    public function setEstadoCuenta($estado)         { $this->estadoCuenta = $estado; }

    public function estaActiva()
    {
        return $this->estadoCuenta == 'Activa';
    }

    public function esCoordinador()
    {
        return $this->tipoDeUsuario == 'Coordinador';
    }

    public function esTecnico()
    {
        return $this->tipoDeUsuario == 'Asistente' || $this->tipoDeUsuario == 'Coordinador';
    }
}
