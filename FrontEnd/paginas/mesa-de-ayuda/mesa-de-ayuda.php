<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/dao/TicketDAO.php';

ControlAcceso::exigirSesion('../../../index.php');

$ticketDAO = new TicketDAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!Sesion::esCoordinador()) {
        header('Location: mesa-de-ayuda.php?aviso=permiso');
        exit;
    }

    $id = trim((string) (isset($_POST['id']) ? $_POST['id'] : ''));

    try {
        $ticketDAO->eliminar($id);
        header('Location: mesa-de-ayuda.php?aviso=eliminado');
        exit;
    } catch (Exception $e) {
        error_log('SGRSI mesa-de-ayuda.php eliminar: ' . $e->getMessage());
        header('Location: mesa-de-ayuda.php?aviso=error');
        exit;
    }
}

$avisos = [
    'creado'    => ['exito', 'El ticket se registró correctamente.'],
    'guardado'  => ['exito', 'Los cambios se guardaron.'],
    'eliminado' => ['exito', 'El ticket se eliminó.'],
    'permiso'   => ['error', 'Solo el coordinador puede eliminar tickets.'],
    'ajeno'     => ['error', 'Ese ticket no es tuyo.'],
    'noexiste'  => ['error', 'No existe un ticket con ese número.'],
    'error'     => ['error', 'No se pudo completar la operación. Intentá de nuevo en unos minutos.'],
];

$mensaje = null;
$aviso   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$aviso])) {
    $mensaje = ['tipo' => $avisos[$aviso][0], 'texto' => $avisos[$aviso][1]];
}

try {
    if (ControlAcceso::puedeAtender()) {
        $tickets = $ticketDAO->obtenerTodos();
    } else {
        $tickets = $ticketDAO->obtenerPorSolicitante(Sesion::ci());
    }
} catch (Exception $e) {
    error_log('SGRSI mesa-de-ayuda.php listar: ' . $e->getMessage());
    $tickets = [];
    $mensaje = ['tipo' => 'error', 'texto' => 'No se pudo cargar el historial de tickets. Intentá de nuevo en unos minutos.'];
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
    <title>SGRSI - Mesa de ayuda</title>
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
                <li class="sidebar-item activo">Mesa de Ayuda</li>
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
                        <li class="topbar-item"><a href="nuevo-ticket.php">Nuevo ticket</a></li>
                        <li class="topbar-item activo">Historial de tickets</li>
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
                                <th>Hora</th>
                                <th>Salón</th>
                                <th>Equipo</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)) { ?>
                                <tr>
                                    <td colspan="8">Todavía no hay tickets registrados.</td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($tickets as $ticket) { ?>
                                <?php $alta = strtotime($ticket['fecha_hora_alta']); ?>
                                <tr>
                                    <td><?php echo v($ticket['id_ticket']); ?></td>
                                    <td><?php echo v(date('d/m/y', $alta)); ?></td>
                                    <td><?php echo v(date('H:i', $alta)); ?></td>
                                    <td><?php echo v($ticket['nombre_salon']); ?></td>
                                    <td><?php echo v($ticket['nombre_equipo']); ?></td>
                                    <td><?php echo v($ticket['tipo_de_defecto']); ?></td>
                                    <td><span class="badge <?php echo v(Dominio::claseDelBadge($ticket['estado_ticket'])); ?>"><?php echo v($ticket['estado_ticket']); ?></span></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="detalle-ticket.php?id=<?php echo urlencode($ticket['id_ticket']); ?>">
                                            <?php echo ControlAcceso::puedeAtender() ? 'Atender' : 'Ver'; ?>
                                        </a>

                                        <?php if (Sesion::esCoordinador()) { ?>
                                            <form action="mesa-de-ayuda.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="id" value="<?php echo v($ticket['id_ticket']); ?>">
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
    <script src="../../js/mesa-de-ayuda/mesa-de-ayuda.js"></script>
</body>

</html>
