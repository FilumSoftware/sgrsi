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

                <p id="resultado-ticket" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-ticket" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
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
                        </select>
                        <p class="error-mensaje" id="error-salon"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="equipo"><?php echo Idioma::e('th_equipo'); ?></label>
                        <select name="equipo" id="equipo" required>
                            <option value="">
                                <?php echo Idioma::e('opt_elegi_salon_primero'); ?>
                            </option>
                        </select>
                        <p class="error-mensaje" id="error-equipo"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="defecto"><?php echo Idioma::e('th_tipo_falla'); ?></label>
                        <select name="defecto" id="defecto" required>
                            <option value=""><?php echo Idioma::e('opt_elija_opcion'); ?></option>
                        </select>
                        <p class="error-mensaje" id="error-defecto"></p>
                    </div>
                    <div class="form-grupo oculta" id="grupo-detalle-defecto">
                        <label for="detalle-defecto"><?php echo Idioma::e('th_describi_falla'); ?></label>
                        <input type="text" name="detalle-defecto" id="detalle-defecto" maxlength="120" minlength="3">
                    </div>
                    <div class="form-grupo">
                        <label for="prioridad"><?php echo Idioma::e('th_prioridad'); ?></label>
                        <select name="prioridad" id="prioridad" required>
                            <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                <option value="<?php echo v($prioridad); ?>"><?php echo v(Idioma::t('valor_' . $prioridad)); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-prioridad"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="motivo"><?php echo Idioma::e('th_motivo'); ?></label>
                        <textarea name="motivo" id="motivo" rows="5" placeholder="<?php echo Idioma::e('ph_escriba_motivo'); ?>" required></textarea>
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
