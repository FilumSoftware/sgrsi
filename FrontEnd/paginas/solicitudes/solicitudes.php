<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/dao/SolicitudDAO.php';

ControlAcceso::exigirSesion('../../../index.php');

$solicitudDAO = new SolicitudDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!Sesion::esCoordinador()) {
        header('Location: solicitudes.php?aviso=permiso');
        exit;
    }

    $id = trim((string) (isset($_POST['id']) ? $_POST['id'] : ''));

    try {
        $solicitudDAO->eliminar($id);
        header('Location: solicitudes.php?aviso=eliminada');
        exit;
    } catch (Exception $e) {
        error_log('SGRSI solicitudes.php eliminar: ' . $e->getMessage());
        header('Location: solicitudes.php?aviso=error');
        exit;
    }
}

$avisos = [
    'creada'    => ['exito', 'La solicitud se registró correctamente.'],
    'guardada'  => ['exito', 'Los cambios se guardaron.'],
    'eliminada' => ['exito', 'La solicitud se eliminó.'],
    'permiso'   => ['error', 'Solo el coordinador puede eliminar solicitudes.'],
    'ajena'     => ['error', 'Esa solicitud no es tuya.'],
    'noexiste'  => ['error', 'No existe una solicitud con ese número.'],
    'error'     => ['error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.'],
];

$mensaje = null;
$aviso   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$aviso])) {
    $mensaje = ['tipo' => $avisos[$aviso][0], 'texto' => $avisos[$aviso][1]];
}

try {
    if (ControlAcceso::puedeAtender()) {
        $solicitudes = $solicitudDAO->obtenerTodos();
    } else {
        $solicitudes = $solicitudDAO->obtenerPorSolicitante(Sesion::ci());
    }
} catch (Exception $e) {
    error_log('SGRSI solicitudes.php listar: ' . $e->getMessage());
    $solicitudes = [];
    $mensaje = ['tipo' => 'error', 'texto' => 'No se pudo cargar el listado de solicitudes. Intentá de nuevo en unos minutos.'];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/main.css">
    <title>SGRSI - Solicitudes</title>
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
                <li class="sidebar-item activo">Solicitudes</li>
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
                        <li class="topbar-item"><a href="nueva-solicitud.php">Nueva solicitud</a></li>
                        <li class="topbar-item activo">Listado solicitudes</li>
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
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Salón</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Solicitante</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($solicitudes)) { ?>
                                <tr>
                                    <td colspan="9">Todavía no hay solicitudes registradas.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($solicitudes as $solicitud) { ?>
                                <tr>
                                    <td><a class="tabla-enlace" href="detalle-solicitud.php?id=<?php echo urlencode($solicitud['id_solicitud']); ?>"><?php echo v($solicitud['id_solicitud']); ?></a></td>
                                    <td><?php echo v(date('d/m/y', strtotime($solicitud['fecha_hora_alta']))); ?></td>
                                    <td><?php echo v($solicitud['nombre_salon']); ?></td>
                                    <td><?php echo v($solicitud['descripcion']); ?></td>
                                    <td><span class="badge <?php echo v(Dominio::claseDelBadge($solicitud['estado_solicitud'])); ?>"><?php echo v($solicitud['estado_solicitud']); ?></span></td>
                                    <td><?php echo v($solicitud['prioridad']); ?></td>
                                    <td><?php echo v($solicitud['nombre_solicitante']); ?></td>
                                    <td><?php echo v(isset($solicitud['nombre_responsable']) ? $solicitud['nombre_responsable'] : '—'); ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="detalle-solicitud.php?id=<?php echo urlencode($solicitud['id_solicitud']); ?><?php echo ControlAcceso::puedeAtender() ? '&amp;modo=editar' : ''; ?>">
                                            <?php echo ControlAcceso::puedeAtender() ? 'Atender' : 'Ver'; ?>
                                        </a>

                                        <?php if (Sesion::esCoordinador()) { ?>
                                            <form action="solicitudes.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="id" value="<?php echo v($solicitud['id_solicitud']); ?>">
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
    <script src="../../js/solicitudes/solicitudes.js"></script>
    <script src="../../js/fila-clickeable.js"></script>
</body>

</html>
