<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';

ControlAcceso::exigirRol(['Asistente', 'Coordinador'], '../../../index.php');

function v($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title>SGRSI - Tipos de incidencia</title>
</head>
<body>
    <div class="layout">
        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <?php } ?>
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
                <nav><ul>
                    <li class="topbar-item"><a href="nuevo-ticket.php">Nuevo ticket</a></li>
                    <li class="topbar-item"><a href="mesa-de-ayuda.php">Historial de tickets</a></li>
                    <li class="topbar-item activo">Tipos de incidencia</li>
                </ul></nav>
                <div class="topbar-logo"><img src="../../img/logo.png" alt="Logo SGRSI"><span>SGRSI</span></div>
            </header>
            <section class="content">
                <h1>Tipos de incidencia</h1>
                <p id="resultado-tipos" class="error-mensaje" role="status" aria-live="polite"></p>
                <form id="form-tipo" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" id="nombre-original">
                    <div class="form-grupo">
                        <label for="nombre-tipo">Nombre</label>
                        <input type="text" id="nombre-tipo" minlength="3" maxlength="80" required>
                    </div>
                    <div class="form-grupo">
                        <label for="estado-tipo">Estado</label>
                        <select id="estado-tipo" disabled>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" id="guardar-tipo">Agregar tipo</button>
                    <button type="button" id="cancelar-edicion-tipo" class="oculta">Cancelar edición</button>
                </form>
                <div class="tabla-wrapper">
                    <table>
                        <thead><tr><th>Nombre</th><th>Estado</th><th>Acciones</th></tr></thead>
                        <tbody id="lista-tipos"><tr><td colspan="3">Cargando tipos...</td></tr></tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script src="../../js/mesa-de-ayuda/tipos-incidencia.js"></script>
</body>
</html>
