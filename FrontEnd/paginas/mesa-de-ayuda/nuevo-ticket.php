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
    <title>SGRSI - Nuevo ticket</title>
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
                        <li class="topbar-item activo">Nuevo ticket</li>
                        <li class="topbar-item"><a href="mesa-de-ayuda.php">Historial de tickets</a></li>
                        <?php if (ControlAcceso::puedeAtender()) { ?>
                            <li class="topbar-item"><a href="tipos-incidencia.php">Tipos de incidencia</a></li>
                        <?php } ?>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">

                <p id="resultado-ticket" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-ticket" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <div class="form-grupo">
                        <label for="solicitante">Solicitante</label>
                        <input type="text" id="solicitante" value="<?php echo v(Sesion::nombre()); ?>" disabled>
                    </div>
                    <div class="form-grupo">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="hora">Hora</label>
                        <input type="time" name="hora" id="hora" value="<?php echo date('H:i'); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="salon">Salón</label>
                        <select name="salon" id="salon" required>
                            <option value="">Elija una opción</option>
                        </select>
                        <p class="error-mensaje" id="error-salon"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="equipo">Equipo</label>
                        <select name="equipo" id="equipo" required>
                            <option value="">
                                Elegí primero un salón
                            </option>
                        </select>
                        <p class="error-mensaje" id="error-equipo"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="defecto">Tipo de falla</label>
                        <select name="defecto" id="defecto" required>
                            <option value="">Elija una opción</option>
                        </select>
                        <p class="error-mensaje" id="error-defecto"></p>
                    </div>
                    <div class="form-grupo oculta" id="grupo-detalle-defecto">
                        <label for="detalle-defecto">Describí la falla</label>
                        <input type="text" name="detalle-defecto" id="detalle-defecto" maxlength="120" minlength="3">
                    </div>
                    <div class="form-grupo">
                        <label for="prioridad">Prioridad</label>
                        <select name="prioridad" id="prioridad" required>
                            <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                <option value="<?php echo v($prioridad); ?>"><?php echo v($prioridad); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-prioridad"></p>
                    </div>
                    <div class="form-grupo">
                        <label for="motivo">Motivo</label>
                        <textarea name="motivo" id="motivo" rows="5" placeholder="Escriba el motivo..." required></textarea>
                        <p class="error-mensaje" id="error-motivo"></p>
                    </div>

                    <button type="submit" id="btn-enviar" name="accion" value="crear">Enviar</button>

                </form>
            </section>
        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/mesa-de-ayuda/nuevo-ticket.js"></script>
</body>

</html>
