<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/dao/UsoSalaDAO.php';

ControlAcceso::exigirSesion('../../../index.php');

$usoSalaDAO = new UsoSalaDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!Sesion::esCoordinador()) {
        header('Location: historial-uso-sala.php?aviso=permiso');
        exit;
    }

    $id = trim((string) (isset($_POST['id']) ? $_POST['id'] : ''));

    try {
        $usoSalaDAO->eliminar($id);
        header('Location: historial-uso-sala.php?aviso=eliminado');
        exit;
    } catch (Exception $e) {
        error_log('SGRSI historial-uso-sala.php eliminar: ' . $e->getMessage());
        header('Location: historial-uso-sala.php?aviso=error');
        exit;
    }
}

$avisos = [
    'creado'    => ['exito', 'El uso de la sala se registró correctamente.'],
    'guardado'  => ['exito', 'Los cambios se guardaron.'],
    'eliminado' => ['exito', 'El registro se eliminó.'],
    'permiso'   => ['error', 'Solo el coordinador puede eliminar registros.'],
    'ajeno'     => ['error', 'Ese registro no es tuyo.'],
    'noexiste'  => ['error', 'No existe un registro con ese número.'],
    'error'     => ['error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.'],
];

$mensaje = null;
$aviso   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$aviso])) {
    $mensaje = ['tipo' => $avisos[$aviso][0], 'texto' => $avisos[$aviso][1]];
}

try {
    if (ControlAcceso::puedeAtender()) {
        $usos = $usoSalaDAO->obtenerTodos();
    } else {
        $usos = $usoSalaDAO->obtenerPorSolicitante(Sesion::ci());
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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/sala.css">
    <title>SGRSI - Historial de uso de sala</title>
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <li class="sidebar-item activo">Sala de informática</li>
                <?php if (ControlAcceso::puedeAtender()) { ?>
                    <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php">Mesa de Ayuda</a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php">Solicitudes</a></li>
                <?php if (ControlAcceso::puedeGestionarUsuarios()) { ?>
                    <li class="sidebar-item"><a href="../usuarios/nuevo-usuario.php">Usuarios</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir">Cerrar sesión</a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="planilla-uso-sala.php">Planilla de uso</a></li>
                        <li class="topbar-item activo">Historial de uso</li>
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
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Docente</th>
                                <th>Asignatura</th>
                                <th>Grupo</th>
                                <th>Turno</th>
                                <th>Salón</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usos)) { ?>
                                <tr>
                                    <td colspan="8">Todavía no hay registros de uso de sala.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($usos as $uso) { ?>
                                <tr>
                                    <td><?php echo v(date('d/m/y', strtotime($uso['fecha']))); ?></td>
                                    <td><?php echo v(substr($uso['hora_inicio'], 0, 5)); ?> a <?php echo v(substr($uso['hora_fin'], 0, 5)); ?></td>
                                    <td><?php echo v($uso['nombre_docente']); ?></td>
                                    <td><?php echo v($uso['asignatura']); ?></td>
                                    <td><?php echo v($uso['grupo']); ?></td>
                                    <td><?php echo v($uso['turno']); ?></td>
                                    <td><?php echo v($uso['nombre_salon']); ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="detalle-uso-sala.php?id=<?php echo urlencode($uso['id_uso']); ?>">Editar</a>

                                        <?php if (Sesion::esCoordinador()) { ?>
                                            <form action="historial-uso-sala.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="id" value="<?php echo v($uso['id_uso']); ?>">
                                                <button type="submit" class="btn-eliminar" data-confirmar="1">Eliminar</button>
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
    <script src="../../js/uso-sala/historial-uso-sala.js"></script>
</body>

</html>
