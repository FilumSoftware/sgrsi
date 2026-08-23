<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query(
            'SELECT ci, nombre_usuario, tipo_de_usuario, estado_cuenta
               FROM usuario
              ORDER BY nombre_usuario'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorCi($ci)
    {
        $stmt = $this->pdo->prepare(
            'SELECT ci, nombre_usuario, tipo_de_usuario, estado_cuenta
               FROM usuario
              WHERE ci = :ci'
        );
        $stmt->execute([':ci' => $ci]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerParaLogin($ci)
    {
        $stmt = $this->pdo->prepare(
            'SELECT ci, nombre_usuario, contrasena, tipo_de_usuario, estado_cuenta
               FROM usuario
              WHERE ci = :ci'
        );
        $stmt->execute([':ci' => $ci]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTecnicos()
    {
        $stmt = $this->pdo->query(
            "SELECT ci, nombre_usuario, tipo_de_usuario
               FROM usuario
              WHERE tipo_de_usuario IN ('Asistente', 'Coordinador')
                AND estado_cuenta = 'Activa'
              ORDER BY nombre_usuario"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar(Usuario $usuario, $claveEnClaro)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuario (ci, nombre_usuario, contrasena, tipo_de_usuario, estado_cuenta)
             VALUES (:ci, :nombre, :contrasena, :tipo, :estado)'
        );

        return $stmt->execute([
            ':ci'         => $usuario->getCi(),
            ':nombre'     => $usuario->getNombreUsuario(),
            ':contrasena' => password_hash($claveEnClaro, PASSWORD_DEFAULT),
            ':tipo'       => $usuario->getTipoDeUsuario(),
            ':estado'     => $usuario->getEstadoCuenta()
        ]);
    }

    public function actualizar(Usuario $usuario)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuario
                SET nombre_usuario = :nombre,
                    tipo_de_usuario = :tipo,
                    estado_cuenta = :estado
              WHERE ci = :ci'
        );

        return $stmt->execute([
            ':nombre' => $usuario->getNombreUsuario(),
            ':tipo'   => $usuario->getTipoDeUsuario(),
            ':estado' => $usuario->getEstadoCuenta(),
            ':ci'     => $usuario->getCi()
        ]);
    }

    public function cambiarEstado($ci, $estado)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuario SET estado_cuenta = :estado WHERE ci = :ci'
        );

        return $stmt->execute([':estado' => $estado, ':ci' => $ci]);
    }

    public function cambiarContrasena($ci, $claveEnClaro)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuario SET contrasena = :contrasena WHERE ci = :ci'
        );

        return $stmt->execute([
            ':contrasena' => password_hash($claveEnClaro, PASSWORD_DEFAULT),
            ':ci'         => $ci
        ]);
    }
}
