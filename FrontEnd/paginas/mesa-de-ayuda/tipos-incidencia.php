<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Asistente', 'Coordinador'], '../../../index.php');

function v($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="<?php echo Idioma::t('html_lang'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title><?php echo Idioma::e('tit_tipos_incidencia'); ?></title>
</head>
<body>
    <div class="layout">
        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php"><?php echo Idioma::e('nav_dashboard'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php"><?php echo Idioma::e('nav_sala'); ?></a></li>
                <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php"><?php echo Idioma::e('nav_inventario'); ?></a></li>
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
                <nav><ul>
                    <li class="topbar-item"><a href="nuevo-ticket.php"><?php echo Idioma::e('tab_nuevo_ticket'); ?></a></li>
                    <li class="topbar-item"><a href="mesa-de-ayuda.php"><?php echo Idioma::e('tab_historial_tickets'); ?></a></li>
                    <li class="topbar-item activo"><?php echo Idioma::e('tab_tipos_incidencia'); ?></li>
                </ul></nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>"><span>SGRSI</span></div>
            </header>
            <section class="content">
                <h1><?php echo Idioma::e('tab_tipos_incidencia'); ?></h1>
                <p id="resultado-tipos" class="error-mensaje" role="status" aria-live="polite"></p>
                <form id="form-tipo" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" id="nombre-original">
                    <div class="form-grupo">
                        <label for="nombre-tipo"><?php echo Idioma::e('th_nombre'); ?></label>
                        <input type="text" id="nombre-tipo" minlength="3" maxlength="80" required>
                    </div>
                    <div class="form-grupo">
                        <label for="estado-tipo"><?php echo Idioma::e('th_estado'); ?></label>
                        <select id="estado-tipo" disabled>
                            <option value="Activo"><?php echo Idioma::e('valor_Activo'); ?></option>
                            <option value="Inactivo"><?php echo Idioma::e('valor_Inactivo'); ?></option>
                        </select>
                    </div>
                    <button type="submit" id="guardar-tipo"><?php echo Idioma::e('btn_agregar_tipo'); ?></button>
                    <button type="button" id="cancelar-edicion-tipo" class="oculta"><?php echo Idioma::e('btn_cancelar_edicion'); ?></button>
                </form>
                <div class="tabla-wrapper">
                    <table>
                        <thead><tr><th><?php echo Idioma::e('th_nombre'); ?></th><th><?php echo Idioma::e('th_estado'); ?></th><th><?php echo Idioma::e('th_acciones'); ?></th></tr></thead>
                        <tbody id="lista-tipos"><tr><td colspan="3"><?php echo Idioma::e('cargando_tipos'); ?></td></tr></tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/mesa-de-ayuda/tipos-incidencia.js"></script>
</body>
</html>
