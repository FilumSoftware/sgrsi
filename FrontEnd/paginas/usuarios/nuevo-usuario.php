<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorUsuarios.php';

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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title>SGRSI - Nuevo usuario</title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <?php if (ControlAcceso::puedeAtender()) { ?>
                    <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php">Mesa de Ayuda</a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php">Solicitudes</a></li>
                <li class="sidebar-item activo">Usuarios</li>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir">Cerrar sesión</a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item activo">Nuevo usuario</li>
                        <li class="topbar-item"><a href="usuarios.php">Usuarios</a></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
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
                            <label for="ci">Cédula:</label>
                            <input type="text" id="ci" name="ci" value="<?php echo v($valores['ci']); ?>" required>
                            <p class="error-mensaje" id="error-ci"<?php echo !empty($errores['ci']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['ci']) ? $errores['ci'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo v($valores['nombre']); ?>" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['nombre']) ? $errores['nombre'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="password">Contraseña:</label>
                            <input type="password" id="password" name="password" required>
                            <p class="error-mensaje" id="error-password"<?php echo !empty($errores['password']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['password']) ? $errores['password'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="confirmar-password">Repita la contraseña:</label>
                            <input type="password" id="confirmar-password" name="confirmar-password" required>
                            <p class="error-mensaje" id="error-confirmar-password"<?php echo !empty($errores['confirmar']) ? ' style="display: block;"' : ''; ?>><?php echo v(isset($errores['confirmar']) ? $errores['confirmar'] : ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="tipo-usuario">Permisos:</label>
                            <select id="tipo-usuario" name="tipo-usuario">
                                <?php foreach ($rolesValidos as $rol) { ?>
                                    <option value="<?php echo v($rol); ?>"<?php echo $valores['rol'] === $rol ? ' selected' : ''; ?>><?php echo v($rol); ?></option>
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
    <script src="../../js/validacion.js"></script>
    <script src="../../js/usuarios/nuevo-usuario.js"></script>
</body>

</html>
