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
    <title>SGRSI - Préstamos</title>
</head>
<body data-coordinador="<?php echo Sesion::esCoordinador() ? '1' : '0'; ?>">
    <div class="layout">
        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <li class="sidebar-item"><a href="../inventario/inventario.php">Inventario</a></li>
                <li class="sidebar-item activo">Préstamos</li>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php">Mesa de Ayuda</a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php">Solicitudes</a></li>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../usuarios/usuarios.php">Usuarios</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir">Cerrar sesión</a></li>
            </ul>
        </nav>

        <main class="main">
            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="../inventario/inventario.php">Equipos</a></li>
                        <li class="topbar-item activo">Préstamos</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1 id="titulo-formulario">Registrar préstamo</h1>
                <p id="resultado-prestamo" class="error-mensaje" role="status" aria-live="polite"></p>
                <form id="form-prestamo" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <div class="form-grupo">
                        <label for="equipo">Equipo</label>
                        <select id="equipo" required>
                            <option value="">Cargando equipos...</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="solicitante">Usuario</label>
                        <select id="solicitante" required>
                            <option value="">Cargando usuarios...</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label for="fecha-prevista">Devolución prevista</label>
                        <input type="date" id="fecha-prevista">
                    </div>
                    <div class="form-grupo">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" maxlength="500" rows="3"></textarea>
                    </div>
                    <button type="submit" id="guardar-prestamo">Registrar préstamo</button>
                    <button type="button" id="cancelar-edicion" class="btn-editar oculta">Cancelar edición</button>
                </form>
            </section>

            <section class="content">
                <h2>Historial de préstamos</h2>
                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>N.º</th>
                                <th>Equipo</th>
                                <th>Usuario</th>
                                <th>Entrega</th>
                                <th>Prevista</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="lista-prestamos">
                            <tr><td colspan="7">Cargando préstamos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script src="../../js/prestamos/prestamos.js"></script>
</body>
</html>
