<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorTiposIncidencia.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Asistente', 'Coordinador'], '../../../index.php');

$gestor = new GestorTiposIncidencia();
$valores = ['nombre' => '', 'estado' => 'Activo', 'nombre_original' => ''];
$errorGeneral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }
    $accion = ResultadoGestion::campo($_POST, 'accion');
    $editaba = $valores['nombre_original'] !== '';

    if (!Sesion::verificarTokenApi(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $resultado = ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    } elseif ($accion === 'inactivar') {
        $resultado = $gestor->desactivar($valores['nombre']);
    } elseif ($accion === 'activar') {
        $resultado = $gestor->actualizar($valores['nombre'], $valores['nombre'], 'Activo');
    } elseif ($editaba) {
        $resultado = $gestor->actualizar($valores['nombre_original'], $valores['nombre'], $valores['estado']);
    } else {
        $resultado = $gestor->insertar($valores['nombre']);
    }

    if ($resultado['ok']) {
        if ($accion === 'inactivar') {
            $aviso = 'inactivado';
        } elseif ($accion === 'activar') {
            $aviso = 'activado';
        } else {
            $aviso = $editaba ? 'guardado' : 'creado';
        }
        header('Location: tipos-incidencia.php?aviso=' . $aviso);
        exit;
    }

    http_response_code($resultado['codigo']);
    $errorGeneral = Idioma::t($resultado['mensaje']);
}

$avisos = [
    'creado'     => Idioma::t('aviso_tipo_creado'),
    'guardado'   => Idioma::t('aviso_guardado'),
    'activado'   => Idioma::t('aviso_tipo_activado'),
    'inactivado' => Idioma::t('aviso_tipo_inactivado'),
];

$clave = isset($_GET['aviso']) ? $_GET['aviso'] : '';
$exito = isset($avisos[$clave]) ? $avisos[$clave] : '';

try {
    $tipos = ResultadoGestion::valor($gestor->obtenerTodos());
} catch (Exception $e) {
    error_log('SGRSI tipos-incidencia.php obtenerTodos: ' . $e->getMessage());
    $tipos = [];
    $errorGeneral = Idioma::t('aviso_error_operacion');
}

$editando = $valores['nombre_original'] !== '';

if (!$editando && isset($_GET['editar'])) {
    foreach ($tipos as $tipo) {
        if ($tipo['nombre'] === $_GET['editar']) {
            $valores['nombre_original'] = $tipo['nombre'];
            $valores['nombre'] = $tipo['nombre'];
            $valores['estado'] = $tipo['estado'];
            $editando = true;
            break;
        }
    }
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
                <?php if ($errorGeneral !== '') { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errorGeneral); ?></p>
                <?php } ?>
                <p id="resultado-tipos" class="error-mensaje <?php echo $exito !== '' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $exito !== '' ? ' style="display: block;"' : ''; ?>><?php echo v($exito); ?></p>
                <form id="form-tipo" action="tipos-incidencia.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" id="nombre-original" name="nombre_original" value="<?php echo v($valores['nombre_original']); ?>">
                    <div class="form-grupo">
                        <label for="nombre-tipo"><?php echo Idioma::e('th_nombre'); ?></label>
                        <input type="text" id="nombre-tipo" name="nombre" value="<?php echo v($valores['nombre']); ?>" minlength="3" maxlength="80" required>
                    </div>
                    <div class="form-grupo">
                        <label for="estado-tipo"><?php echo Idioma::e('th_estado'); ?></label>
                        <select id="estado-tipo" name="estado"<?php echo $editando ? '' : ' disabled'; ?>>
                            <option value="Activo"<?php echo $valores['estado'] === 'Activo' ? ' selected' : ''; ?>><?php echo Idioma::e('valor_Activo'); ?></option>
                            <option value="Inactivo"<?php echo $valores['estado'] === 'Inactivo' ? ' selected' : ''; ?>><?php echo Idioma::e('valor_Inactivo'); ?></option>
                        </select>
                    </div>
                    <button type="submit" id="guardar-tipo" name="accion" value="guardar"><?php echo $editando ? Idioma::e('btn_guardar_cambios') : Idioma::e('btn_agregar_tipo'); ?></button>
                    <a id="cancelar-edicion-tipo" class="btn-editar<?php echo $editando ? '' : ' oculta'; ?>" href="tipos-incidencia.php"><?php echo Idioma::e('btn_cancelar_edicion'); ?></a>
                </form>
                <div class="tabla-wrapper">
                    <table>
                        <thead><tr><th><?php echo Idioma::e('th_nombre'); ?></th><th><?php echo Idioma::e('th_estado'); ?></th><th><?php echo Idioma::e('th_acciones'); ?></th></tr></thead>
                        <tbody id="lista-tipos">
                            <?php if (empty($tipos)) { ?>
                                <tr>
                                    <td colspan="3"><?php echo Idioma::e('vacio_tipos'); ?></td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($tipos as $tipo) { ?>
                                <?php $activo = $tipo['estado'] === 'Activo'; ?>
                                <tr>
                                    <td><?php echo v($tipo['nombre']); ?></td>
                                    <td><?php echo Idioma::e('valor_' . $tipo['estado']); ?></td>
                                    <td class="acciones">
                                        <?php if ($tipo['nombre'] === 'Otros') { ?>
                                            <span class="sin-accion"><?php echo Idioma::e('js_siempre_activo'); ?></span>
                                        <?php } else { ?>
                                            <a class="btn-editar" href="tipos-incidencia.php?editar=<?php echo urlencode($tipo['nombre']); ?>"><?php echo Idioma::e('btn_editar'); ?></a>
                                            <form action="tipos-incidencia.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                                                <input type="hidden" name="nombre" value="<?php echo v($tipo['nombre']); ?>">
                                                <button type="submit" name="accion" value="<?php echo $activo ? 'inactivar' : 'activar'; ?>" class="<?php echo $activo ? 'btn-eliminar' : 'btn-editar'; ?>"><?php echo $activo ? Idioma::e('js_inactivar') : Idioma::e('js_activar'); ?></button>
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
    <script src="../../js/mesa-de-ayuda/tipos-incidencia.js"></script>
</body>
</html>
