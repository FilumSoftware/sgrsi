<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorUsoSala.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorUsoSala();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->eliminarDesdeFormulario(ResultadoGestion::campo($_POST, 'id'), Sesion::esCoordinador())
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    $aviso = $resultado['ok'] ? 'eliminado' : ($resultado['codigo'] === 403 ? 'permiso' : 'error');
    header('Location: historial-uso-sala.php?aviso=' . $aviso);
    exit;
}

$avisos = [
    'creado'    => ['exito', Idioma::t('aviso_uso_creado')],
    'guardado'  => ['exito', Idioma::t('aviso_guardado')],
    'eliminado' => ['exito', Idioma::t('aviso_registro_eliminado')],
    'permiso'   => ['error', Idioma::t('aviso_permiso_registros')],
    'ajeno'     => ['error', Idioma::t('aviso_registro_ajeno')],
    'noexiste'  => ['error', Idioma::t('aviso_registro_noexiste')],
    'error'     => ['error', Idioma::t('aviso_error_operacion')],
];

$mensaje = null;
$aviso   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$aviso])) {
    $mensaje = ['tipo' => $avisos[$aviso][0], 'texto' => $avisos[$aviso][1]];
}

try {
    if (ControlAcceso::puedeAtender()) {
        $usos = ResultadoGestion::valor($gestor->obtenerTodos());
    } else {
        $usos = ResultadoGestion::valor($gestor->obtenerPorSolicitante(Sesion::ci()));
    }
} catch (Exception $e) {
    error_log('SGRSI historial-uso-sala.php listar: ' . $e->getMessage());
    $usos = [];
    $mensaje = ['tipo' => 'error', 'texto' => 'No se pudo cargar el historial. Intentá de nuevo en unos minutos.'];
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
    <link rel="stylesheet" href="../../css/sala.css">
    <title><?php echo Idioma::e('tit_historial_uso'); ?></title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php"><?php echo Idioma::e('nav_dashboard'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_sala'); ?></li>
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
                        <li class="topbar-item"><a href="planilla-uso-sala.php"><?php echo Idioma::e('tab_planilla_uso'); ?></a></li>
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_historial_uso'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="content">

                <?php if ($mensaje) { ?>
                    <p class="error-mensaje <?php echo $mensaje['tipo'] === 'exito' ? 'mensaje-exito' : ''; ?>" style="display: block;">
                        <?php echo v($mensaje['texto']); ?>
                    </p>
                <?php } ?>
                <p id="resultado-historial" class="error-mensaje" role="status" aria-live="polite"></p>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th><?php echo Idioma::e('th_fecha'); ?></th>
                                <th><?php echo Idioma::e('th_horario'); ?></th>
                                <th><?php echo Idioma::e('th_docente'); ?></th>
                                <th><?php echo Idioma::e('th_asignatura'); ?></th>
                                <th><?php echo Idioma::e('th_grupo'); ?></th>
                                <th><?php echo Idioma::e('th_turno'); ?></th>
                                <th><?php echo Idioma::e('th_salon'); ?></th>
                                <th><?php echo Idioma::e('th_acciones'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="lista-usos" data-csrf="<?php echo v(Sesion::tokenApi()); ?>" data-coordinador="<?php echo Sesion::esCoordinador() ? '1' : '0'; ?>">
                            <?php if (empty($usos)) { ?>
                                <tr>
                                    <td colspan="8"><?php echo Idioma::e('vacio_uso_sala'); ?></td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($usos as $uso) { ?>
                                <tr>
                                    <td><a class="tabla-enlace" href="detalle-uso-sala.php?id=<?php echo urlencode($uso['id_uso']); ?>"><?php echo v(date('d/m/y', strtotime($uso['fecha']))); ?></a></td>
                                    <td><?php echo v(substr($uso['hora_inicio'], 0, 5)); ?> a <?php echo v(substr($uso['hora_fin'], 0, 5)); ?></td>
                                    <td><?php echo v($uso['nombre_docente']); ?></td>
                                    <td><?php echo v($uso['asignatura']); ?></td>
                                    <td><?php echo v($uso['grupo']); ?></td>
                                    <td><?php echo v($uso['turno']); ?></td>
                                    <td><?php echo v($uso['nombre_salon']); ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="detalle-uso-sala.php?id=<?php echo urlencode($uso['id_uso']); ?>&amp;modo=editar"><?php echo Idioma::e('btn_editar'); ?></a>

                                        <?php if (Sesion::esCoordinador()) { ?>
                                            <form action="historial-uso-sala.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                                                <input type="hidden" name="id" value="<?php echo v($uso['id_uso']); ?>">
                                                <button type="submit" class="btn-eliminar" data-confirmar="1"><?php echo Idioma::e('btn_eliminar'); ?></button>
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
    <script src="../../js/uso-sala/historial-uso-sala.js"></script>
</body>

</html>
