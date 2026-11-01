<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorUsoSala.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorUsoSala();


$id = '';

if (isset($_POST['id'])) {
    $id = trim((string) $_POST['id']);
} elseif (isset($_GET['id'])) {
    $id = trim((string) $_GET['id']);
}

if ($id === '') {
    header('Location: historial-uso-sala.php?aviso=noexiste');
    exit;
}

try {
    $fila = ResultadoGestion::valor($gestor->obtenerPorId($id));

    if ($fila) {
        $detallesGuardados = ResultadoGestion::valor($gestor->obtenerDetalle($id));
    } else {
        $detallesGuardados = [];
    }
} catch (Exception $e) {
    error_log('SGRSI detalle-uso-sala.php cargar: ' . $e->getMessage());
    header('Location: historial-uso-sala.php?aviso=error');
    exit;
}

if (!$fila) {
    header('Location: historial-uso-sala.php?aviso=noexiste');
    exit;
}

if (!ControlAcceso::puedeAtender() && $fila['ci_solicitante'] !== Sesion::ci()) {
    header('Location: historial-uso-sala.php?aviso=ajeno');
    exit;
}

$modoVer = $_SERVER['REQUEST_METHOD'] !== 'POST'
    && (!isset($_GET['modo']) || $_GET['modo'] !== 'editar');

$errores = [];
$valores = [
    'fecha'      => $fila['fecha'],
    'entrada'    => substr($fila['hora_inicio'], 0, 5),
    'salida'     => substr($fila['hora_fin'], 0, 5),
    'asignatura' => $fila['asignatura'],
    'grupo'      => $fila['grupo'],
    'turno'      => $fila['turno'],
    'salon'      => $fila['nombre_salon'],
];

$pcElegidas   = [];
$alumnosDados = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($valores as $campo => $valorActual) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }

    if (isset($_POST['pc']) && is_array($_POST['pc'])) {
        $pcElegidas = $_POST['pc'];
    }

    if (isset($_POST['alumno']) && is_array($_POST['alumno'])) {
        $alumnosDados = $_POST['alumno'];
    }
} else {
    $numero = 1;

    foreach ($detallesGuardados as $guardado) {
        $pcElegidas[$numero] = $guardado['id_equipo'];

        if ($guardado['nombre_alumno'] === null) {
            $alumnosDados[$numero] = '';
        } else {
            $alumnosDados[$numero] = $guardado['nombre_alumno'];
        }

        $numero = $numero + 1;
    }
}

try {
    $salones = ResultadoGestion::valor($gestor->obtenerSalones());
} catch (Exception $e) {
    error_log('SGRSI detalle-uso-sala.php salones: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = Idioma::t('err_lista_salones');
}

$equipos = [];

if ($valores['salon'] !== '') {
    try {
        $equipos = ResultadoGestion::valor($gestor->obtenerEquiposPorSalon($valores['salon']));
    } catch (Exception $e) {
        error_log('SGRSI detalle-uso-sala.php equipos: ' . $e->getMessage());
        $errores['general'] = Idioma::t('err_lista_equipos');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ResultadoGestion::campo($_POST, 'accion') === 'guardar') {
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->actualizarDesdeFormulario($id, $_POST, Sesion::ci(), ControlAcceso::puedeAtender())
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    if ($resultado['ok']) {
        header('Location: historial-uso-sala.php?aviso=guardado');
        exit;
    }
    http_response_code($resultado['codigo']);
    $errores['general'] = $resultado['mensaje'];
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
<html lang="<?php echo Idioma::t('html_lang'); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/sala.css">
    <title><?php echo Idioma::e('tit_detalle_uso'); ?></title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php"><?php echo Idioma::e('nav_dashboard'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_sala'); ?></li>
                <?php if (ControlAcceso::puedeAtender()) { ?>
                    <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php"><?php echo Idioma::e('nav_inventario'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php"><?php echo Idioma::e('nav_mesa'); ?></a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php"><?php echo Idioma::e('nav_solicitudes'); ?></a></li>
                <?php if (ControlAcceso::puedeGestionarUsuarios()) { ?>
                    <li class="sidebar-item"><a href="../usuarios/nuevo-usuario.php"><?php echo Idioma::e('nav_usuarios'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir"><?php echo Idioma::e('nav_cerrar_sesion'); ?></a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="planilla-uso-sala.php"><?php echo Idioma::e('tab_planilla_uso'); ?></a></li>
                        <li class="topbar-item"><a href="historial-uso-sala.php"><?php echo Idioma::e('tab_historial_uso'); ?></a></li>
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_detalle_uso'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1>Registro de uso n.º <?php echo v($fila['id_uso']); ?></h1>

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>
                <p id="resultado-uso-sala" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-uso-sala" action="detalle-uso-sala.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="id" value="<?php echo v($fila['id_uso']); ?>">
                    <fieldset<?php echo $modoVer ? ' disabled' : ''; ?>>

                        <div class="form-grupo">
                            <label for="docente"><?php echo Idioma::e('th_docente'); ?></label>
                            <input type="text" id="docente" value="<?php echo v($fila['nombre_docente']); ?>" disabled>
                        </div>
                        <div class="form-grupo">
                            <label for="fecha"><?php echo Idioma::e('th_fecha'); ?></label>
                            <input type="date" id="fecha" name="fecha" value="<?php echo v($valores['fecha']); ?>" required>
                            <p class="error-mensaje" id="error-fecha"<?php echo claseError($errores, 'fecha'); ?>><?php echo v(isset($errores['fecha']) ? $errores['fecha'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="entrada"><?php echo Idioma::e('th_hora_entrada'); ?></label>
                            <input type="time" id="entrada" name="entrada" value="<?php echo v($valores['entrada']); ?>" required>
                            <p class="error-mensaje" id="error-entrada"<?php echo claseError($errores, 'entrada'); ?>><?php echo v(isset($errores['entrada']) ? $errores['entrada'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="salida"><?php echo Idioma::e('th_hora_salida'); ?></label>
                            <input type="time" id="salida" name="salida" value="<?php echo v($valores['salida']); ?>" required>
                            <p class="error-mensaje" id="error-salida"<?php echo claseError($errores, 'salida'); ?>><?php echo v(isset($errores['salida']) ? $errores['salida'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="asignatura"><?php echo Idioma::e('th_asignatura'); ?></label>
                            <input type="text" id="asignatura" name="asignatura" value="<?php echo v($valores['asignatura']); ?>" required>
                            <p class="error-mensaje" id="error-asignatura"<?php echo claseError($errores, 'asignatura'); ?>><?php echo v(isset($errores['asignatura']) ? $errores['asignatura'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="grupo"><?php echo Idioma::e('th_grupo'); ?></label>
                            <input type="text" id="grupo" name="grupo" value="<?php echo v($valores['grupo']); ?>" placeholder="Ej: 1MI" required>
                            <p class="error-mensaje" id="error-grupo"<?php echo claseError($errores, 'grupo'); ?>><?php echo v(isset($errores['grupo']) ? $errores['grupo'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="turno"><?php echo Idioma::e('th_turno'); ?></label>
                            <select name="turno" id="turno" required>
                                <?php foreach (Dominio::TURNOS as $turno) { ?>
                                    <option value="<?php echo v($turno); ?>"<?php echo $valores['turno'] === $turno ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $turno)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-turno"<?php echo claseError($errores, 'turno'); ?>><?php echo v(isset($errores['turno']) ? $errores['turno'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="salon"><?php echo Idioma::e('th_salon'); ?></label>
                            <select name="salon" id="salon" required>
                                <?php foreach ($salones as $unSalon) { ?>
                                    <option value="<?php echo v($unSalon['nombre_salon']); ?>"<?php echo $valores['salon'] === $unSalon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($unSalon['nombre_salon']); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"<?php echo claseError($errores, 'salon'); ?>><?php echo v(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                        </div>

                        <div id="lista-pcs">
                            <h2><?php echo Idioma::e('enc_asignacion_equipos'); ?></h2>

                            <?php foreach ($equipos as $numeroEquipo => $equipo) { ?>
                                <?php $numeroFila = $numeroEquipo + 1; ?>
                                <?php $elegida = ResultadoGestion::campo($pcElegidas, $numeroFila); ?>
                                <div class="fila-pc<?php echo ($numeroFila > 1 && $elegida === '') ? ' oculta' : ''; ?>" data-index="<?php echo $numeroFila; ?>">
                                    <div class="campo">
                                        <label for="pc-<?php echo $numeroFila; ?>">PC</label>
                                        <select id="pc-<?php echo $numeroFila; ?>" name="pc[<?php echo $numeroFila; ?>]">
                                            <option value=""><?php echo Idioma::e('opt_selecciona_pc'); ?></option>
                                            <?php foreach ($equipos as $opcion) { ?>
                                                <option value="<?php echo v($opcion['id_equipo']); ?>"<?php echo (int) $elegida === (int) $opcion['id_equipo'] ? ' selected' : ''; ?>><?php echo v($opcion['nombre_equipo']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="campo">
                                        <label for="alumno-<?php echo $numeroFila; ?>"><?php echo Idioma::e('th_alumno'); ?></label>
                                        <input type="text" id="alumno-<?php echo $numeroFila; ?>" name="alumno[<?php echo $numeroFila; ?>]" value="<?php echo v(ResultadoGestion::campo($alumnosDados, $numeroFila)); ?>" placeholder="<?php echo Idioma::e('ph_nombre_alumno'); ?>">
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if (count($equipos) > 1) { ?>
                                <button type="button" id="agregar-pc"><?php echo Idioma::e('btn_agregar_pc'); ?></button>
                            <?php } ?>

                            <p class="error-mensaje" id="error-pcs"<?php echo claseError($errores, 'pcs'); ?>><?php echo v(isset($errores['pcs']) ? $errores['pcs'] : ''); ?></p>
                        </div>

                    </fieldset>

                    <?php if ($modoVer) { ?>
                        <a class="btn-editar-form" href="detalle-uso-sala.php?id=<?php echo urlencode($fila['id_uso']); ?>&amp;modo=editar"><?php echo Idioma::e('btn_editar'); ?></a>
                    <?php } else { ?>
                        <button type="submit" id="btn-enviar" name="accion" value="guardar"><?php echo Idioma::e('btn_guardar_cambios'); ?></button>
                    <?php } ?>

                </form>

                <div class="form-grupo">
                    <a href="historial-uso-sala.php" class="btn-back"><?php echo Idioma::e('btn_volver_historial'); ?></a>
                </div>

            </section>
        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/uso-sala/planilla-uso-sala.js"></script>
</body>

</html>
