<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorTickets.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorTickets();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sesion::verificarTokenApi(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $destino = 'error';
    } elseif (!Sesion::esCoordinador()) {
        $destino = 'permiso';
    } else {
        $resultado = $gestor->eliminar(ResultadoGestion::campo($_POST, 'id_ticket'));
        if (!$resultado['ok']) {
            $destino = 'error';
        } else {
            $destino = $resultado['datos'] ? 'eliminado' : 'noexiste';
        }
    }
    header('Location: mesa-de-ayuda.php?aviso=' . $destino);
    exit;
}

$avisos = [
    'creado'    => ['exito', Idioma::t('aviso_ticket_creado')],
    'guardado'  => ['exito', Idioma::t('aviso_guardado')],
    'eliminado' => ['exito', Idioma::t('aviso_ticket_eliminado')],
    'permiso'   => ['error', Idioma::t('aviso_permiso_tickets')],
    'ajeno'     => ['error', Idioma::t('aviso_ticket_ajeno')],
    'noexiste'  => ['error', Idioma::t('aviso_ticket_noexiste')],
    'error'     => ['error', Idioma::t('aviso_error_operacion')],
];

$mensaje = null;
$aviso   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$aviso])) {
    $mensaje = ['tipo' => $avisos[$aviso][0], 'texto' => $avisos[$aviso][1]];
}

$clasesEstado = [
    'Cancelado'  => 'bg-secondary',
    'Resuelto'   => 'bg-success',
    'En proceso' => 'bg-info text-dark',
    'Pendiente'  => 'bg-warning text-dark',
];

try {
    $tickets = ResultadoGestion::valor(Sesion::esTecnico()
        ? $gestor->obtenerTodos()
        : $gestor->obtenerPorSolicitante(Sesion::ci()));
} catch (Exception $e) {
    error_log('SGRSI mesa-de-ayuda.php listado: ' . $e->getMessage());
    $tickets = [];
    $mensaje = ['tipo' => 'error', 'texto' => Idioma::t('aviso_error_operacion')];
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
    <title><?php echo Idioma::e('tit_mesa'); ?></title>
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
                        <li class="topbar-item"><a href="nuevo-ticket.php"><?php echo Idioma::e('tab_nuevo_ticket'); ?></a></li>
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_historial_tickets'); ?></li>
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

            <section class="content">

                <p id="resultado-tickets" class="error-mensaje <?php echo $mensaje && $mensaje['tipo'] === 'exito' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $mensaje ? ' style="display: block;"' : ''; ?>><?php echo $mensaje ? v($mensaje['texto']) : ''; ?></p>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php echo Idioma::e('th_fecha'); ?></th>
                                <th><?php echo Idioma::e('th_hora'); ?></th>
                                <th><?php echo Idioma::e('th_salon'); ?></th>
                                <th><?php echo Idioma::e('th_equipo'); ?></th>
                                <th><?php echo Idioma::e('th_motivo'); ?></th>
                                <th><?php echo Idioma::e('th_estado'); ?></th>
                                <th><?php echo Idioma::e('th_acciones'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="lista-tickets" data-csrf="<?php echo v(Sesion::tokenApi()); ?>" data-coordinador="<?php echo Sesion::esCoordinador() ? '1' : '0'; ?>" data-atender="<?php echo ControlAcceso::puedeAtender() ? '1' : '0'; ?>">
                            <?php if (empty($tickets)) { ?>
                                <tr>
                                    <td colspan="8"><?php echo Idioma::e('dash_sin_tickets'); ?></td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($tickets as $ticket) { ?>
                                <?php
                                $url = 'detalle-ticket.php?id=' . (int) $ticket['id_ticket'];
                                $marca = strtotime($ticket['fecha_hora_alta']);
                                $atender = ControlAcceso::puedeAtender() && $ticket['estado_ticket'] !== 'Cancelado';
                                $clase = isset($clasesEstado[$ticket['estado_ticket']]) ? $clasesEstado[$ticket['estado_ticket']] : 'bg-warning text-dark';
                                $vencido = !empty($ticket['fuera_de_plazo']);
                                ?>
                                <tr<?php echo $vencido ? ' class="fila-ticket-vencido"' : ''; ?>>
                                    <td><a class="tabla-enlace" href="<?php echo v($url); ?>"><?php echo (int) $ticket['id_ticket']; ?></a></td>
                                    <td><?php echo $marca ? date('d/m/y', $marca) : ''; ?></td>
                                    <td><?php echo $marca ? date('H:i', $marca) : ''; ?></td>
                                    <td><?php echo v($ticket['nombre_salon']); ?></td>
                                    <td><?php echo v($ticket['nombre_equipo']); ?></td>
                                    <td>
                                        <?php if ($ticket['tipo_de_defecto'] === 'Otros' && !empty($ticket['detalle_defecto'])) { ?>
                                            <?php echo Idioma::e('js_otros_prefijo') . v($ticket['detalle_defecto']); ?>
                                        <?php } else { ?>
                                            <?php echo v($ticket['tipo_de_defecto']); ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $clase; ?>"><?php echo Idioma::e('valor_' . $ticket['estado_ticket']); ?></span>
                                        <?php if ($vencido) { ?>
                                            <span class="aviso-plazo-vencido"><?php echo Idioma::e('js_fuera_de_plazo'); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="<?php echo v($url); ?><?php echo $atender ? '&amp;modo=editar' : ''; ?>"><?php echo $atender ? Idioma::e('js_atender') : Idioma::e('js_ver'); ?></a>

                                        <?php if (Sesion::esCoordinador()) { ?>
                                            <form action="mesa-de-ayuda.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                                                <input type="hidden" name="id_ticket" value="<?php echo (int) $ticket['id_ticket']; ?>">
                                                <button type="submit" class="btn-eliminar"><?php echo Idioma::e('btn_eliminar'); ?></button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/mesa-de-ayuda/mesa-de-ayuda.js"></script>
</body>

</html>
