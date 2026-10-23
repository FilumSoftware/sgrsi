<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';

ControlAcceso::exigirSesion('../../../index.php');

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

                <p id="resultado-solicitudes" class="error-mensaje <?php echo $mensaje && $mensaje['tipo'] === 'exito' ? 'mensaje-exito' : ''; ?>" role="status" aria-live="polite"<?php echo $mensaje ? ' style="display: block;"' : ''; ?>><?php echo $mensaje ? v($mensaje['texto']) : ''; ?></p>

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
                        <tbody id="lista-solicitudes" data-csrf="<?php echo v(Sesion::tokenApi()); ?>" data-coordinador="<?php echo Sesion::esCoordinador() ? '1' : '0'; ?>" data-atender="<?php echo ControlAcceso::puedeAtender() ? '1' : '0'; ?>">
                            <tr><td colspan="9">Cargando solicitudes...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
    <script src="../../js/solicitudes/solicitudes.js"></script>
</body>

</html>
