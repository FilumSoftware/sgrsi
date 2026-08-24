<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/dao/UsuarioDAO.php';

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

$usuarioDAO = new UsuarioDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ci     = trim((string) (isset($_POST['ci']) ? $_POST['ci'] : ''));
    $estado = trim((string) (isset($_POST['estado']) ? $_POST['estado'] : ''));

    if ($ci === Sesion::ci()) {
        header('Location: usuarios.php?aviso=propia');
        exit;
    }

    if (!in_array($estado, ['Activa', 'Inactiva'], true)) {
        header('Location: usuarios.php?aviso=estado');
        exit;
    }

    try {
        $usuarioDAO->cambiarEstado($ci, $estado);
        header('Location: usuarios.php?aviso=' . ($estado === 'Activa' ? 'activada' : 'desactivada'));
        exit;
    } catch (Exception $e) {
        error_log('SGRSI usuarios.php cambiarEstado: ' . $e->getMessage());
        header('Location: usuarios.php?aviso=error');
        exit;
    }
}

$avisos = [
    'activada'     => ['exito', 'La cuenta quedó habilitada.'],
    'desactivada'  => ['exito', 'La cuenta quedó inhabilitada.'],
    'creada'       => ['exito', 'Usuario creado correctamente.'],
    'guardada'     => ['exito', 'Los cambios se guardaron.'],
    'propia'       => ['error', 'No podés cambiar el estado de tu propia cuenta.'],
    'estado'       => ['error', 'El estado indicado no es válido.'],
    'noencontrado' => ['error', 'No existe un usuario con esa cédula.'],
    'error'        => ['error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.'],
];

$mensaje = null;
$clave   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$clave])) {
    $mensaje = ['tipo' => $avisos[$clave][0], 'texto' => $avisos[$clave][1]];
}

try {
    $usuarios = $usuarioDAO->obtenerTodos();
} catch (Exception $e) {
    error_log('SGRSI usuarios.php obtenerTodos: ' . $e->getMessage());
    $usuarios = [];
    $mensaje  = ['tipo' => 'error', 'texto' => 'No se pudo cargar el listado de usuarios. Intentá de nuevo en unos minutos.'];
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
    <title>SGRSI - Usuarios</title>
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
                        <li class="topbar-item"><a href="nuevo-usuario.php">Nuevo usuario</a></li>
                        <li class="topbar-item activo">Usuarios</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="content">

                <?php if ($mensaje) { ?>
                    <p class="error-mensaje <?php echo $mensaje['tipo'] === 'exito' ? 'mensaje-exito' : ''; ?>" style="display: block;">
                        <?php echo v($mensaje['texto']); ?>
                    </p>
                <?php } ?>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>C.I.</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)) { ?>
                                <tr>
                                    <td colspan="5">No hay usuarios registrados.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($usuarios as $usuario) { ?>
                                <?php $inactiva = $usuario['estado_cuenta'] === 'Inactiva'; ?>
                                <tr<?php echo $inactiva ? ' class="fila-desactivada"' : ''; ?>>
                                    <td><a class="tabla-enlace" href="detalle-usuario.php?ci=<?php echo urlencode($usuario['ci']); ?>"><?php echo v($usuario['ci']); ?></a></td>
                                    <td><?php echo v($usuario['nombre_usuario']); ?></td>
                                    <td><?php echo v($usuario['tipo_de_usuario']); ?></td>
                                    <td><?php echo v($usuario['estado_cuenta']); ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="detalle-usuario.php?ci=<?php echo urlencode($usuario['ci']); ?>&amp;modo=editar">Editar</a>

                                        <?php if ($usuario['ci'] === Sesion::ci()) { ?>
                                            <span class="sin-accion">Tu cuenta</span>
                                        <?php } else { ?>
                                            <form action="usuarios.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="ci" value="<?php echo v($usuario['ci']); ?>">
                                                <input type="hidden" name="estado" value="<?php echo $inactiva ? 'Activa' : 'Inactiva'; ?>">
                                                <button type="submit" class="<?php echo $inactiva ? 'btn-editar' : 'btn-eliminar'; ?>" data-confirmar="<?php echo $inactiva ? 'habilitar' : 'inhabilitar'; ?>">
                                                    <?php echo $inactiva ? 'Habilitar' : 'Inhabilitar'; ?>
                                                </button>
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
    <script src="../../js/usuarios/usuarios.js"></script>
    <script src="../../js/fila-clickeable.js"></script>
</body>

</html>
