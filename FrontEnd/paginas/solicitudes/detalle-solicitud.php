<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorSolicitudes.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorSolicitudes();

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
    $fila = ResultadoGestion::valor($gestor->obtenerPorId($id));
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

$modoVer = $_SERVER['REQUEST_METHOD'] !== 'POST'
    && (!isset($_GET['modo']) || $_GET['modo'] !== 'editar');

$editable = $puedeAtender && !$modoVer;

$errores = [];
$valores = [
    'salon'       => $fila['nombre_salon'],
    'prioridad'   => $fila['prioridad'],
    'estado'      => $fila['estado_solicitud'],
    'responsable' => isset($fila['ci_responsable']) ? $fila['ci_responsable'] : '',
    'motivo'      => $fila['descripcion'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $valorActual) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }
}

try {
    $salones  = ResultadoGestion::valor($gestor->obtenerSalones());
    $tecnicos = ResultadoGestion::valor($gestor->obtenerTecnicos());
} catch (Exception $e) {
    error_log('SGRSI detalle-solicitud.php listas: ' . $e->getMessage());
    $salones  = [];
    $tecnicos = [];
    $errores['general'] = Idioma::t('err_listas');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->actualizarDesdeFormulario($id, $_POST, $puedeAtender)
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    if ($resultado['ok']) {
        header('Location: solicitudes.php?aviso=guardada');
        exit;
    }
    http_response_code($resultado['codigo']);
    $errores['general'] = $resultado['mensaje'];
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
<html lang="<?php echo Idioma::t('html_lang'); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title><?php echo Idioma::e('tit_detalle_solicitud'); ?></title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php"><?php echo Idioma::e('nav_dashboard'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php"><?php echo Idioma::e('nav_sala'); ?></a></li>
                <?php if (ControlAcceso::puedeAtender()) { ?>
                    <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php"><?php echo Idioma::e('nav_inventario'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php"><?php echo Idioma::e('nav_mesa'); ?></a></li>
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_solicitudes'); ?></li>
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
                        <li class="topbar-item"><a href="nueva-solicitud.php"><?php echo Idioma::e('tab_nueva_solicitud'); ?></a></li>
                        <li class="topbar-item"><a href="solicitudes.php"><?php echo Idioma::e('tab_listado_solicitudes'); ?></a></li>
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_detalle_solicitud'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>
                <p id="resultado-guardado" class="error-mensaje" role="status" aria-live="polite"></p>

                <?php if (!$puedeAtender) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo Idioma::e('nota_solicitud_propia'); ?></p>
                <?php } ?>

                <form id="form-detalle-solicitud" action="detalle-solicitud.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <fieldset>
                        <legend>Solicitud n.º <?php echo v($fila['id_solicitud']); ?></legend>

                        <input type="hidden" name="id" value="<?php echo v($fila['id_solicitud']); ?>">

                        <div class="form-grupo">
                            <label for="solicitante"><?php echo Idioma::e('lbl_solicitante'); ?></label>
                            <input type="text" id="solicitante" value="<?php echo v($fila['nombre_solicitante']); ?>" readonly>
                        </div>
                        <div class="form-grupo">
                            <label for="fecha"><?php echo Idioma::e('lbl_fecha'); ?></label>
                            <input type="date" id="fecha" value="<?php echo v(date('Y-m-d', $alta)); ?>" readonly>
                        </div>
                        <div class="form-grupo">
                            <label for="estado"><?php echo Idioma::e('lbl_estado'); ?></label>
                            <select id="estado" name="estado"<?php echo $editable ? '' : ' disabled'; ?>>
                                <?php foreach (Dominio::ESTADOS_SOLICITUD as $estado) { ?>
                                    <option value="<?php echo v($estado); ?>"<?php echo $valores['estado'] === $estado ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $estado)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-estado"<?php echo claseError($errores, 'estado'); ?>><?php echo v(isset($errores['estado']) ? $errores['estado'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="prioridad"><?php echo Idioma::e('lbl_prioridad'); ?></label>
                            <select id="prioridad" name="prioridad"<?php echo $editable ? '' : ' disabled'; ?>>
                                <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                    <option value="<?php echo v($prioridad); ?>"<?php echo $valores['prioridad'] === $prioridad ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $prioridad)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-prioridad"<?php echo claseError($errores, 'prioridad'); ?>><?php echo v(isset($errores['prioridad']) ? $errores['prioridad'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="responsable"><?php echo Idioma::e('lbl_responsable'); ?></label>
                            <select id="responsable" name="responsable"<?php echo $editable ? '' : ' disabled'; ?>>
                                <option value=""><?php echo Idioma::e('opt_seleccionar_tecnico'); ?></option>
                                <?php foreach ($tecnicos as $tecnico) { ?>
                                    <option value="<?php echo v($tecnico['ci']); ?>"<?php echo $valores['responsable'] === $tecnico['ci'] ? ' selected' : ''; ?>><?php echo v($tecnico['nombre_usuario']); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-responsable"<?php echo claseError($errores, 'responsable'); ?>><?php echo v(isset($errores['responsable']) ? $errores['responsable'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="salon"><?php echo Idioma::e('lbl_salon'); ?></label>
                            <select id="salon" name="salon" required<?php echo $editable ? '' : ' disabled'; ?>>
                                <?php foreach ($salones as $salon) { ?>
                                    <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"<?php echo claseError($errores, 'salon'); ?>><?php echo v(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="motivo"><?php echo Idioma::e('lbl_motivo'); ?></label>
                            <textarea id="motivo" name="motivo" rows="4"<?php echo $editable ? '' : ' disabled'; ?>><?php echo v($valores['motivo']); ?></textarea>
                            <p class="error-mensaje" id="error-motivo"<?php echo claseError($errores, 'motivo'); ?>><?php echo v(isset($errores['motivo']) ? $errores['motivo'] : ''); ?></p>
                        </div>

                        <?php if ($editable) { ?>
                            <input type="submit" id="btn-guardar" value="Guardar solicitud">
                        <?php } elseif ($puedeAtender) { ?>
                            <a class="btn-editar-form" href="detalle-solicitud.php?id=<?php echo urlencode($id); ?>&amp;modo=editar"><?php echo Idioma::e('btn_editar'); ?></a>
                        <?php } ?>

                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/solicitudes/detalle-solicitud.js"></script>
</body>

</html>
