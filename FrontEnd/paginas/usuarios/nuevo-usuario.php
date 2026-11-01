<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorUsuarios.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

$gestor = new GestorUsuarios();

$rolesValidos = ['Solicitante', 'Asistente', 'Coordinador'];


$errores = [];
$valores = ['ci' => '', 'nombre' => '', 'rol' => 'Solicitante'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores['ci'] = ResultadoGestion::campo($_POST, 'ci');
    $valores['nombre'] = ResultadoGestion::campo($_POST, 'nombre');
    $valores['rol'] = ResultadoGestion::campo($_POST, 'tipo-usuario');
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->crearDesdeFormulario($_POST)
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    if ($resultado['ok']) {
        header('Location: usuarios.php?aviso=creada');
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
    <title><?php echo Idioma::e('tit_nuevo_usuario'); ?></title>
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
                        <li class="topbar-item activo"><?php echo Idioma::e('tab_nuevo_usuario'); ?></li>
                        <li class="topbar-item"><a href="usuarios.php"><?php echo Idioma::e('nav_usuarios'); ?></a></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>
                <p id="resultado-usuario" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-usuario" action="nuevo-usuario.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <fieldset>
                        <div class="form-grupo">
                            <label for="ci"><?php echo Idioma::e('lbl_cedula'); ?></label>
                            <input type="text" id="ci" name="ci" value="<?php echo v($valores['ci']); ?>" required>
                            <p class="error-mensaje" id="error-ci"<?php echo !empty($errores['ci']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['ci']) ? $errores['ci'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="nombre"><?php echo Idioma::e('lbl_nombre'); ?></label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo v($valores['nombre']); ?>" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['nombre']) ? $errores['nombre'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="password"><?php echo Idioma::e('lbl_contrasena'); ?></label>
                            <input type="password" id="password" name="password" required>
                            <p class="error-mensaje" id="error-password"<?php echo !empty($errores['password']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['password']) ? $errores['password'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="confirmar-password"><?php echo Idioma::e('lbl_repita_contrasena'); ?></label>
                            <input type="password" id="confirmar-password" name="confirmar-password" required>
                            <p class="error-mensaje" id="error-confirmar-password"<?php echo !empty($errores['confirmar']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['confirmar']) ? $errores['confirmar'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="tipo-usuario"><?php echo Idioma::e('lbl_permisos'); ?></label>
                            <select id="tipo-usuario" name="tipo-usuario">
                                <?php foreach ($rolesValidos as $rol) { ?>
                                    <option value="<?php echo v($rol); ?>"<?php echo $valores['rol'] === $rol ? ' selected' : ''; ?>><?php echo v(Idioma::t('valor_' . $rol)); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-rol"<?php echo !empty($errores['rol']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['rol']) ? $errores['rol'] : ''); ?></p>
                        </div>

                        <input type="submit" value="Crear Cuenta">

                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/usuarios/nuevo-usuario.js"></script>
</body>

</html>
