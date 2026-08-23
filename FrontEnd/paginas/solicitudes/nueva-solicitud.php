<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';

ControlAcceso::exigirSesion('../../../index.php');

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
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
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
                <form id="form-solicitud">
                    <fieldset>
                        <div class="form-grupo">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" required>
                        </div>
                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon">
                            <option value="laboratorio-1">Laboratorio 1</option>
                            <option value="laboratorio-2">Laboratorio 2</option>
                            <option value="laboratorio-3">Laboratorio 3</option>
                            <option value="laboratorio-4">Laboratorio 4</option>
                            <option value="laboratorio-5">Laboratorio 5</option>
                            <option value="laboratorio-6">Laboratorio 6</option>
                            <option value="taller-1">Taller 1</option>
                            <option value="taller-2">Taller 2</option>
                            <option value="taller-3">Taller 3</option>
                            <option value="deposito">Depósito</option>
                        </select>
                        </div>
                        <div class="form-grupo">
                            <label for="motivo">Motivo:</label><br>
                            <textarea id="motivo" name="motivo" rows="4" cols="50" required></textarea>
                            <p class="error-mensaje" id="error-motivo"></p>
                        </div>

                        <input type="submit" value="Enviar solicitud">
                    </fieldset>
                </form>
            </section>

        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/solicitudes/nueva-solicitud.js"></script>
</body>

</html>