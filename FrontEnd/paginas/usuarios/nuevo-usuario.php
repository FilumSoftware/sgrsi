<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/dao/UsuarioDAO.php';
require_once __DIR__ . '/../../../BackEnd/models/Usuario.php';

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

$rolesValidos = ['Solicitante', 'Asistente', 'Coordinador'];
$largoMinimoClave = 8;

$usuarioDAO = new UsuarioDAO();

$errores = [];
$valores = ['ci' => '', 'nombre' => '', 'rol' => 'Solicitante'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $valores['ci']     = trim((string) (isset($_POST['ci']) ? $_POST['ci'] : ''));
    $valores['nombre'] = trim((string) (isset($_POST['nombre']) ? $_POST['nombre'] : ''));
    $valores['rol']    = trim((string) (isset($_POST['tipo-usuario']) ? $_POST['tipo-usuario'] : ''));

    $clave         = (string) (isset($_POST['password']) ? $_POST['password'] : '');
    $claveRepetida = (string) (isset($_POST['confirmar-password']) ? $_POST['confirmar-password'] : '');

    if (preg_match('/^[0-9]{8}$/', $valores['ci']) !== 1) {
        $errores['ci'] = 'La cédula son ocho dígitos, sin puntos ni guiones.';
    }

    if ($valores['nombre'] === '') {
        $errores['nombre'] = 'El nombre es obligatorio.';
    } elseif (strlen($valores['nombre']) > 60) {
        $errores['nombre'] = 'El nombre no puede superar los 60 caracteres.';
    }

    if (!in_array($valores['rol'], $rolesValidos, true)) {
        $errores['rol'] = 'Elegí un rol válido de la lista.';
    }

    if (strlen($clave) < $largoMinimoClave) {
        $errores['password'] = 'La contraseña necesita al menos ' . $largoMinimoClave . ' caracteres.';
    }

    if ($clave !== $claveRepetida) {
        $errores['confirmar'] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        try {
            if ($usuarioDAO->obtenerPorCi($valores['ci'])) {
                $errores['ci'] = 'Ya hay una cuenta registrada con esa cédula.';
            } else {
                $usuario = new Usuario($valores['ci'], $valores['nombre'], $valores['rol'], 'Activa');
                $usuarioDAO->insertar($usuario, $clave);

                header('Location: usuarios.php?aviso=creada');
                exit;
            }
        } catch (Exception $e) {
            error_log('SGRSI nuevo-usuario.php insertar: ' . $e->getMessage());
            $errores['general'] = 'No se pudo crear el usuario. Intentá de nuevo en unos minutos.';
        }
    }
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
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
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

                <form id="form-usuario" action="nuevo-usuario.php" method="post">
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
