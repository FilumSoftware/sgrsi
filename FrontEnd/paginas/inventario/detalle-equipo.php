<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/dao/EquipoDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/SalonDAO.php';
require_once __DIR__ . '/../../../BackEnd/models/Equipo.php';

ControlAcceso::exigirRol(['Asistente', 'Coordinador'], '../../../index.php');

$categoriasValidas = ['PC de escritorio', 'Laptop', 'Proyector', 'Impresora', 'Otro'];
$estadosValidos    = ['Operativo', 'En reparación', 'Derivado', 'En trámite de baja', 'De baja'];

$salonDAO  = new SalonDAO();
$equipoDAO = new EquipoDAO();

$idEquipo = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idEquipo <= 0) {
    header('Location: inventario.php');
    exit;
}

$errores = [];

$modoVer = $_SERVER['REQUEST_METHOD'] !== 'POST'
    && (!isset($_GET['modo']) || $_GET['modo'] !== 'editar');

try {
    $salones = $salonDAO->obtenerTodos();
} catch (Exception $e) {
    error_log('SGRSI detalle-equipo.php: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = 'No se pudo cargar la lista de salones. Intentá de nuevo en unos minutos.';
}

try {
    $equipoActual = $equipoDAO->obtenerPorId($idEquipo);
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
    'categoria' => $equipoActual['categoria'],
    'extras'    => isset($equipoActual['descripcion']) ? $equipoActual['descripcion'] : '',
    'estado'    => $equipoActual['estado_equipo'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $valores['nombre']    = trim((string) (isset($_POST['nombre']) ? $_POST['nombre'] : ''));
    $valores['salon']     = trim((string) (isset($_POST['salon']) ? $_POST['salon'] : ''));
    $valores['categoria'] = trim((string) (isset($_POST['categoria']) ? $_POST['categoria'] : ''));
    $valores['extras']    = trim((string) (isset($_POST['extras']) ? $_POST['extras'] : ''));
    $valores['estado']    = trim((string) (isset($_POST['estado']) ? $_POST['estado'] : ''));

    if ($valores['nombre'] === '') {
        $errores['nombre'] = 'El nombre del equipo es obligatorio.';
    } elseif (strlen($valores['nombre']) > 100) {
        $errores['nombre'] = 'El nombre del equipo no puede superar los 100 caracteres.';
    }

    $nombresSalon = [];

    foreach ($salones as $unSalon) {
        $nombresSalon[] = $unSalon['nombre_salon'];
    }

    if ($valores['salon'] === '' || !in_array($valores['salon'], $nombresSalon, true)) {
        $errores['salon'] = 'Elegí un salón válido de la lista.';
    }

    if ($valores['categoria'] === '' || !in_array($valores['categoria'], $categoriasValidas, true)) {
        $errores['categoria'] = 'Elegí una categoría válida de la lista.';
    }

    if ($valores['estado'] === '' || !in_array($valores['estado'], $estadosValidos, true)) {
        $errores['estado'] = 'Elegí un estado válido de la lista.';
    }

    if (empty($errores)) {
        try {
            $equipo = new Equipo(
                $valores['nombre'],
                $valores['categoria'],
                $valores['extras'] !== '' ? $valores['extras'] : null,
                $valores['salon'],
                $valores['estado'],
                $idEquipo
            );

            $equipoDAO->actualizar($equipo);

            header('Location: inventario.php?ok=2');
            exit;

        } catch (Exception $e) {
            error_log('SGRSI detalle-equipo.php actualizar: ' . $e->getMessage());
            $errores['general'] = 'No se pudo guardar el equipo. Intentá de nuevo en unos minutos.';
        }
    }
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

                <form id="form-detalle-equipo" action="detalle-equipo.php?id=<?php echo (int) $idEquipo; ?>" method="post">
                    <fieldset<?php echo $modoVer ? ' disabled' : ''; ?>>
                        <div class="form-grupo">
                            <label for="id-equipo">ID Equipo (No editable):</label>
                            <input type="text" id="id-equipo" name="id-equipo" value="<?php echo (int) $idEquipo; ?>" readonly>
                        </div>

                        <div class="form-grupo">
                            <label for="nombre">Nombre del equipo:</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($valores['nombre']); ?>" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['nombre']) ? $errores['nombre'] : ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon">
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
                            <label for="categoria">Categoría:</label>
                            <select id="categoria" name="categoria">
                                <?php foreach ($categoriasValidas as $categoria) { ?>
                                    <option value="<?php echo htmlspecialchars($categoria); ?>"
                                        <?php echo $categoria === $valores['categoria'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-categoria"<?php echo !empty($errores['categoria']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars(isset($errores['categoria']) ? $errores['categoria'] : ''); ?></p>
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
