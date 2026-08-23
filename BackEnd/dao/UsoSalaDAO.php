<?php

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/../models/UsoSala.php';
require_once __DIR__ . '/../models/DetalleUso.php';

class UsoSalaDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexion::getInstancia()->getPdo();
    }

    public function obtenerTodos()
    {
        $stmt = $this->pdo->query(
            'SELECT u.id_uso, u.fecha, u.hora_inicio, u.hora_fin, u.nombre_salon,
                    u.asignatura, u.grupo, u.turno, u.ci_solicitante,
                    d.nombre_usuario AS nombre_docente
               FROM uso_sala u
               JOIN usuario d ON d.ci = u.ci_solicitante
              ORDER BY u.fecha DESC, u.hora_inicio DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($idUso)
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_uso, u.fecha, u.hora_inicio, u.hora_fin, u.nombre_salon,
                    u.asignatura, u.grupo, u.turno, u.ci_solicitante,
                    d.nombre_usuario AS nombre_docente
               FROM uso_sala u
               JOIN usuario d ON d.ci = u.ci_solicitante
              WHERE u.id_uso = :id'
        );
        $stmt->execute([':id' => $idUso]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalle($idUso)
    {
        $stmt = $this->pdo->prepare(
            'SELECT d.id_equipo, d.nombre_alumno, e.nombre_equipo
               FROM detalle_uso d
               JOIN equipo e ON e.id_equipo = d.id_equipo
              WHERE d.id_uso = :id
              ORDER BY e.nombre_equipo'
        );
        $stmt->execute([':id' => $idUso]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertar(UsoSala $uso, $detalles)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO uso_sala (fecha, hora_inicio, hora_fin, nombre_salon,
                                       ci_solicitante, asignatura, grupo, turno)
                 VALUES (:fecha, :inicio, :fin, :salon, :docente, :asignatura, :grupo, :turno)'
            );
            $stmt->execute([
                ':fecha'      => $uso->getFecha(),
                ':inicio'     => $uso->getHoraInicio(),
                ':fin'        => $uso->getHoraFin(),
                ':salon'      => $uso->getNombreSalon(),
                ':docente'    => $uso->getCiSolicitante(),
                ':asignatura' => $uso->getAsignatura(),
                ':grupo'      => $uso->getGrupo(),
                ':turno'      => $uso->getTurno()
            ]);

            $idUso = $this->pdo->lastInsertId();

            $stmtDetalle = $this->pdo->prepare(
                'INSERT INTO detalle_uso (id_uso, id_equipo, nombre_alumno)
                 VALUES (:uso, :equipo, :alumno)'
            );

            foreach ($detalles as $detalle) {
                $stmtDetalle->execute([
                    ':uso'    => $idUso,
                    ':equipo' => $detalle->getIdEquipo(),
                    ':alumno' => $detalle->getNombreAlumno()
                ]);
            }

            $this->pdo->commit();

            return $idUso;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('SGRSI: fallo el alta de uso de sala. ' . $e->getMessage());
            throw new Exception('No se pudo registrar el uso de la sala.');
        }
    }

    public function eliminar($idUso)
    {
        $stmt = $this->pdo->prepare('DELETE FROM uso_sala WHERE id_uso = :id');

        return $stmt->execute([':id' => $idUso]);
    }

    public function obtenerPorSolicitante($ci)
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id_uso, u.fecha, u.hora_inicio, u.hora_fin, u.nombre_salon,
                    u.asignatura, u.grupo, u.turno, u.ci_solicitante,
                    d.nombre_usuario AS nombre_docente
               FROM uso_sala u
               JOIN usuario d ON d.ci = u.ci_solicitante
              WHERE u.ci_solicitante = :ci
              ORDER BY u.fecha DESC, u.hora_inicio DESC'
        );
        $stmt->execute([':ci' => $ci]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar(UsoSala $uso, $detalles)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'UPDATE uso_sala
                    SET fecha = :fecha,
                        hora_inicio = :inicio,
                        hora_fin = :fin,
                        nombre_salon = :salon,
                        ci_solicitante = :docente,
                        asignatura = :asignatura,
                        grupo = :grupo,
                        turno = :turno
                  WHERE id_uso = :id'
            );
            $stmt->execute([
                ':fecha'      => $uso->getFecha(),
                ':inicio'     => $uso->getHoraInicio(),
                ':fin'        => $uso->getHoraFin(),
                ':salon'      => $uso->getNombreSalon(),
                ':docente'    => $uso->getCiSolicitante(),
                ':asignatura' => $uso->getAsignatura(),
                ':grupo'      => $uso->getGrupo(),
                ':turno'      => $uso->getTurno(),
                ':id'         => $uso->getIdUso()
            ]);

            $this->pdo->prepare('DELETE FROM detalle_uso WHERE id_uso = :id')
                      ->execute([':id' => $uso->getIdUso()]);

            $stmtDetalle = $this->pdo->prepare(
                'INSERT INTO detalle_uso (id_uso, id_equipo, nombre_alumno)
                 VALUES (:uso, :equipo, :alumno)'
            );

            foreach ($detalles as $detalle) {
                $stmtDetalle->execute([
                    ':uso'    => $uso->getIdUso(),
                    ':equipo' => $detalle->getIdEquipo(),
                    ':alumno' => $detalle->getNombreAlumno()
                ]);
            }

            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('SGRSI: fallo la edicion de uso de sala. ' . $e->getMessage());
            throw new Exception('No se pudo actualizar el uso de la sala.');
        }
    }

    public function contar()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS cantidad FROM uso_sala');
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $fila['cantidad'];
    }
}
