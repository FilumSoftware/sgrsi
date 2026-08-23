<?php

require_once __DIR__ . '/../../../BackEnd/dao/EquipoDAO.php';

$equipoDAO = new EquipoDAO();
$mensaje   = null;

if (isset($_GET['ok'])) {
    $mensaje = ['tipo' => 'exito', 'texto' => 'Equipo registrado correctamente.'];
}

try {
    $equipos = $equipoDAO->obtenerTodos();
} catch (Exception $e) {
    error_log('SGRSI inventario.php: ' . $e->getMessage());
    $mensaje = ['tipo' => 'error', 'texto' => 'No se pudo cargar el listado de equipos. Intentá de nuevo en unos minutos.'];
    $equipos = [];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title>SGRSI - Inventario</title>
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.html">Dashboard</a></li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.html">Sala de informática</a></li>
                <li class="sidebar-item activo">Inventario</li>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.html">Mesa de Ayuda</a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.html">Solicitudes</a></li>
                <li class="sidebar-item"><a href="../usuarios/nuevo-usuario.html">Usuarios</a></li>
                <li class="sidebar-item"><a href="../../../index.html" class="btn-salir">Cerrar sesión</a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="nuevo-equipo.php">Registro de equipos</a></li>
                        <li class="topbar-item activo">Equipos</li>
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
                        <?php echo htmlspecialchars($mensaje['texto']); ?>
                    </p>
                <?php } ?>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Extras</th>
                                <th>Salón</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($equipos)) { ?>
                                <tr>
                                    <td colspan="6">Todavía no hay equipos registrados.</td>
                                </tr>
                            <?php } ?>
                            <?php foreach ($equipos as $equipo) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($equipo['id_equipo']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['descripcion'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['nombre_salon']); ?></td>
                                    <td><?php echo htmlspecialchars($equipo['estado_equipo']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>

</html>