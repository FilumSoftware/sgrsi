<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorSolicitudes.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorSolicitudes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Sesion::verificarTokenApi(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $destino = 'error';
    } elseif (!Sesion::esCoordinador()) {
        $destino = 'permiso';
    } else {
        $resultado = $gestor->eliminar(ResultadoGestion::campo($_POST, 'id_solicitud'));
        if (!$resultado['ok']) {
            $destino = 'error';
        } else {
            $destino = $resultado['datos'] ? 'eliminada' : 'noexiste';
        }
    }
    header('Location: solicitudes.php?aviso=' . $destino);
    exit;
}

$avisos = [
    'creada'    => ['exito', Idioma::t('aviso_solicitud_creada')],
    'guardada'  => ['exito', Idioma::t('aviso_guardado')],
    'eliminada' => ['exito', Idioma::t('aviso_solicitud_eliminada')],
    'permiso'   => ['error', Idioma::t('aviso_permiso_solicitudes')],
    'ajena'     => ['error', Idioma::t('aviso_solicitud_ajena')],
    'noexiste'  => ['error', Idioma::t('aviso_solicitud_noexiste')],
    'error'     => ['error', Idioma::t('aviso_error_operacion')],
];

$mensaje = null;
$aviso   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$aviso])) {
    $mensaje = ['tipo' => $avisos[$aviso][0], 'texto' => $avisos[$aviso][1]];
}

$clasesEstado = [
    'Resuelta'   => 'bg-success',
    'En proceso' => 'bg-info text-dark',
    'Pendiente'  => 'bg-warning text-dark',
];

try {
    $solicitudes = ResultadoGestion::valor(Sesion::esTecnico()
        ? $gestor->obtenerTodos()
        : $gestor->obtenerPorSolicitante(Sesion::ci()));
} catch (Exception $e) {
    error_log('SGRSI solicitudes.php listado: ' . $e->getMessage());
    $solicitudes = [];
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
    <title><?php echo Idioma::e('tit_solicitudes'); ?></title>
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
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_listado_solicitudes'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="content">

                <p id="resultado-solicitudes" class="error-mensaje <?php echo $mensaje && $mensaje['tipo'] === 'exito' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $mensaje ? ' style="display: block;"' : ''; ?>><?php echo $mensaje ? v($mensaje['texto']) : ''; ?></p>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php echo Idioma::e('th_fecha'); ?></th>
                                <th><?php echo Idioma::e('th_salon'); ?></th>
                                <th><?php echo Idioma::e('th_motivo'); ?></th>
                                <th><?php echo Idioma::e('th_estado'); ?></th>
                                <th><?php echo Idioma::e('th_prioridad'); ?></th>
                                <th><?php echo Idioma::e('th_solicitante'); ?></th>
                                <th><?php echo Idioma::e('th_responsable'); ?></th>
                                <th><?php echo Idioma::e('th_acciones'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="lista-solicitudes" data-csrf="<?php echo v(Sesion::tokenApi()); ?>" data-coordinador="<?php echo Sesion::esCoordinador() ? '1' : '0'; ?>" data-atender="<?php echo ControlAcceso::puedeAtender() ? '1' : '0'; ?>">
                            <?php if (empty($solicitudes)) { ?>
                                <tr>
                                    <td colspan="9"><?php echo Idioma::e('js_sin_solicitudes'); ?></td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($solicitudes as $solicitud) { ?>
                                <?php
                                $url = 'detalle-solicitud.php?id=' . (int) $solicitud['id_solicitud'];
                                $marca = strtotime($solicitud['fecha_hora_alta']);
                                $atender = ControlAcceso::puedeAtender();
                                $clase = isset($clasesEstado[$solicitud['estado_solicitud']]) ? $clasesEstado[$solicitud['estado_solicitud']] : 'bg-warning text-dark';
                                ?>
                                <tr>
                                    <td><a class="tabla-enlace" href="<?php echo v($url); ?>"><?php echo (int) $solicitud['id_solicitud']; ?></a></td>
                                    <td><?php echo $marca ? date('d/m/y', $marca) : ''; ?></td>
                                    <td><?php echo v($solicitud['nombre_salon']); ?></td>
                                    <td><?php echo v($solicitud['descripcion']); ?></td>
                                    <td><span class="badge <?php echo $clase; ?>"><?php echo Idioma::e('valor_' . $solicitud['estado_solicitud']); ?></span></td>
                                    <td><?php echo Idioma::e('valor_' . $solicitud['prioridad']); ?></td>
                                    <td><?php echo v($solicitud['nombre_solicitante']); ?></td>
                                    <td><?php echo $solicitud['nombre_responsable'] !== null && $solicitud['nombre_responsable'] !== '' ? v($solicitud['nombre_responsable']) : '—'; ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="<?php echo v($url); ?><?php echo $atender ? '&amp;modo=editar' : ''; ?>"><?php echo $atender ? Idioma::e('js_atender') : Idioma::e('js_ver'); ?></a>

                                        <?php if (Sesion::esCoordinador()) { ?>
                                            <form action="solicitudes.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                                                <input type="hidden" name="id_solicitud" value="<?php echo (int) $solicitud['id_solicitud']; ?>">
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
    <script src="../../js/solicitudes/solicitudes.js"></script>
</body>

</html>
