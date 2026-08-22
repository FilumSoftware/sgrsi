<?php

class Usuario
{
    private $ci;
    private $nombreUsuario;
    private $email;
    private $tipoDeUsuario;
    private $estadoCuenta;

    public function __construct($ci, $nombreUsuario, $email, $tipoDeUsuario, $estadoCuenta = 'Activa')
    {
        $this->ci            = $ci;
        $this->nombreUsuario = $nombreUsuario;
        $this->email         = $email;
        $this->tipoDeUsuario = $tipoDeUsuario;
        $this->estadoCuenta  = $estadoCuenta;
    }

    public function getCi()            { return $this->ci; }
    public function getNombreUsuario() { return $this->nombreUsuario; }
    public function getEmail()         { return $this->email; }
    public function getTipoDeUsuario() { return $this->tipoDeUsuario; }
    public function getEstadoCuenta()  { return $this->estadoCuenta; }

    public function setNombreUsuario($nombreUsuario) { $this->nombreUsuario = $nombreUsuario; }
    public function setEmail($email)                 { $this->email = $email; }
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
