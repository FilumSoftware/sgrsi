<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/logica/Resumen.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

$cuantosTickets = 5;

$mensaje = null;

$tarjetas = [
    'tickets'     => 0,
    'equipos'     => 0,
    'solicitudes' => 0,
    'usos'        => 0,
];

$tickets = [];

try {
    $resumen = new Resumen();

    $tarjetas['tickets']     = $resumen->ticketsAbiertos();
    $tarjetas['equipos']     = $resumen->equiposOperativos();
    $tarjetas['solicitudes'] = $resumen->solicitudesPendientes();
    $tarjetas['usos']        = $resumen->registrosDeLaboratorio();

    $tickets = ControlAcceso::puedeAtender()
        ? $resumen->ultimosTickets($cuantosTickets)
        : $resumen->ultimosTickets($cuantosTickets, Sesion::ci());

} catch (Exception $e) {
    error_log('SGRSI dashboard.php: ' . $e->getMessage());
    $mensaje = Idioma::t('dash_error_resumen');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <title><?php echo Idioma::e('dash_titulo'); ?></title>
</head>

<body>
    <div class="layout">
        <nav class="sidebar">
            <ul>
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_dashboard'); ?></li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php"><?php echo Idioma::e('nav_sala'); ?></a></li>
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
                        <li class="topbar-item activo"><?php echo Idioma::e('dash_resumen_general'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <div class="content">
                <h1 class="saludo"><?php echo Idioma::e('dash_saludo'); ?> <?php echo v(Sesion::nombre()); ?></h1>
                <p class="saludo-sub"><?php echo Idioma::e('dash_resumen_sistema'); ?> · <?php echo v(Idioma::t('valor_' . Sesion::rol())); ?></p>

                <?php if ($mensaje !== null) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($mensaje); ?></p>
                <?php } ?>

                <section class="tarjetas">
                    <div class="tarjeta tarjeta-violeta">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['tickets']); ?></p>
                        <p class="tarjeta-label"><?php echo Idioma::e('dash_tickets_abiertos'); ?></p>
                    </div>
                    <div class="tarjeta tarjeta-lila">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['equipos']); ?></p>
                        <p class="tarjeta-label"><?php echo Idioma::e('dash_equipos_operativos'); ?></p>
                    </div>
                    <div class="tarjeta tarjeta-naranja">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['solicitudes']); ?></p>
                        <p class="tarjeta-label"><?php echo Idioma::e('dash_solicitudes_pendientes'); ?></p>
                    </div>
                    <div class="tarjeta tarjeta-bordo">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['usos']); ?></p>
                        <p class="tarjeta-label"><?php echo Idioma::e('dash_registros_laboratorio'); ?></p>
                    </div>
                </section>

                <section class="tabla-contenedor">
                    <h2 class="tabla-titulo"><?php echo Idioma::e('dash_ultimos_tickets'); ?></h2>
                    <div class="tabla-wrapper">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th><?php echo Idioma::e('th_numero'); ?></th>
                                    <th><?php echo Idioma::e('th_solicitante'); ?></th>
                                    <th><?php echo Idioma::e('th_equipo'); ?></th>
                                    <th><?php echo Idioma::e('th_descripcion'); ?></th>
                                    <th><?php echo Idioma::e('th_estado'); ?></th>
                                    <th><?php echo Idioma::e('th_fecha'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tickets)) { ?>
                                    <tr>
                                        <td colspan="6"><?php echo Idioma::e('dash_sin_tickets'); ?></td>
                                    </tr>
                                <?php } ?>

                                <?php foreach ($tickets as $ticket) { ?>
                                    <tr>
                                        <td><a class="tabla-enlace" href="../mesa-de-ayuda/detalle-ticket.php?id=<?php echo urlencode($ticket['id_ticket']); ?>"><?php echo v($ticket['id_ticket']); ?></a></td>
                                        <td><?php echo v($ticket['nombre_solicitante']); ?></td>
                                        <td><?php echo v($ticket['nombre_equipo']); ?></td>
                                        <td><?php echo v($ticket['tipo_de_defecto']); ?></td>
                                        <td><span class="badge <?php echo v(Dominio::claseDelBadge($ticket['estado_ticket'])); ?>"><?php echo v(Idioma::t('valor_' . $ticket['estado_ticket'])); ?></span></td>
                                        <td><?php echo v(date('d/m/Y', strtotime($ticket['fecha_hora_alta']))); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="metricas-extendidas" aria-labelledby="titulo-metricas">
                    <div class="metricas-encabezado">
                        <h2 id="titulo-metricas" class="tabla-titulo"><?php echo Idioma::e('dash_metricas_titulo'); ?></h2>
                        <button type="button" id="actualizar-metricas" class="btn-editar"><?php echo Idioma::e('dash_actualizar_metricas'); ?></button>
                    </div>
                    <p id="estado-metricas" role="status" aria-live="polite"><?php echo Idioma::e('dash_cargando_metricas'); ?></p>

                    <div class="metricas-grid">
                        <div class="tabla-contenedor">
                            <h3 class="tabla-titulo"><?php echo Idioma::e('dash_equipos_fallados'); ?></h3>
                            <p class="metrica-nota"><?php echo Idioma::e('dash_nota_fallados'); ?></p>
                            <div class="tabla-wrapper">
                                <table class="tabla">
                                    <thead><tr><th><?php echo Idioma::e('th_equipo'); ?></th><th><?php echo Idioma::e('th_salon'); ?></th><th><?php echo Idioma::e('th_fallas'); ?></th></tr></thead>
                                    <tbody id="equipos-mas-fallados"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tabla-contenedor">
                            <h3 class="tabla-titulo"><?php echo Idioma::e('dash_solicitudes_por_mes'); ?></h3>
                            <p class="metrica-nota"><?php echo Idioma::e('dash_nota_meses'); ?></p>
                            <div class="tabla-wrapper">
                                <table class="tabla">
                                    <thead><tr><th><?php echo Idioma::e('th_mes'); ?></th><th><?php echo Idioma::e('th_solicitudes'); ?></th></tr></thead>
                                    <tbody id="solicitudes-por-mes"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <p class="metrica-promedio"><?php echo Idioma::e('dash_promedio_resolucion'); ?> <strong id="promedio-resolucion">—</strong></p>
                    <p class="metrica-nota"><?php echo Idioma::e('dash_nota_promedio'); ?></p>
                </section>

            </div>
        </main>

    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/fila-clickeable.js"></script>
    <script src="../../js/dashboard/dashboard.js"></script>
</body>

</html>
