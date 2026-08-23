<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';
require_once __DIR__ . '/../../../BackEnd/dao/SolicitudDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/SalonDAO.php';
require_once __DIR__ . '/../../../BackEnd/models/Solicitud.php';

ControlAcceso::exigirSesion('../../../index.php');

const MOTIVO_MINIMO = 10;

$solicitudDAO = new SolicitudDAO();
$salonDAO     = new SalonDAO();

$errores = [];
$valores = [
    'fecha'     => date('Y-m-d'),
    'salon'     => '',
    'prioridad' => 'Normal',
    'motivo'    => '',
];

try {
    $salones = $salonDAO->obtenerTodos();
} catch (Exception $e) {
    error_log('SGRSI nueva-solicitud.php salones: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = 'No se pudo cargar la lista de salones. Intentá de nuevo en unos minutos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $valores['fecha']) !== 1) {
        $errores['fecha'] = 'Indicá una fecha válida.';
    }

    $nombresSalon = array_column($salones, 'nombre_salon');

    if (!in_array($valores['salon'], $nombresSalon, true)) {
        $errores['salon'] = 'Elegí un salón válido de la lista.';
    }

    if (!Dominio::esPrioridad($valores['prioridad'])) {
        $errores['prioridad'] = 'Elegí una prioridad válida de la lista.';
    }

    if (strlen($valores['motivo']) < MOTIVO_MINIMO) {
        $errores['motivo'] = 'Escribí al menos ' . MOTIVO_MINIMO . ' caracteres.';
    }

    if (empty($errores)) {
        try {
            // El formulario solo pide la fecha: la hora la pone el servidor.
            $solicitud = new Solicitud(
                $valores['fecha'] . ' ' . date('H:i:s'),
                Sesion::ci(),
                $valores['salon'],
                $valores['motivo'],
                $valores['prioridad']
            );

            $solicitudDAO->insertar($solicitud);

            header('Location: solicitudes.php?aviso=creada');
            exit;
        } catch (Exception $e) {
            error_log('SGRSI nueva-solicitud.php insertar: ' . $e->getMessage());
            $errores['general'] = 'No se pudo registrar la solicitud. Intentá de nuevo en unos minutos.';
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

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>

                <form id="form-solicitud" action="nueva-solicitud.php" method="post">
                    <fieldset>
                        <div class="form-grupo">
                            <label for="solicitante">Solicitante:</label>
                            <input type="text" id="solicitante" value="<?php echo v(Sesion::nombre()); ?>" disabled>
                        </div>
                        <div class="form-grupo">
                            <label for="fecha">Fecha:</label>
                            <input type="date" id="fecha" name="fecha" value="<?php echo v($valores['fecha']); ?>" required>
                            <p class="error-mensaje" id="error-fecha"<?php echo claseError($errores, 'fecha'); ?>><?php echo v($errores['fecha'] ?? ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon" required>
                                <option value="">Elija una opción</option>
                                <?php foreach ($salones as $salon) { ?>
                                    <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"<?php echo claseError($errores, 'salon'); ?>><?php echo v($errores['salon'] ?? ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="prioridad">Prioridad:</label>
                            <select id="prioridad" name="prioridad" required>
                                <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                    <option value="<?php echo v($prioridad); ?>"<?php echo $valores['prioridad'] === $prioridad ? ' selected' : ''; ?>><?php echo v($prioridad); ?></option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-prioridad"<?php echo claseError($errores, 'prioridad'); ?>><?php echo v($errores['prioridad'] ?? ''); ?></p>
                        </div>
                        <div class="form-grupo">
                            <label for="motivo">Motivo:</label>
                            <textarea id="motivo" name="motivo" rows="4" required><?php echo v($valores['motivo']); ?></textarea>
                            <p class="error-mensaje" id="error-motivo"<?php echo claseError($errores, 'motivo'); ?>><?php echo v($errores['motivo'] ?? ''); ?></p>
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
