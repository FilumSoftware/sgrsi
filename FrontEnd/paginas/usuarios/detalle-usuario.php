<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorUsuarios.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

$gestor = new GestorUsuarios();

$rolesValidos = ['Solicitante', 'Asistente', 'Coordinador'];
$estadosValidos = ['Activa', 'Inactiva'];


$ci = '';

if (isset($_POST['ci'])) {
    $ci = trim((string) $_POST['ci']);
} elseif (isset($_GET['ci'])) {
    $ci = trim((string) $_GET['ci']);
}

if ($ci === '') {
    header('Location: usuarios.php?aviso=noencontrado');
    exit;
}

try {
    $fila = ResultadoGestion::valor($gestor->obtenerPorCi($ci));
} catch (Exception $e) {
    error_log('SGRSI detalle-usuario.php obtenerPorCi: ' . $e->getMessage());
    header('Location: usuarios.php?aviso=error');
    exit;
}

if (!$fila) {
    header('Location: usuarios.php?aviso=noencontrado');
    exit;
}

$esPropia = $ci === Sesion::ci();

$modoVer = $_SERVER['REQUEST_METHOD'] !== 'POST'
    && (!isset($_GET['modo']) || $_GET['modo'] !== 'editar');

$errores = [];
$valores = [
    'nombre' => $fila['nombre_usuario'],
    'rol'    => $fila['tipo_de_usuario'],
    'estado' => $fila['estado_cuenta'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores['nombre'] = ResultadoGestion::campo($_POST, 'nombre');
    $valores['rol'] = $esPropia ? $fila['tipo_de_usuario'] : ResultadoGestion::campo($_POST, 'tipo-usuario');
    $valores['estado'] = $esPropia ? $fila['estado_cuenta'] : ResultadoGestion::campo($_POST, 'estado');
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->actualizarDesdeFormulario($ci, $_POST, Sesion::ci())
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    if ($resultado['ok']) {
        header('Location: usuarios.php?aviso=guardada');
        exit;
    }
    http_response_code($resultado['codigo']);
    $errores['general'] = $resultado['mensaje'];
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
    <title><?php echo Idioma::e('tit_detalle_usuario'); ?></title>
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
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php"><?php echo Idioma::e('nav_solicitudes'); ?></a></li>
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_usuarios'); ?></li>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir"><?php echo Idioma::e('nav_cerrar_sesion'); ?></a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="nuevo-usuario.php"><?php echo Idioma::e('tab_nuevo_usuario'); ?></a></li>
                        <li class="topbar-item"><a href="usuarios.php"><?php echo Idioma::e('nav_usuarios'); ?></a></li>
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_detalle_usuario'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1><?php echo Idioma::e('enc_detalle_usuario'); ?></h1>

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>

                <?php if ($esPropia) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo Idioma::e('nota_cuenta_propia'); ?></p>
                <?php } ?>
                <p id="resultado-guardado" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-detalle-usuario" action="detalle-usuario.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <fieldset<?php echo $modoVer ? ' disabled' : ''; ?>>
                        <input type="hidden" name="ci" value="<?php echo v($ci); ?>">

                        <div class="form-grupo">
                            <label for="ci-visible"><?php echo Idioma::e('lbl_cedula'); ?></label>
                            <input type="text" id="ci-visible" value="<?php echo v($ci); ?>" disabled>
                        </div>
                        <div class="form-grupo">
                            <label for="nombre"><?php echo Idioma::e('lbl_nombre'); ?></label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo v($valores['nombre']); ?>" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['nombre']) ? $errores['nombre'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="tipo-usuario"><?php echo Idioma::e('lbl_permisos'); ?></label>
                            <select id="tipo-usuario" name="tipo-usuario"<?php echo $esPropia ? ' disabled' : ''; ?>>
                                <?php foreach ($rolesValidos as $rol) { ?>
                                    <option value="<?php echo v($rol); ?>"<?php echo $valores['rol'] === $rol ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $rol)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-rol"<?php echo !empty($errores['rol']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['rol']) ? $errores['rol'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="estado"><?php echo Idioma::e('lbl_estado_cuenta'); ?></label>
                            <select id="estado" name="estado"<?php echo $esPropia ? ' disabled' : ''; ?>>
                                <?php foreach ($estadosValidos as $estado) { ?>
                                    <option value="<?php echo v($estado); ?>"<?php echo $valores['estado'] === $estado ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $estado)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-estado"<?php echo !empty($errores['estado']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['estado']) ? $errores['estado'] : ''); ?></p>
                        </div>

                        <?php if ($modoVer) { ?>
                            <a class="btn-editar-form" href="detalle-usuario.php?ci=<?php echo urlencode($ci); ?>&amp;modo=editar"><?php echo Idioma::e('btn_editar'); ?></a>
                        <?php } else { ?>
                            <button type="submit" id="btn-guardar"><?php echo Idioma::e('btn_guardar_cambios_may'); ?></button>
                        <?php } ?>

                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/usuarios/detalle-usuario.js"></script>
</body>

</html>
