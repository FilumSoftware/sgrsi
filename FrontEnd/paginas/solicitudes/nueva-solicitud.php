<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorSolicitudes.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorSolicitudes();
$valores = ['nombre_salon' => '', 'prioridad' => 'Normal', 'descripcion' => ''];
$errorGeneral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }

    $resultado = Sesion::verificarTokenApi(isset($_POST['csrf']) ? $_POST['csrf'] : '')
        ? $gestor->crearDesdeFormulario($_POST, Sesion::ci())
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');

    if ($resultado['ok']) {
        header('Location: nueva-solicitud.php?numero=' . (int) $resultado['datos']);
        exit;
    }

    http_response_code($resultado['codigo']);
    $errorGeneral = Idioma::t($resultado['mensaje']);
}

$numero = isset($_GET['numero']) ? (int) $_GET['numero'] : 0;
$exito = $numero > 0
    ? Idioma::t('aviso_solicitud_creada') . Idioma::t('js_num_prefijo') . $numero . '.'
    : '';

try {
    $salones = ResultadoGestion::valor($gestor->obtenerSalones());
} catch (Exception $e) {
    error_log('SGRSI nueva-solicitud.php obtenerSalones: ' . $e->getMessage());
    $salones = [];
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
    <title><?php echo Idioma::e('tit_nueva_solicitud'); ?></title>
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
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_nueva_solicitud'); ?></li>
                        <li class="topbar-item"><a href="solicitudes.php"><?php echo Idioma::e('tab_listado_solicitudes'); ?></a></li>
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
                <p id="resultado-solicitud" class="error-mensaje <?php echo $exito !== '' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $exito !== '' ? ' style="display: block;"' : ''; ?>><?php echo v($exito); ?></p>

                <form id="form-solicitud" action="nueva-solicitud.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <fieldset>
                        <div class="form-grupo">
                            <label for="solicitante"><?php echo Idioma::e('lbl_solicitante'); ?></label>
                            <input type="text" id="solicitante" value="<?php echo v(Sesion::nombre()); ?>" disabled>
                        </div>
                        <div class="form-grupo">
                            <label for="fecha"><?php echo Idioma::e('lbl_fecha'); ?></label>
                            <input type="date" id="fecha" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>
                        <div class="form-grupo">
                            <label for="salon"><?php echo Idioma::e('lbl_salon'); ?></label>
                            <select id="salon" name="nombre_salon" required>
                                <option value=""><?php echo Idioma::e('opt_elija_opcion'); ?></option>
                                <?php foreach ($salones as $salon) { ?>
                                    <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['nombre_salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"></p>
                        </div>
                        <div class="form-grupo">
                            <label for="prioridad"><?php echo Idioma::e('lbl_prioridad'); ?></label>
                            <select id="prioridad" name="prioridad" required>
                                <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                    <option value="<?php echo v($prioridad); ?>"<?php echo $valores['prioridad'] === $prioridad ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $prioridad)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-prioridad"></p>
                        </div>
                        <div class="form-grupo">
                            <label for="motivo"><?php echo Idioma::e('lbl_motivo'); ?></label>
                            <textarea id="motivo" name="descripcion" rows="4" required><?php echo v($valores['descripcion']); ?></textarea>
                            <p class="error-mensaje" id="error-motivo"></p>
                        </div>

                        <input type="submit" id="btn-enviar" value="Enviar solicitud">
                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/solicitudes/nueva-solicitud.js"></script>
</body>

</html>
