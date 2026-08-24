<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/dao/UsoSalaDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/SalonDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/EquipoDAO.php';

ControlAcceso::exigirSesion('../../../index.php');

$usoSalaDAO = new UsoSalaDAO();
$salonDAO   = new SalonDAO();
$equipoDAO  = new EquipoDAO();

$errores = [];
$valores = [
    'fecha'      => date('Y-m-d'),
    'entrada'    => '',
    'salida'     => '',
    'asignatura' => '',
    'grupo'      => '',
    'turno'      => '',
    'salon'      => '',
];

try {
    $salones = $salonDAO->obtenerTodos();
} catch (Exception $e) {
    error_log('SGRSI planilla-uso-sala.php salones: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = 'No se pudo cargar la lista de salones. Intentá de nuevo en unos minutos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $valorActual) {
        $valores[$campo] = trim((string) (isset($_POST[$campo]) ? $_POST[$campo] : ''));
    }
}

$equipos = [];

if ($valores['salon'] !== '') {
    try {
        $equipos = $equipoDAO->obtenerPorSalon($valores['salon']);
    } catch (Exception $e) {
        error_log('SGRSI planilla-uso-sala.php equipos: ' . $e->getMessage());
        $errores['general'] = 'No se pudo cargar la lista de equipos. Intentá de nuevo en unos minutos.';
    }
}

$pcEnviadas     = isset($_POST['pc']) && is_array($_POST['pc']) ? $_POST['pc'] : [];
$alumnoEnviados = isset($_POST['alumno']) && is_array($_POST['alumno']) ? $_POST['alumno'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['accion']) ? $_POST['accion'] : '') === 'crear') {

    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $valores['fecha']) !== 1) {
        $errores['fecha'] = 'Indicá una fecha válida.';
    }

    if (preg_match('/^[0-9]{2}:[0-9]{2}$/', $valores['entrada']) !== 1) {
        $errores['entrada'] = 'Indicá una hora de entrada válida.';
    }

    if (preg_match('/^[0-9]{2}:[0-9]{2}$/', $valores['salida']) !== 1) {
        $errores['salida'] = 'Indicá una hora de salida válida.';
    }

    if (empty($errores['entrada']) && empty($errores['salida']) && $valores['salida'] <= $valores['entrada']) {
        $errores['salida'] = 'La hora de salida debe ser posterior a la de entrada.';
    }

    if ($valores['asignatura'] === '') {
        $errores['asignatura'] = 'La asignatura es obligatoria.';
    } elseif (strlen($valores['asignatura']) > 60) {
        $errores['asignatura'] = 'La asignatura no puede superar los 60 caracteres.';
    }

    if ($valores['grupo'] === '') {
        $errores['grupo'] = 'El grupo es obligatorio.';
    } elseif (strlen($valores['grupo']) > 10) {
        $errores['grupo'] = 'El grupo no puede superar los 10 caracteres.';
    }

    if (!Dominio::esTurno($valores['turno'])) {
        $errores['turno'] = 'Elegí un turno de la lista.';
    }

    $nombresSalon = [];

    foreach ($salones as $unSalon) {
        $nombresSalon[] = $unSalon['nombre_salon'];
    }

    if (!in_array($valores['salon'], $nombresSalon, true)) {
        $errores['salon'] = 'Elegí un salón válido de la lista.';
    }

    if (empty($errores['fecha']) && empty($errores['entrada']) && empty($errores['salida']) && empty($errores['salon'])) {
        try {
            if ($usoSalaDAO->existeSolapamiento($valores['salon'], $valores['fecha'], $valores['entrada'] . ':00', $valores['salida'] . ':00')) {
                $errores['salon'] = 'La sala ya está ocupada en ese horario.';
            }
        } catch (Exception $e) {
            error_log('SGRSI planilla-uso-sala.php solapamiento: ' . $e->getMessage());
            $errores['general'] = 'No se pudo verificar la disponibilidad de la sala. Intentá de nuevo en unos minutos.';
        }
    }

    $idsDelSalon = [];

    foreach ($equipos as $unEquipo) {
        $idsDelSalon[] = (int) $unEquipo['id_equipo'];
    }
    $detalles    = [];
    $yaUsados    = [];

    foreach ($pcEnviadas as $indice => $idEquipo) {
        $idEquipo = trim((string) $idEquipo);

        if ($idEquipo === '') {
            continue;
        }

        if (!in_array((int) $idEquipo, $idsDelSalon, true)) {
            $errores['pcs'] = 'Alguna de las PC elegidas no pertenece al salón seleccionado.';
            continue;
        }

        if (in_array((int) $idEquipo, $yaUsados, true)) {
            $errores['pcs'] = 'No se puede asignar la misma PC dos veces.';
            continue;
        }

        $yaUsados[] = (int) $idEquipo;

        $alumno = trim((string) (isset($alumnoEnviados[$indice]) ? $alumnoEnviados[$indice] : ''));

        $detalles[] = new DetalleUso((int) $idEquipo, $alumno !== '' ? $alumno : null);
    }

    if (empty($detalles) && empty($errores['pcs'])) {
        $errores['pcs'] = 'Asigná al menos una PC.';
    }

    if (empty($errores)) {
        try {
            $uso = new UsoSala(
                $valores['fecha'],
                $valores['entrada'] . ':00',
                $valores['salida'] . ':00',
                $valores['salon'],
                Sesion::ci(),
                $valores['asignatura'],
                $valores['grupo'],
                $valores['turno']
            );

            $usoSalaDAO->insertar($uso, $detalles);

            header('Location: historial-uso-sala.php?aviso=creado');
            exit;
        } catch (Exception $e) {
            error_log('SGRSI planilla-uso-sala.php insertar: ' . $e->getMessage());
            $errores['general'] = 'No se pudo registrar el uso de la sala. Intentá de nuevo en unos minutos.';
        }
    }
}

function v($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function claseError($errores, $campo)
{
    return !empty($errores[$campo]) ? ' style="display: block;"' : '';
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
    <title>SGRSI - Planilla de uso de sala</title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <?php } ?>
                <li class="sidebar-item activo">Sala de informática</li>
                <?php if (ControlAcceso::puedeAtender()) { ?>
                    <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
                <?php } ?>
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
                        <li class="topbar-item activo">Planilla de uso</li>
                        <li class="topbar-item"><a href="historial-uso-sala.php">Historial de uso</a></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1>Planilla de Registro de Uso de Salas de Informática</h1>

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>

                <form id="form-uso-sala" action="planilla-uso-sala.php" method="post">
                    <div class="form-grupo">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo v($valores['fecha']); ?>" required>
                        <p class="error-mensaje" id="error-fecha"<?php echo claseError($errores, 'fecha'); ?>><?php echo v(isset($errores['fecha']) ? $errores['fecha'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="entrada">Hora entrada</label>
                        <input type="time" id="entrada" name="entrada" value="<?php echo v($valores['entrada']); ?>" required>
                        <p class="error-mensaje" id="error-entrada"<?php echo claseError($errores, 'entrada'); ?>><?php echo v(isset($errores['entrada']) ? $errores['entrada'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="salida">Hora salida</label>
                        <input type="time" id="salida" name="salida" value="<?php echo v($valores['salida']); ?>" required>
                        <p class="error-mensaje" id="error-salida"<?php echo claseError($errores, 'salida'); ?>><?php echo v(isset($errores['salida']) ? $errores['salida'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="docente">Docente</label>
                        <input type="text" id="docente" value="<?php echo v(Sesion::nombre()); ?>" disabled>
                    </div>
                    <div class="form-grupo">
                        <label for="asignatura">Asignatura</label>
                        <input type="text" id="asignatura" name="asignatura" value="<?php echo v($valores['asignatura']); ?>" required>
                        <p class="error-mensaje" id="error-asignatura"<?php echo claseError($errores, 'asignatura'); ?>><?php echo v(isset($errores['asignatura']) ? $errores['asignatura'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="grupo">Grupo</label>
                        <input type="text" id="grupo" name="grupo" value="<?php echo v($valores['grupo']); ?>" placeholder="Ej: 1MI" required>
                        <p class="error-mensaje" id="error-grupo"<?php echo claseError($errores, 'grupo'); ?>><?php echo v(isset($errores['grupo']) ? $errores['grupo'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="turno">Turno</label>
                        <select name="turno" id="turno" required>
                            <option value="">Elija un turno</option>
                            <?php foreach (Dominio::TURNOS as $turno) { ?>
                                <option value="<?php echo v($turno); ?>"<?php echo $valores['turno'] === $turno ? ' selected' : ''; ?>><?php echo v($turno); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-turno"<?php echo claseError($errores, 'turno'); ?>><?php echo v(isset($errores['turno']) ? $errores['turno'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="salon">Salón</label>
                        <select name="salon" id="salon" required onchange="this.form.submit()">
                            <option value="">Elija una opción</option>
                            <?php foreach ($salones as $salon) { ?>
                                <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-salon"<?php echo claseError($errores, 'salon'); ?>><?php echo v(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                    </div>

                    <div id="lista-pcs">
                        <?php if (empty($equipos)) { ?>
                            <p class="sin-accion">Elegí un salón para asignar las PC.</p>
                        <?php } ?>

                        <?php foreach ($equipos as $numero => $equipo) { ?>
                            <?php $fila = $numero + 1; ?>
                            <?php $elegida = trim((string) (isset($pcEnviadas[$fila]) ? $pcEnviadas[$fila] : '')); ?>
                            <div class="fila-pc<?php echo ($fila > 1 && $elegida === '') ? ' oculta' : ''; ?>" data-index="<?php echo $fila; ?>">
                                <div class="campo">
                                    <label for="pc-<?php echo $fila; ?>">PC</label>
                                    <select id="pc-<?php echo $fila; ?>" name="pc[<?php echo $fila; ?>]">
                                        <option value="">-- Seleccioná una PC --</option>
                                        <?php foreach ($equipos as $opcion) { ?>
                                            <option value="<?php echo v($opcion['id_equipo']); ?>"<?php echo (int) $elegida === (int) $opcion['id_equipo'] ? ' selected' : ''; ?>><?php echo v($opcion['nombre_equipo']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="campo">
                                    <label for="alumno-<?php echo $fila; ?>">Alumno</label>
                                    <input type="text" id="alumno-<?php echo $fila; ?>" name="alumno[<?php echo $fila; ?>]" value="<?php echo v(isset($alumnoEnviados[$fila]) ? $alumnoEnviados[$fila] : ''); ?>" placeholder="Nombre del alumno">
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (count($equipos) > 1) { ?>
                            <button type="button" id="agregar-pc">+ Agregar PC</button>
                        <?php } ?>

                        <p class="error-mensaje" id="error-pcs"<?php echo claseError($errores, 'pcs'); ?>><?php echo v(isset($errores['pcs']) ? $errores['pcs'] : ''); ?></p>
                    </div>

                    <button type="submit" id="btn-enviar" name="accion" value="crear">Enviar</button>

                </form>
            </section>
        </main>
    </div>
    <script src="../../js/uso-sala/planilla-uso-sala.js"></script>
</body>

</html>