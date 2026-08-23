<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title>SGRSI - Usuarios</title>
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php">Inventario</a></li>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php">Mesa de Ayuda</a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php">Solicitudes</a></li>
                <li class="sidebar-item activo">Usuarios</li>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir">Cerrar sesión</a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="nuevo-usuario.php">Nuevo usuario</a></li>
                        <li class="topbar-item activo">Usuarios</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="content">
                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Mail</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Juan Pérez</td>
                                <td>jperez@itr.edu.uy</td>
                                <td>Solicitante</td>
                                <td class="acciones">
                                    <button class="btn-editar">Editar</button>
                                    <button class="btn-eliminar">Desactivar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>María García</td>
                                <td>mgarcia@itr.edu.uy</td>
                                <td>Solicitante</td>
                                <td class="acciones">
                                    <button class="btn-editar">Editar</button>
                                    <button class="btn-eliminar">Desactivar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Carlos López</td>
                                <td>clopez@itr.edu.uy</td>
                                <td>Asistente</td>
                                <td class="acciones">
                                    <button class="btn-editar">Editar</button>
                                    <button class="btn-eliminar">Desactivar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Ana Rodríguez</td>
                                <td>arodriguez@itr.edu.uy</td>
                                <td>Asistente</td>
                                <td class="acciones">
                                    <button class="btn-editar">Editar</button>
                                    <button class="btn-eliminar">Desactivar</button>
                                </td>
                            </tr>
                            <tr>
                                <td>Luis Martínez</td>
                                <td>lmartinez@itr.edu.uy</td>
                                <td>Coordinador</td>
                                <td class="acciones">
                                    <button class="btn-editar">Editar</button>
                                    <button class="btn-eliminar">Desactivar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
    <script src="../../js/usuarios/usuarios.js"></script>
</body>

</html>