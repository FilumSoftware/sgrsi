<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/dao/UsuarioDAO.php';
require_once __DIR__ . '/../../../BackEnd/models/Usuario.php';

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

const ROLES_VALIDOS   = ['Solicitante', 'Asistente', 'Coordinador'];
const ESTADOS_VALIDOS = ['Activa', 'Inactiva'];

$usuarioDAO = new UsuarioDAO();

$ci = trim((string) ($_POST['ci'] ?? $_GET['ci'] ?? ''));

if ($ci === '') {
    header('Location: usuarios.php?aviso=noencontrado');
    exit;
}

try {
    $fila = $usuarioDAO->obtenerPorCi($ci);
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

$errores = [];
$valores = [
    'nombre' => $fila['nombre_usuario'],
    'rol'    => $fila['tipo_de_usuario'],
    'estado' => $fila['estado_cuenta'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $valores['nombre'] = trim((string) ($_POST['nombre'] ?? ''));

    // Sobre la cuenta propia solo se edita el nombre: cambiarse el rol o el
    // estado dejaría al coordinador afuera del sistema.
    $valores['rol']    = $esPropia ? $fila['tipo_de_usuario'] : trim((string) ($_POST['tipo-usuario'] ?? ''));
    $valores['estado'] = $esPropia ? $fila['estado_cuenta']   : trim((string) ($_POST['estado'] ?? ''));

    if ($valores['nombre'] === '') {
        $errores['nombre'] = 'El nombre es obligatorio.';
    } elseif (strlen($valores['nombre']) > 60) {
        $errores['nombre'] = 'El nombre no puede superar los 60 caracteres.';
    }

    if (!in_array($valores['rol'], ROLES_VALIDOS, true)) {
        $errores['rol'] = 'Elegí un rol válido de la lista.';
    }

    if (!in_array($valores['estado'], ESTADOS_VALIDOS, true)) {
        $errores['estado'] = 'Elegí un estado válido de la lista.';
    }

    if (empty($errores)) {
        try {
            $usuario = new Usuario($ci, $valores['nombre'], $valores['rol'], $valores['estado']);
            $usuarioDAO->actualizar($usuario);

            header('Location: usuarios.php?aviso=guardada');
            exit;
        } catch (Exception $e) {
            error_log('SGRSI detalle-usuario.php actualizar: ' . $e->getMessage());
            $errores['general'] = 'No se pudieron guardar los cambios. Intentá de nuevo en unos minutos.';
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
    <title>SGRSI - Detalle de usuario</title>
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
                        <li class="topbar-item"><a href="nuevo-usuario.php">Nuevo usuario</a></li>
                        <li class="topbar-item"><a href="usuarios.php">Usuarios</a></li>
                        <li class="topbar-item activo">Detalle de usuario</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1>Detalle de Usuario</h1>

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>

                <?php if ($esPropia) { ?>
                    <p class="error-mensaje" style="display: block;">Es tu propia cuenta: solo podés cambiar el nombre.</p>
                <?php } ?>

                <form id="form-detalle-usuario" action="detalle-usuario.php" method="post">
                    <fieldset>
                        <input type="hidden" name="ci" value="<?php echo v($ci); ?>">

                        <div class="form-grupo">
                            <label for="ci-visible">Cédula:</label>
                            <input type="text" id="ci-visible" value="<?php echo v($ci); ?>" disabled>
                        </div>
                        <div class="form-grupo">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo v($valores['nombre']); ?>" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo v($errores['nombre'] ?? ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="tipo-usuario">Permisos:</label>
                            <select id="tipo-usuario" name="tipo-usuario"<?php echo $esPropia ? ' disabled' : ''; ?>>
                                <?php foreach (ROLES_VALIDOS as $rol) { ?>
                                    <option value="<?php echo v($rol); ?>"<?php echo $valores['rol'] === $rol ? ' selected' : ''; ?>><?php echo v($rol); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-rol"<?php echo !empty($errores['rol']) ? ' style="display: block;"' : ''; ?>><?php echo v($errores['rol'] ?? ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="estado">Estado de la cuenta:</label>
                            <select id="estado" name="estado"<?php echo $esPropia ? ' disabled' : ''; ?>>
                                <?php foreach (ESTADOS_VALIDOS as $estado) { ?>
                                    <option value="<?php echo v($estado); ?>"<?php echo $valores['estado'] === $estado ? ' selected' : ''; ?>><?php echo v($estado); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-estado"<?php echo !empty($errores['estado']) ? ' style="display: block;"' : ''; ?>><?php echo v($errores['estado'] ?? ''); ?></p>
                        </div>

                        <button type="submit" id="btn-guardar">Guardar Cambios</button>

                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/usuarios/detalle-usuario.js"></script>
</body>

</html>
