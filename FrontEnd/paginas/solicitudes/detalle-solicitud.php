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
    <title>SGRSI - Detalle de solicitud</title>
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
                        <li class="topbar-item"><a href="nueva-solicitud.php">Nueva solicitud</a></li>
                        <li class="topbar-item"><a href="solicitudes.php">Listado solicitudes</a></li>
                        <li class="topbar-item activo">Detalle solicitud</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <form id="form-detalle-solicitud">
                    <fieldset>
                        <legend>Detalle Solicitud</legend>

                        <div class="form-grupo">
                            <label for="estado">Estado:</label>
                            <select id="estado" name="estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="finalizada">Finalizada</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                        </div>
                        <div class="form-grupo">
                            <label for="prioridad">Prioridad:</label>
                            <select id="prioridad" name="prioridad">
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta" selected>Alta</option>
                    </select>
                        </div>

                        <!-- el solicitante se autocompleta cuando se crea el ticket-->
                        <div class="form-grupo">
                            <label for="solicitante-display">Solicitante:</label>
                            <input type="text" id="solicitante-display" readonly value="Juan Pérez">
                            <input type="hidden" id="solicitante" name="solicitante" value="juan-perez">
                        </div>

                        <!-- en el caso del usuario admin, el responsable se elige de un dropdown que viene del backend-->
                        <div class="form-grupo">
                            <label for="responsable">Responsable:</label>
                            <select id="responsable" name="responsable" required>
                        <option value="">— Seleccionar técnico —</option>
                                 <!-- acá va el backend -->
                        </select>
                        </div>

                        <div class="form-grupo">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" value="2026-06-01">
                        </div>
                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon">
                        <option value="laboratorio-1" selected>Laboratorio 1</option>
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
                            <textarea id="motivo" name="motivo" rows="4" cols="50">PC no enciende</textarea><br><br>
                        </div>

                        <input type="submit" value="Guardar solicitud">
                    </fieldset>
                </form>
            </section>

        </main>
    </div>
</body>

</html>