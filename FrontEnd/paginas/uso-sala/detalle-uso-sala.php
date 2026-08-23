<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/dao/UsoSalaDAO.php';

ControlAcceso::exigirSesion('../../../index.php');

$usoSalaDAO = new UsoSalaDAO();

$id = trim((string) (isset($_GET['id']) ? $_GET['id'] : ''));

if ($id === '') {
    header('Location: historial-uso-sala.php?aviso=noexiste');
    exit;
}

try {
    $uso      = $usoSalaDAO->obtenerPorId($id);
    $detalles = $uso ? $usoSalaDAO->obtenerDetalle($id) : [];
} catch (Exception $e) {
    error_log('SGRSI detalle-uso-sala.php cargar: ' . $e->getMessage());
    header('Location: historial-uso-sala.php?aviso=error');
    exit;
}

if (!$uso) {
    header('Location: historial-uso-sala.php?aviso=noexiste');
    exit;
}

if (!ControlAcceso::puedeAtender() && $uso['ci_solicitante'] !== Sesion::ci()) {
    header('Location: historial-uso-sala.php?aviso=ajeno');
    exit;
}

function v($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/sala.css">
    <title>SGRSI - Detalle de uso de sala</title>
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <li class="sidebar-item activo">Sala de informática</li>
                <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php">Mesa de Ayuda</a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php">Solicitudes</a></li>
                <?php if (ControlAcceso::puedeGestionarUsuarios()) { ?>
                    <li class="sidebar-item"><a href="../usuarios/nuevo-usuario.php">Usuarios</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir">Cerrar sesión</a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="planilla-uso-sala.php">Planilla de uso</a></li>
                        <li class="topbar-item"><a href="historial-uso-sala.php">Historial de uso</a></li>
                        <li class="topbar-item activo">Detalle de uso</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1>Detalle de Registro de Uso de Sala</h1>

                <div class="detalle-info-general">
                    <div class="form-grupo">
                        <label for="fecha">Fecha</label>
                        <input type="text" id="fecha" value="<?php echo v(date('d/m/Y', strtotime($uso['fecha']))); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="hora-entrada">Hora entrada</label>
                        <input type="text" id="hora-entrada" value="<?php echo v(substr($uso['hora_inicio'], 0, 5)); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="hora-salida">Hora salida</label>
                        <input type="text" id="hora-salida" value="<?php echo v(substr($uso['hora_fin'], 0, 5)); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="docente">Docente</label>
                        <input type="text" id="docente" value="<?php echo v($uso['nombre_docente']); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="asignatura">Asignatura</label>
                        <input type="text" id="asignatura" value="<?php echo v($uso['asignatura']); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="grupo">Grupo</label>
                        <input type="text" id="grupo" value="<?php echo v($uso['grupo']); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="turno">Turno</label>
                        <input type="text" id="turno" value="<?php echo v($uso['turno']); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="salon">Salón</label>
                        <input type="text" id="salon" value="<?php echo v($uso['nombre_salon']); ?>" readonly>
                    </div>
                </div>

                <hr class="separador">

                <div class="form-grupo">
                    <h2>Asignación de Equipos</h2>
                    <div class="tabla-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>PC</th>
                                    <th>Alumno</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detalles)) { ?>
                                    <tr>
                                        <td colspan="2">Este registro no tiene equipos asignados.</td>
                                    </tr>
                                <?php } ?>

                                <?php foreach ($detalles as $detalle) { ?>
                                    <tr>
                                        <td><strong><?php echo v($detalle['nombre_equipo']); ?></strong></td>
                                        <td><?php echo v(isset($detalle['nombre_alumno']) ? $detalle['nombre_alumno'] : 'Sin identificar'); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-grupo">
                    <a href="historial-uso-sala.php" class="btn-back">Volver al Historial</a>
                </div>

            </section>
        </main>
    </div>
</body>

</html>
