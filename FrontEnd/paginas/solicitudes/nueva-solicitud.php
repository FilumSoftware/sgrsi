<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';

ControlAcceso::exigirSesion('../../../index.php');

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
    <title>SGRSI - Nueva solicitud</title>
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
                        <li class="topbar-item activo">Nueva solicitud</li>
                        <li class="topbar-item"><a href="solicitudes.php">Listado solicitudes</a></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">

                <p id="resultado-solicitud" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-solicitud" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <fieldset>
                        <div class="form-grupo">
                            <label for="solicitante">Solicitante:</label>
                            <input type="text" id="solicitante" value="<?php echo v(Sesion::nombre()); ?>" disabled>
                        </div>
                        <div class="form-grupo">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>
                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon" required>
                                <option value="">Elija una opción</option>
                            </select>
                            <p class="error-mensaje" id="error-salon"></p>
                        </div>
                        <div class="form-grupo">
                            <label for="prioridad">Prioridad:</label>
                            <select id="prioridad" name="prioridad" required>
                                <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                    <option value="<?php echo v($prioridad); ?>"><?php echo v($prioridad); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-prioridad"></p>
                        </div>
                        <div class="form-grupo">
                            <label for="motivo">Motivo:</label>
                            <textarea id="motivo" name="motivo" rows="4" required></textarea>
                            <p class="error-mensaje" id="error-motivo"></p>
                        </div>

                        <input type="submit" id="btn-enviar" value="Enviar solicitud">
                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/solicitudes/nueva-solicitud.js"></script>
</body>

</html>
