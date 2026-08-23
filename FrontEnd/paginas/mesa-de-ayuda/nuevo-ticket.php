<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/dao/TicketDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/SalonDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/EquipoDAO.php';
require_once __DIR__ . '/../../../BackEnd/models/Ticket.php';

ControlAcceso::exigirSesion('../../../index.php');

$motivoMinimo = 10;

$ticketDAO = new TicketDAO();
$salonDAO  = new SalonDAO();
$equipoDAO = new EquipoDAO();

$errores = [];
$valores = [
    'fecha'     => date('Y-m-d'),
    'hora'      => date('H:i'),
    'salon'     => '',
    'equipo'    => '',
    'defecto'   => '',
    'prioridad' => 'Normal',
    'motivo'    => '',
];

try {
    $salones = $salonDAO->obtenerTodos();
} catch (Exception $e) {
    error_log('SGRSI nuevo-ticket.php salones: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = 'No se pudo cargar la lista de salones. Intentá de nuevo en unos minutos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $valorActual) {
        $valores[$campo] = trim((string) (isset($_POST[$campo]) ? $_POST[$campo] : ''));
    }
}

$equipos = [];

if ($valores['salon'] !== '') {
    try {
        $equipos = $equipoDAO->obtenerPorSalon($valores['salon']);
    } catch (Exception $e) {
        error_log('SGRSI nuevo-ticket.php equipos: ' . $e->getMessage());
        $errores['general'] = 'No se pudo cargar la lista de equipos. Intentá de nuevo en unos minutos.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['accion']) ? $_POST['accion'] : '') === 'crear') {

    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $valores['fecha']) !== 1) {
        $errores['fecha'] = 'Indicá una fecha válida.';
    }

    if (preg_match('/^[0-9]{2}:[0-9]{2}$/', $valores['hora']) !== 1) {
        $errores['hora'] = 'Indicá una hora válida.';
    }

    $nombresSalon = [];

    foreach ($salones as $unSalon) {
        $nombresSalon[] = $unSalon['nombre_salon'];
    }

    if (!in_array($valores['salon'], $nombresSalon, true)) {
        $errores['salon'] = 'Elegí un salón válido de la lista.';
    }

    $idsEquipo = [];

    foreach ($equipos as $unEquipo) {
        $idsEquipo[] = (int) $unEquipo['id_equipo'];
    }

    if (!in_array((int) $valores['equipo'], $idsEquipo, true)) {
        $errores['equipo'] = 'Elegí un equipo del salón seleccionado.';
    }

    if (!Dominio::esTipoDeDefecto($valores['defecto'])) {
        $errores['defecto'] = 'Elegí un tipo de falla de la lista.';
    }

    if (!Dominio::esPrioridad($valores['prioridad'])) {
        $errores['prioridad'] = 'Elegí una prioridad válida de la lista.';
    }

    if (strlen($valores['motivo']) < $motivoMinimo) {
        $errores['motivo'] = 'Escribí al menos ' . $motivoMinimo . ' caracteres.';
    }

    if (empty($errores)) {
        try {
            $ticket = new Ticket(
                $valores['fecha'] . ' ' . $valores['hora'] . ':00',
                Sesion::ci(),
                (int) $valores['equipo'],
                $valores['defecto'],
                $valores['motivo'],
                $valores['prioridad']
            );

            $ticketDAO->insertar($ticket);

            header('Location: mesa-de-ayuda.php?aviso=creado');
            exit;
        } catch (Exception $e) {
            error_log('SGRSI nuevo-ticket.php insertar: ' . $e->getMessage());
            $errores['general'] = 'No se pudo registrar el ticket. Intentá de nuevo en unos minutos.';
        }
    }
}

function v($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function claseError($errores, $campo)
{
    return !empty($errores[$campo]) ? ' style="display: block;"' : '';
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
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
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
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>

                <form id="form-ticket" action="nuevo-ticket.php" method="post">
                    <div class="form-grupo">
                        <label for="solicitante">Solicitante</label>
                        <input type="text" id="solicitante" value="<?php echo v(Sesion::nombre()); ?>" disabled>
                    </div>
                    <div class="form-grupo">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" value="<?php echo v($valores['fecha']); ?>" required>
                        <p class="error-mensaje" id="error-fecha"<?php echo claseError($errores, 'fecha'); ?>><?php echo v(isset($errores['fecha']) ? $errores['fecha'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="hora">Hora</label>
                        <input type="time" name="hora" id="hora" value="<?php echo v($valores['hora']); ?>" required>
                        <p class="error-mensaje" id="error-hora"<?php echo claseError($errores, 'hora'); ?>><?php echo v(isset($errores['hora']) ? $errores['hora'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="salon">Salón</label>
                        <select name="salon" id="salon" required onchange="this.form.submit()">
                            <option value="">Elija una opción</option>
                            <?php foreach ($salones as $salon) { ?>
                                <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-salon"<?php echo claseError($errores, 'salon'); ?>><?php echo v(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="equipo">Equipo</label>
                        <select name="equipo" id="equipo" required>
                            <option value="">
                                <?php echo $valores['salon'] === '' ? 'Elegí primero un salón' : 'Elija una opción'; ?>
                            </option>
                            <?php foreach ($equipos as $equipo) { ?>
                                <option value="<?php echo v($equipo['id_equipo']); ?>"<?php echo (int) $valores['equipo'] === (int) $equipo['id_equipo'] ? ' selected' : ''; ?>><?php echo v($equipo['nombre_equipo']); ?> (<?php echo v($equipo['estado_equipo']); ?>)</option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-equipo"<?php echo claseError($errores, 'equipo'); ?>><?php echo v(isset($errores['equipo']) ? $errores['equipo'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="defecto">Tipo de falla</label>
                        <select name="defecto" id="defecto" required>
                            <option value="">Elija una opción</option>
                            <?php foreach (Dominio::TIPOS_DE_DEFECTO as $tipo) { ?>
                                <option value="<?php echo v($tipo); ?>"<?php echo $valores['defecto'] === $tipo ? ' selected' : ''; ?>><?php echo v($tipo); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-defecto"<?php echo claseError($errores, 'defecto'); ?>><?php echo v(isset($errores['defecto']) ? $errores['defecto'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="prioridad">Prioridad</label>
                        <select name="prioridad" id="prioridad" required>
                            <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                <option value="<?php echo v($prioridad); ?>"<?php echo $valores['prioridad'] === $prioridad ? ' selected' : ''; ?>><?php echo v($prioridad); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-prioridad"<?php echo claseError($errores, 'prioridad'); ?>><?php echo v(isset($errores['prioridad']) ? $errores['prioridad'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="motivo">Motivo</label>
                        <textarea name="motivo" id="motivo" rows="5" placeholder="Escriba el motivo..." required><?php echo v($valores['motivo']); ?></textarea>
                        <p class="error-mensaje" id="error-motivo"<?php echo claseError($errores, 'motivo'); ?>><?php echo v(isset($errores['motivo']) ? $errores['motivo'] : ''); ?></p>
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
