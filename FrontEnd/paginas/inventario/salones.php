<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

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
    <title><?php echo Idioma::e('tit_salones'); ?></title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php"><?php echo Idioma::e('nav_dashboard'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php"><?php echo Idioma::e('nav_sala'); ?></a></li>
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_inventario'); ?></li>
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
                        <li class="topbar-item"><a href="nuevo-equipo.php"><?php echo Idioma::e('tab_registro_equipos'); ?></a></li>
                        <li class="topbar-item"><a href="inventario.php"><?php echo Idioma::e('tab_equipos'); ?></a></li>
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_salones'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="content">

                <h1><?php echo Idioma::e('enc_salones'); ?></h1>

                <p id="resultado-salones" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-salon" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" id="nombre-original">
                    <div class="form-grupo">
                        <label for="nombre-salon"><?php echo Idioma::e('th_nombre'); ?></label>
                        <input type="text" id="nombre-salon" minlength="2" maxlength="30" required>
                    </div>
                    <div class="form-grupo">
                        <label for="descripcion-salon"><?php echo Idioma::e('th_descripcion'); ?></label>
                        <textarea id="descripcion-salon" rows="3" maxlength="500"></textarea>
                    </div>
                    <button type="submit" id="guardar-salon"><?php echo Idioma::e('btn_agregar_salon'); ?></button>
                    <button type="button" id="cancelar-edicion-salon" class="oculta"><?php echo Idioma::e('btn_cancelar_edicion'); ?></button>
                </form>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo Idioma::e('th_nombre'); ?></th>
                                <th><?php echo Idioma::e('th_descripcion'); ?></th>
                                <th><?php echo Idioma::e('th_acciones'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="lista-salones">
                            <tr><td colspan="3"><?php echo Idioma::e('cargando_salones'); ?></td></tr>
                        </tbody>
                    </table>
                </div>

            </section>

        </main>

    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/inventario/salones.js"></script>
</body>

</html>
