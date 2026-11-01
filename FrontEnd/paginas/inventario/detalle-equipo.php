<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorInventario.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Asistente', 'Coordinador'], '../../../index.php');

$gestor = new GestorInventario();

$estadosValidos = ['Operativo', 'En reparación', 'En trámite de baja', 'De baja'];


$idEquipo = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idEquipo <= 0) {
    header('Location: inventario.php');
    exit;
}

$errores = [];

$modoVer = $_SERVER['REQUEST_METHOD'] !== 'POST'
    && (!isset($_GET['modo']) || $_GET['modo'] !== 'editar');

try {
    $salones = ResultadoGestion::valor($gestor->obtenerSalones());
} catch (Exception $e) {
    error_log('SGRSI detalle-equipo.php: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = Idioma::t('err_lista_salones');
}

try {
    $equipoActual = ResultadoGestion::valor($gestor->obtenerPorId($idEquipo));
} catch (Exception $e) {
    error_log('SGRSI detalle-equipo.php: ' . $e->getMessage());
    $equipoActual = false;
}

if (!$equipoActual) {
    header('Location: inventario.php');
    exit;
}

$valores = [
    'nombre'    => $equipoActual['nombre_equipo'],
    'salon'     => $equipoActual['nombre_salon'],
    'extras'    => isset($equipoActual['descripcion']) ? $equipoActual['descripcion'] : '',
    'estado'    => $equipoActual['estado_equipo'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->actualizarDesdeFormulario($idEquipo, $_POST)
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    if ($resultado['ok']) {
        header('Location: inventario.php?ok=2');
        exit;
    }
    http_response_code($resultado['codigo']);
    $errores['general'] = $resultado['mensaje'];
}

?>
<!DOCTYPE html>
<html lang="<?php echo Idioma::t('html_lang'); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title><?php echo Idioma::e('tit_detalle_equipo'); ?></title>
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
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_detalle_equipo'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1><?php echo Idioma::e('enc_detalle_equipo'); ?></h1>

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo htmlspecialchars($errores['general']); ?></p>
                <?php } ?>
                <p id="resultado-guardado" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-detalle-equipo" action="detalle-equipo.php?id=<?php echo (int) $idEquipo; ?>" method="post" data-csrf="<?php echo htmlspecialchars(Sesion::tokenApi(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(Sesion::tokenApi(), ENT_QUOTES, 'UTF-8'); ?>">
                    <fieldset<?php echo $modoVer ? ' disabled' : ''; ?>>
                        <div class="form-grupo">
                            <label for="id-equipo"><?php echo Idioma::e('lbl_id_equipo'); ?></label>
                            <input type="text" id="id-equipo" name="id-equipo" value="<?php echo (int) $idEquipo; ?>" readonly>
                        </div>

                        <div class="form-grupo">
                            <label for="nombre"><?php echo Idioma::e('lbl_nombre_equipo'); ?></label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($valores['nombre']); ?>" maxlength="100" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['nombre']) ? $errores['nombre'] : ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="salon"><?php echo Idioma::e('lbl_salon'); ?></label>
                            <select id="salon" name="salon" required>
                                <?php foreach ($salones as $salon) { ?>
                                    <option value="<?php echo htmlspecialchars($salon['nombre_salon']); ?>"
                                        <?php echo $salon['nombre_salon'] === $valores['salon'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($salon['nombre_salon']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"<?php echo !empty($errores['salon']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="extras"><?php echo Idioma::e('lbl_extras'); ?></label>
                            <input type="text" id="extras" name="extras" value="<?php echo htmlspecialchars($valores['extras']); ?>">
                        </div>

                        <div class="form-grupo">
                            <label for="estado"><?php echo Idioma::e('lbl_estado_actual'); ?></label>
                            <select id="estado" name="estado">
                                <?php foreach ($estadosValidos as $estado) { ?>
                                    <option value="<?php echo htmlspecialchars(Idioma::t('valor_' . $estado)); ?>"
                                        <?php echo $estado === $valores['estado'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(Idioma::t('valor_' . $estado)); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-estado"<?php echo !empty($errores['estado']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['estado']) ? $errores['estado'] : ''); ?></p>
                        </div>

                        <?php if ($modoVer) { ?>
                            <a class="btn-editar-form" href="detalle-equipo.php?id=<?php echo (int) $idEquipo; ?>&amp;modo=editar"><?php echo Idioma::e('btn_editar'); ?></a>
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
    <script src="../../js/inventario/detalle-equipo.js"></script>
</body>

</html>
