<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/logica/Resumen.php';

ControlAcceso::exigirSesion('../../../index.php');

$cuantosTickets = 5;

$mensaje = null;

$tarjetas = [
    'tickets'     => 0,
    'equipos'     => 0,
    'solicitudes' => 0,
    'usos'        => 0,
];

$tickets = [];

try {
    $resumen = new Resumen();

    $tarjetas['tickets']     = $resumen->ticketsAbiertos();
    $tarjetas['equipos']     = $resumen->equiposOperativos();
    $tarjetas['solicitudes'] = $resumen->solicitudesPendientes();
    $tarjetas['usos']        = $resumen->registrosDeLaboratorio();

    $tickets = ControlAcceso::puedeAtender()
        ? $resumen->ultimosTickets($cuantosTickets)
        : $resumen->ultimosTickets($cuantosTickets, Sesion::ci());

} catch (Exception $e) {
    error_log('SGRSI dashboard.php: ' . $e->getMessage());
    $mensaje = 'No se pudo cargar el resumen del sistema. Intentá de nuevo en unos minutos.';
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
    <link rel="stylesheet" href="../../css/dashboard.css">
    <title>SGRSI - Dashboard</title>
</head>

<body>
    <div class="container">
        <nav class="sidebar">
            <ul>
                <li class="sidebar-item activo">Dashboard</li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
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
                        <li class="topbar-item activo">Resumen general</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <div class="content">
                <h1 class="saludo">Hola, <?php echo v(Sesion::nombre()); ?></h1>
                <p class="saludo-sub">Resumen del sistema · <?php echo v(Sesion::rol()); ?></p>

                <?php if ($mensaje !== null) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($mensaje); ?></p>
                <?php } ?>

                <section class="tarjetas">
                    <div class="tarjeta tarjeta-violeta">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['tickets']); ?></p>
                        <p class="tarjeta-label">Tickets abiertos</p>
                    </div>
                    <div class="tarjeta tarjeta-lila">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['equipos']); ?></p>
                        <p class="tarjeta-label">Equipos operativos</p>
                    </div>
                    <div class="tarjeta tarjeta-naranja">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['solicitudes']); ?></p>
                        <p class="tarjeta-label">Solicitudes pendientes</p>
                    </div>
                    <div class="tarjeta tarjeta-bordo">
                        <p class="tarjeta-numero"><?php echo v($tarjetas['usos']); ?></p>
                        <p class="tarjeta-label">Registros de laboratorio</p>
                    </div>
                </section>

                <section class="tabla-contenedor">
                    <h2 class="tabla-titulo">Últimos tickets registrados</h2>
                    <div class="tabla-wrapper">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Solicitante</th>
                                    <th>Equipo</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tickets)) { ?>
                                    <tr>
                                        <td colspan="6">Todavía no hay tickets registrados.</td>
                                    </tr>
                                <?php } ?>

                                <?php foreach ($tickets as $ticket) { ?>
                                    <tr>
                                        <td><?php echo v($ticket['id_ticket']); ?></td>
                                        <td><?php echo v($ticket['nombre_solicitante']); ?></td>
                                        <td><?php echo v($ticket['nombre_equipo']); ?></td>
                                        <td><?php echo v($ticket['tipo_de_defecto']); ?></td>
                                        <td><span class="badge <?php echo v(Dominio::claseDelBadge($ticket['estado_ticket'])); ?>"><?php echo v($ticket['estado_ticket']); ?></span></td>
                                        <td><?php echo v(date('d/m/Y', strtotime($ticket['fecha_hora_alta']))); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>

    </div>
</body>

</html>
