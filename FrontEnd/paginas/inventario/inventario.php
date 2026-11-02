<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorInventario.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Asistente', 'Coordinador'], '../../../index.php');

$gestor = new GestorInventario();
$errorGeneral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = Sesion::verificarTokenApi(isset($_POST['csrf']) ? $_POST['csrf'] : '')
        ? $gestor->darDeBaja(ResultadoGestion::campo($_POST, 'id_equipo'))
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');

    if ($resultado['ok'] && $resultado['datos']) {
        header('Location: inventario.php?ok=3');
        exit;
    }

    if (!$resultado['ok']) {
        http_response_code($resultado['codigo']);
        $errorGeneral = Idioma::t($resultado['mensaje']);
    } else {
        http_response_code(404);
        $errorGeneral = Idioma::t('No existe ese equipo.');
    }
}

$avisos = [
    '1' => Idioma::t('aviso_equipo_creado'),
    '2' => Idioma::t('aviso_guardado'),
    '3' => Idioma::t('aviso_equipo_baja'),
];

$clave = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$exito = isset($avisos[$clave]) ? $avisos[$clave] : '';

try {
    $equipos = ResultadoGestion::valor($gestor->obtenerTodos());
} catch (Exception $e) {
    error_log('SGRSI inventario.php obtenerTodos: ' . $e->getMessage());
    $equipos = [];
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
    <title><?php echo Idioma::e('tit_inventario'); ?></title>
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
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_equipos'); ?></li>
                        <?php if (Sesion::esCoordinador()) { ?>
                            <li class="topbar-item"><a href="salones.php"><?php echo Idioma::e('tab_salones'); ?></a></li>
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

                <?php if ($errorGeneral !== '') { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errorGeneral); ?></p>
                <?php } ?>
                <p id="resultado-inventario" class="error-mensaje <?php echo $exito !== '' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $exito !== '' ? ' style="display: block;"' : ''; ?>><?php echo v($exito); ?></p>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th><?php echo Idioma::e('th_nombre'); ?></th>
                                <th><?php echo Idioma::e('th_extras'); ?></th>
                                <th><?php echo Idioma::e('th_salon'); ?></th>
                                <th><?php echo Idioma::e('th_estado'); ?></th>
                                <th><?php echo Idioma::e('th_acciones'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="lista-equipos" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                            <?php if (empty($equipos)) { ?>
                                <tr>
                                    <td colspan="6"><?php echo Idioma::e('vacio_equipos'); ?></td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($equipos as $equipo) { ?>
                                <?php $url = 'detalle-equipo.php?id=' . (int) $equipo['id_equipo']; ?>
                                <tr>
                                    <td><a class="tabla-enlace" href="<?php echo v($url); ?>"><?php echo (int) $equipo['id_equipo']; ?></a></td>
                                    <td><?php echo v($equipo['nombre_equipo']); ?></td>
                                    <td><?php echo v($equipo['descripcion']); ?></td>
                                    <td><?php echo v($equipo['nombre_salon']); ?></td>
                                    <td><?php echo Idioma::e('valor_' . $equipo['estado_equipo']); ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="<?php echo v($url); ?>&amp;modo=editar"><?php echo Idioma::e('btn_editar'); ?></a>

                                        <?php if ($equipo['estado_equipo'] !== 'De baja') { ?>
                                            <form action="inventario.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                                                <input type="hidden" name="id_equipo" value="<?php echo (int) $equipo['id_equipo']; ?>">
                                                <button type="submit" class="btn-eliminar"><?php echo Idioma::e('js_registrar_baja'); ?></button>
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
    <script src="../../js/inventario/inventario.js"></script>
</body>

</html>
