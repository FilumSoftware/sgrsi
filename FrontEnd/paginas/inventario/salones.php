<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorSalones.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

$gestor = new GestorSalones();
$valores = ['nombre_salon' => '', 'descripcion' => '', 'nombre_original' => ''];
$errorGeneral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }
    $accion = ResultadoGestion::campo($_POST, 'accion');
    $editaba = $valores['nombre_original'] !== '';

    if (!Sesion::verificarTokenApi(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $resultado = ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    } elseif ($accion === 'eliminar') {
        $resultado = $gestor->eliminar($valores['nombre_salon']);
    } elseif ($editaba) {
        $resultado = $gestor->actualizar($valores['nombre_original'], $valores['descripcion']);
    } else {
        $resultado = $gestor->insertar($valores['nombre_salon'], $valores['descripcion']);
    }

    if ($resultado['ok']) {
        $aviso = $accion === 'eliminar' ? 'eliminado' : ($editaba ? 'guardado' : 'creado');
        header('Location: salones.php?aviso=' . $aviso);
        exit;
    }

    http_response_code($resultado['codigo']);
    $errorGeneral = Idioma::t($resultado['mensaje']);
}

$avisos = [
    'creado'    => Idioma::t('aviso_salon_creado'),
    'guardado'  => Idioma::t('aviso_guardado'),
    'eliminado' => Idioma::t('aviso_salon_eliminado'),
];

$clave = isset($_GET['aviso']) ? $_GET['aviso'] : '';
$exito = isset($avisos[$clave]) ? $avisos[$clave] : '';

try {
    $salones = ResultadoGestion::valor($gestor->obtenerTodos());
} catch (Exception $e) {
    error_log('SGRSI salones.php obtenerTodos: ' . $e->getMessage());
    $salones = [];
    $errorGeneral = Idioma::t('aviso_error_operacion');
}

$editando = $valores['nombre_original'] !== '';

if (!$editando && isset($_GET['editar'])) {
    foreach ($salones as $salon) {
        if ($salon['nombre_salon'] === $_GET['editar']) {
            $valores['nombre_original'] = $salon['nombre_salon'];
            $valores['nombre_salon'] = $salon['nombre_salon'];
            $valores['descripcion'] = (string) $salon['descripcion'];
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

                <?php if ($errorGeneral !== '') { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errorGeneral); ?></p>
                <?php } ?>
                <p id="resultado-salones" class="error-mensaje <?php echo $exito !== '' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $exito !== '' ? ' style="display: block;"' : ''; ?>><?php echo v($exito); ?></p>

                <form id="form-salon" action="salones.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" id="nombre-original" name="nombre_original" value="<?php echo v($valores['nombre_original']); ?>">
                    <div class="form-grupo">
                        <label for="nombre-salon"><?php echo Idioma::e('th_nombre'); ?></label>
                        <input type="text" id="nombre-salon" name="nombre_salon" value="<?php echo v($valores['nombre_salon']); ?>" minlength="2" maxlength="30" required<?php echo $editando ? ' readonly' : ''; ?>>
                    </div>
                    <div class="form-grupo">
                        <label for="descripcion-salon"><?php echo Idioma::e('th_descripcion'); ?></label>
                        <textarea id="descripcion-salon" name="descripcion" rows="3" maxlength="500"><?php echo v($valores['descripcion']); ?></textarea>
                    </div>
                    <button type="submit" id="guardar-salon" name="accion" value="guardar"><?php echo $editando ? Idioma::e('btn_guardar_cambios') : Idioma::e('btn_agregar_salon'); ?></button>
                    <a id="cancelar-edicion-salon" class="btn-editar<?php echo $editando ? '' : ' oculta'; ?>" href="salones.php"><?php echo Idioma::e('btn_cancelar_edicion'); ?></a>
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
                            <?php if (empty($salones)) { ?>
                                <tr>
                                    <td colspan="3"><?php echo Idioma::e('vacio_salones'); ?></td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($salones as $salon) { ?>
                                <tr>
                                    <td><?php echo v($salon['nombre_salon']); ?></td>
                                    <td><?php echo v($salon['descripcion']); ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="salones.php?editar=<?php echo urlencode($salon['nombre_salon']); ?>"><?php echo Idioma::e('btn_editar'); ?></a>
                                        <form action="salones.php" method="post" class="form-en-linea">
                                            <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                                            <input type="hidden" name="nombre_salon" value="<?php echo v($salon['nombre_salon']); ?>">
                                            <button type="submit" name="accion" value="eliminar" class="btn-eliminar"><?php echo Idioma::e('btn_eliminar'); ?></button>
                                        </form>
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
    <script src="../../js/inventario/salones.js"></script>
</body>

</html>
