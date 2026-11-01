<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

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

                <p id="resultado-solicitud" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-solicitud" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
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
                            <select id="salon" name="salon" required>
                                <option value=""><?php echo Idioma::e('opt_elija_opcion'); ?></option>
                            </select>
                            <p class="error-mensaje" id="error-salon"></p>
                        </div>
                        <div class="form-grupo">
                            <label for="prioridad"><?php echo Idioma::e('lbl_prioridad'); ?></label>
                            <select id="prioridad" name="prioridad" required>
                                <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                    <option value="<?php echo v($prioridad); ?>"><?php echo v(Idioma::t('valor_' . $prioridad)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-prioridad"></p>
                        </div>
                        <div class="form-grupo">
                            <label for="motivo"><?php echo Idioma::e('lbl_motivo'); ?></label>
                            <textarea id="motivo" name="motivo" rows="4" required></textarea>
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
