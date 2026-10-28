<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorInventario.php';

ControlAcceso::exigirRol(['Asistente', 'Coordinador'], '../../../index.php');

$gestor = new GestorInventario();

$estadosValidos = ['Operativo', 'En reparación', 'En trámite de baja', 'De baja'];


$idEquipo = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idEquipo <= 0) {
    header('Location: inventario.php');
    exit;
}

$errores = [];

$modoVer = $_SERVER['REQUEST_METHOD'] !== 'POST'
    && (!isset($_GET['modo']) || $_GET['modo'] !== 'editar');

try {
    $salones = ResultadoGestion::valor($gestor->obtenerSalones());
} catch (Exception $e) {
    error_log('SGRSI detalle-equipo.php: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = 'No se pudo cargar la lista de salones. Intentá de nuevo en unos minutos.';
}

try {
    $equipoActual = ResultadoGestion::valor($gestor->obtenerPorId($idEquipo));
} catch (Exception $e) {
    error_log('SGRSI detalle-equipo.php: ' . $e->getMessage());
    $equipoActual = false;
}

if (!$equipoActual) {
    header('Location: inventario.php');
    exit;
}

$valores = [
    'nombre'    => $equipoActual['nombre_equipo'],
    'salon'     => $equipoActual['nombre_salon'],
    'extras'    => isset($equipoActual['descripcion']) ? $equipoActual['descripcion'] : '',
    'estado'    => $equipoActual['estado_equipo'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($valores) as $campo) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->actualizarDesdeFormulario($idEquipo, $_POST)
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    if ($resultado['ok']) {
        header('Location: inventario.php?ok=2');
        exit;
    }
    http_response_code($resultado['codigo']);
    $errores['general'] = $resultado['mensaje'];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title>SGRSI - Detalle de equipo</title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php">Sala de informática</a></li>
                <li class="sidebar-item activo">Inventario</li>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php">Mesa de Ayuda</a></li>
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
                        <li class="topbar-item"><a href="nuevo-equipo.php">Registro de equipos</a></li>
                        <li class="topbar-item"><a href="inventario.php">Equipos</a></li>
                        <li class="topbar-item activo">Detalle de equipo</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1>Detalle de Equipo</h1>

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo htmlspecialchars($errores['general']); ?></p>
                <?php } ?>
                <p id="resultado-guardado" class="error-mensaje" role="status" aria-live="polite"></p>

                <form id="form-detalle-equipo" action="detalle-equipo.php?id=<?php echo (int) $idEquipo; ?>" method="post" data-csrf="<?php echo htmlspecialchars(Sesion::tokenApi(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(Sesion::tokenApi(), ENT_QUOTES, 'UTF-8'); ?>">
                    <fieldset<?php echo $modoVer ? ' disabled' : ''; ?>>
                        <div class="form-grupo">
                            <label for="id-equipo">ID Equipo (No editable):</label>
                            <input type="text" id="id-equipo" name="id-equipo" value="<?php echo (int) $idEquipo; ?>" readonly>
                        </div>

                        <div class="form-grupo">
                            <label for="nombre">Nombre del equipo:</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($valores['nombre']); ?>" maxlength="100" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['nombre']) ? $errores['nombre'] : ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon" required>
                                <?php foreach ($salones as $salon) { ?>
                                    <option value="<?php echo htmlspecialchars($salon['nombre_salon']); ?>"
                                        <?php echo $salon['nombre_salon'] === $valores['salon'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($salon['nombre_salon']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"<?php echo !empty($errores['salon']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="extras">Extras:</label>
                            <input type="text" id="extras" name="extras" value="<?php echo htmlspecialchars($valores['extras']); ?>">
                        </div>

                        <div class="form-grupo">
                            <label for="estado">Estado actual:</label>
                            <select id="estado" name="estado">
                                <?php foreach ($estadosValidos as $estado) { ?>
                                    <option value="<?php echo htmlspecialchars($estado); ?>"
                                        <?php echo $estado === $valores['estado'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($estado); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-estado"<?php echo !empty($errores['estado']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['estado']) ? $errores['estado'] : ''); ?></p>
                        </div>

                        <?php if ($modoVer) { ?>
                            <a class="btn-editar-form" href="detalle-equipo.php?id=<?php echo (int) $idEquipo; ?>&amp;modo=editar">Editar</a>
                        <?php } else { ?>
                            <button type="submit" id="btn-guardar">Guardar Cambios</button>
                        <?php } ?>

                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/inventario/detalle-equipo.js"></script>
</body>

</html>
