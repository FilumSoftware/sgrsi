<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorInventario.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorTickets.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorTiposIncidencia.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorTickets();
$valores = [
    'salon'           => '',
    'id_equipo'       => '',
    'tipo_de_defecto' => '',
    'detalle_defecto' => '',
    'descripcion'     => '',
    'prioridad'       => 'Normal',
];
$errorGeneral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }

    $resultado = Sesion::verificarTokenApi(isset($_POST['csrf']) ? $_POST['csrf'] : '')
        ? $gestor->crearDesdeFormulario($_POST, Sesion::ci())
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');

    if ($resultado['ok']) {
        header('Location: nuevo-ticket.php?numero=' . (int) $resultado['datos']);
        exit;
    }

    http_response_code($resultado['codigo']);
    $errorGeneral = Idioma::t($resultado['mensaje']);
}

$numero = isset($_GET['numero']) ? (int) $_GET['numero'] : 0;
$exito = $numero > 0
    ? Idioma::t('aviso_ticket_creado') . Idioma::t('js_num_prefijo') . $numero . '.'
    : '';

try {
    $salones = ResultadoGestion::valor($gestor->obtenerSalones());
    $equipos = ResultadoGestion::valor((new GestorInventario())->obtenerTodos());
    $tipos   = ResultadoGestion::valor((new GestorTiposIncidencia())->obtenerActivos());
} catch (Exception $e) {
    error_log('SGRSI nuevo-ticket.php catalogos: ' . $e->getMessage());
    $salones = [];
    $equipos = [];
    $tipos   = [];
    $errorGeneral = Idioma::t('aviso_error_operacion');
}

function v($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="<?php echo Idioma::t('html_lang'); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title><?php echo Idioma::e('tit_nuevo_ticket'); ?></title>
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
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_mesa'); ?></li>
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
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_nuevo_ticket'); ?></li>
                        <li class="topbar-item"><a href="mesa-de-ayuda.php"><?php echo Idioma::e('tab_historial_tickets'); ?></a></li>
                        <?php if (ControlAcceso::puedeAtender()) { ?>
                            <li class="topbar-item"><a href="tipos-incidencia.php"><?php echo Idioma::e('tab_tipos_incidencia'); ?></a></li>
                        <?php } ?>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">

                <?php if ($errorGeneral !== '') { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errorGeneral); ?></p>
                <?php } ?>
                <p id="resultado-ticket" class="error-mensaje <?php echo $exito !== '' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $exito !== '' ? ' style="display: block;"' : ''; ?>><?php echo v($exito); ?></p>

                <form id="form-ticket" action="nuevo-ticket.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <div class="form-grupo">
                        <label for="solicitante"><?php echo Idioma::e('th_solicitante'); ?></label>
                        <input type="text" id="solicitante" value="<?php echo v(Sesion::nombre()); ?>" disabled>
                    </div>
                    <div class="form-grupo">
                        <label for="fecha"><?php echo Idioma::e('th_fecha'); ?></label>
                        <input type="date" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="hora"><?php echo Idioma::e('th_hora'); ?></label>
                        <input type="time" name="hora" id="hora" value="<?php echo date('H:i'); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="salon"><?php echo Idioma::e('th_salon'); ?></label>
                        <select name="salon" id="salon" required>
                            <option value=""><?php echo Idioma::e('opt_elija_opcion'); ?></option>
                            <?php foreach ($salones as $salon) { ?>
                                <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-salon"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="equipo"><?php echo Idioma::e('th_equipo'); ?></label>
                        <select name="id_equipo" id="equipo" required>
                            <option value=""><?php echo Idioma::e('opt_elegi_salon_primero'); ?></option>
                            <?php $salonActual = null; ?>
                            <?php foreach ($equipos as $equipo) { ?>
                                <?php if ($equipo['nombre_salon'] !== $salonActual) { ?>
                                    <?php if ($salonActual !== null) { ?></optgroup><?php } ?>
                                    <optgroup label="<?php echo v($equipo['nombre_salon']); ?>">
                                    <?php $salonActual = $equipo['nombre_salon']; ?>
                                <?php } ?>
                                <option value="<?php echo (int) $equipo['id_equipo']; ?>"<?php echo $valores['id_equipo'] === (string) $equipo['id_equipo'] ? ' selected' : ''; ?>><?php echo v($equipo['nombre_equipo']); ?> (<?php echo Idioma::e('valor_' . $equipo['estado_equipo']); ?>)</option>
                            <?php } ?>
                            <?php if ($salonActual !== null) { ?></optgroup><?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-equipo"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="defecto"><?php echo Idioma::e('th_tipo_falla'); ?></label>
                        <select name="tipo_de_defecto" id="defecto" required>
                            <option value=""><?php echo Idioma::e('opt_elija_opcion'); ?></option>
                            <?php foreach ($tipos as $tipo) { ?>
                                <option value="<?php echo v($tipo['nombre']); ?>"<?php echo $valores['tipo_de_defecto'] === $tipo['nombre'] ? ' selected' : ''; ?>><?php echo v($tipo['nombre']); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-defecto"></p>
                    </div>
                    <div class="form-grupo" id="grupo-detalle-defecto">
                        <label for="detalle-defecto"><?php echo Idioma::e('th_describi_falla'); ?></label>
                        <input type="text" name="detalle_defecto" id="detalle-defecto" value="<?php echo v($valores['detalle_defecto']); ?>" maxlength="120" minlength="3">
                    </div>
                    <div class="form-grupo">
                        <label for="prioridad"><?php echo Idioma::e('th_prioridad'); ?></label>
                        <select name="prioridad" id="prioridad" required>
                            <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                <option value="<?php echo v($prioridad); ?>"<?php echo $valores['prioridad'] === $prioridad ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $prioridad)); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-prioridad"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="motivo"><?php echo Idioma::e('th_motivo'); ?></label>
                        <textarea name="descripcion" id="motivo" rows="5" placeholder="<?php echo Idioma::e('ph_escriba_motivo'); ?>" required><?php echo v($valores['descripcion']); ?></textarea>
                        <p class="error-mensaje" id="error-motivo"></p>
                    </div>

                    <button type="submit" id="btn-enviar" name="accion" value="crear"><?php echo Idioma::e('btn_enviar'); ?></button>

                </form>
            </section>
        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/mesa-de-ayuda/nuevo-ticket.js"></script>
</body>

</html>
