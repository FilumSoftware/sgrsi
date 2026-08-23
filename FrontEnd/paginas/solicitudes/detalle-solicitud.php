<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/dao/SolicitudDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/SalonDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/UsuarioDAO.php';
require_once __DIR__ . '/../../../BackEnd/models/Solicitud.php';

ControlAcceso::exigirSesion('../../../index.php');

$motivoMinimo = 10;

$solicitudDAO = new SolicitudDAO();
$salonDAO     = new SalonDAO();
$usuarioDAO   = new UsuarioDAO();

$id = '';

if (isset($_POST['id'])) {
    $id = trim((string) $_POST['id']);
} elseif (isset($_GET['id'])) {
    $id = trim((string) $_GET['id']);
}

if ($id === '') {
    header('Location: solicitudes.php?aviso=noexiste');
    exit;
}

try {
    $fila = $solicitudDAO->obtenerPorId($id);
} catch (Exception $e) {
    error_log('SGRSI detalle-solicitud.php obtenerPorId: ' . $e->getMessage());
    header('Location: solicitudes.php?aviso=error');
    exit;
}

if (!$fila) {
    header('Location: solicitudes.php?aviso=noexiste');
    exit;
}

$puedeAtender = ControlAcceso::puedeAtender();

if (!$puedeAtender && $fila['ci_solicitante'] !== Sesion::ci()) {
    header('Location: solicitudes.php?aviso=ajena');
    exit;
}

$errores = [];
$valores = [
    'salon'       => $fila['nombre_salon'],
    'prioridad'   => $fila['prioridad'],
    'estado'      => $fila['estado_solicitud'],
    'responsable' => isset($fila['ci_responsable']) ? $fila['ci_responsable'] : '',
    'motivo'      => $fila['descripcion'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!$puedeAtender) {
        header('Location: solicitudes.php?aviso=permiso');
        exit;
    }

    foreach ($valores as $campo => $valorActual) {
        $valores[$campo] = trim((string) (isset($_POST[$campo]) ? $_POST[$campo] : ''));
    }
}

try {
    $salones  = $salonDAO->obtenerTodos();
    $tecnicos = $usuarioDAO->obtenerTecnicos();
} catch (Exception $e) {
    error_log('SGRSI detalle-solicitud.php listas: ' . $e->getMessage());
    $salones  = [];
    $tecnicos = [];
    $errores['general'] = 'No se pudieron cargar las listas. Intentá de nuevo en unos minutos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombresSalon = [];

    foreach ($salones as $unSalon) {
        $nombresSalon[] = $unSalon['nombre_salon'];
    }

    if (!in_array($valores['salon'], $nombresSalon, true)) {
        $errores['salon'] = 'Elegí un salón válido de la lista.';
    }

    if (!Dominio::esPrioridad($valores['prioridad'])) {
        $errores['prioridad'] = 'Elegí una prioridad válida de la lista.';
    }

    if (!Dominio::esEstadoSolicitud($valores['estado'])) {
        $errores['estado'] = 'Elegí un estado válido de la lista.';
    }

    $cedulasTecnico = [];

    foreach ($tecnicos as $unTecnico) {
        $cedulasTecnico[] = $unTecnico['ci'];
    }

    if ($valores['responsable'] !== '' && !in_array($valores['responsable'], $cedulasTecnico, true)) {
        $errores['responsable'] = 'Elegí un técnico de la lista.';
    }

    if (strlen($valores['motivo']) < $motivoMinimo) {
        $errores['motivo'] = 'Escribí al menos ' . $motivoMinimo . ' caracteres.';
    }

    if ($valores['estado'] === 'Resuelta' && $valores['responsable'] === '') {
        $errores['responsable'] = 'Para cerrar la solicitud hay que asignar un responsable.';
    }

    if (empty($errores)) {

        $cierre = null;

        if ($valores['estado'] === 'Resuelta') {
            $cierre = isset($fila['fecha_hora_cierre']) ? $fila['fecha_hora_cierre'] : null;

            if ($cierre === null) {
                $ahora  = time();
                $alta   = strtotime($fila['fecha_hora_alta']);
                $cierre = date('Y-m-d H:i:s', $ahora >= $alta ? $ahora : $alta);
            }
        }

        try {
            $solicitud = new Solicitud(
                $fila['fecha_hora_alta'],
                $fila['ci_solicitante'],
                $valores['salon'],
                $valores['motivo'],
                $valores['prioridad'],
                $valores['estado'],
                $valores['responsable'] !== '' ? $valores['responsable'] : null,
                $cierre,
                $fila['id_solicitud']
            );

            $solicitudDAO->actualizar($solicitud);

            header('Location: solicitudes.php?aviso=guardada');
            exit;
        } catch (Exception $e) {
            error_log('SGRSI detalle-solicitud.php actualizar: ' . $e->getMessage());
            $errores['general'] = 'No se pudieron guardar los cambios. Intentá de nuevo en unos minutos.';
        }
    }
}

$alta = strtotime($fila['fecha_hora_alta']);

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
    <title>SGRSI - Detalle de solicitud</title>
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <?php if (ControlAcceso::puedeAtender()) { ?>
                    <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php">Mesa de Ayuda</a></li>
                <li class="sidebar-item activo">Solicitudes</li>
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
                        <li class="topbar-item"><a href="nueva-solicitud.php">Nueva solicitud</a></li>
                        <li class="topbar-item"><a href="solicitudes.php">Listado solicitudes</a></li>
                        <li class="topbar-item activo">Detalle solicitud</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>

                <?php if (!$puedeAtender) { ?>
                    <p class="error-mensaje" style="display: block;">Esta solicitud es tuya, pero solo la puede modificar un técnico.</p>
                <?php } ?>

                <form id="form-detalle-solicitud" action="detalle-solicitud.php" method="post">
                    <fieldset>
                        <legend>Solicitud n.º <?php echo v($fila['id_solicitud']); ?></legend>

                        <input type="hidden" name="id" value="<?php echo v($fila['id_solicitud']); ?>">

                        <div class="form-grupo">
                            <label for="solicitante">Solicitante:</label>
                            <input type="text" id="solicitante" value="<?php echo v($fila['nombre_solicitante']); ?>" readonly>
                        </div>
                        <div class="form-grupo">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" value="<?php echo v(date('Y-m-d', $alta)); ?>" readonly>
                        </div>
                        <div class="form-grupo">
                            <label for="estado">Estado:</label>
                            <select id="estado" name="estado"<?php echo $puedeAtender ? '' : ' disabled'; ?>>
                                <?php foreach (Dominio::ESTADOS_SOLICITUD as $estado) { ?>
                                    <option value="<?php echo v($estado); ?>"<?php echo $valores['estado'] === $estado ? ' selected' : ''; ?>><?php echo v($estado); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-estado"<?php echo claseError($errores, 'estado'); ?>><?php echo v(isset($errores['estado']) ? $errores['estado'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="prioridad">Prioridad:</label>
                            <select id="prioridad" name="prioridad"<?php echo $puedeAtender ? '' : ' disabled'; ?>>
                                <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                    <option value="<?php echo v($prioridad); ?>"<?php echo $valores['prioridad'] === $prioridad ? ' selected' : ''; ?>><?php echo v($prioridad); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-prioridad"<?php echo claseError($errores, 'prioridad'); ?>><?php echo v(isset($errores['prioridad']) ? $errores['prioridad'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="responsable">Responsable:</label>
                            <select id="responsable" name="responsable"<?php echo $puedeAtender ? '' : ' disabled'; ?>>
                                <option value="">— Seleccionar técnico —</option>
                                <?php foreach ($tecnicos as $tecnico) { ?>
                                    <option value="<?php echo v($tecnico['ci']); ?>"<?php echo $valores['responsable'] === $tecnico['ci'] ? ' selected' : ''; ?>><?php echo v($tecnico['nombre_usuario']); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-responsable"<?php echo claseError($errores, 'responsable'); ?>><?php echo v(isset($errores['responsable']) ? $errores['responsable'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon"<?php echo $puedeAtender ? '' : ' disabled'; ?>>
                                <?php foreach ($salones as $salon) { ?>
                                    <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"<?php echo claseError($errores, 'salon'); ?>><?php echo v(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="motivo">Motivo:</label>
                            <textarea id="motivo" name="motivo" rows="4"<?php echo $puedeAtender ? '' : ' disabled'; ?>><?php echo v($valores['motivo']); ?></textarea>
                            <p class="error-mensaje" id="error-motivo"<?php echo claseError($errores, 'motivo'); ?>><?php echo v(isset($errores['motivo']) ? $errores['motivo'] : ''); ?></p>
                        </div>

                        <?php if ($puedeAtender) { ?>
                            <input type="submit" value="Guardar solicitud">
                        <?php } ?>

                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/solicitudes/detalle-solicitud.js"></script>
</body>

</html>
