<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/dao/EquipoDAO.php';
require_once __DIR__ . '/../../../BackEnd/dao/SalonDAO.php';
require_once __DIR__ . '/../../../BackEnd/models/Equipo.php';

ControlAcceso::exigirSesion('../../../index.php');

const CATEGORIAS_VALIDAS = ['PC de escritorio', 'Laptop', 'Proyector', 'Impresora', 'Otro'];

$salonDAO  = new SalonDAO();
$equipoDAO = new EquipoDAO();

$errores = [];
$valores = ['nombre' => '', 'salon' => '', 'categoria' => '', 'extras' => ''];

try {
    $salones = $salonDAO->obtenerTodos();
} catch (Exception $e) {
    error_log('SGRSI nuevo-equipo.php: ' . $e->getMessage());
    $salones = [];
    $errores['general'] = 'No se pudo cargar la lista de salones. Intentá de nuevo en unos minutos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $valores['nombre']    = trim((string) ($_POST['nombre'] ?? ''));
    $valores['salon']     = trim((string) ($_POST['salon'] ?? ''));
    $valores['categoria'] = trim((string) ($_POST['categoria'] ?? ''));
    $valores['extras']    = trim((string) ($_POST['extras'] ?? ''));

    if ($valores['nombre'] === '') {
        $errores['nombre'] = 'El nombre del equipo es obligatorio.';
    } elseif (strlen($valores['nombre']) > 100) {
        $errores['nombre'] = 'El nombre del equipo no puede superar los 100 caracteres.';
    }

    $nombresSalon = array_column($salones, 'nombre_salon');
    if ($valores['salon'] === '' || !in_array($valores['salon'], $nombresSalon, true)) {
        $errores['salon'] = 'Elegí un salón válido de la lista.';
    }

    if ($valores['categoria'] === '' || !in_array($valores['categoria'], CATEGORIAS_VALIDAS, true)) {
        $errores['categoria'] = 'Elegí una categoría válida de la lista.';
    }

    if (empty($errores)) {
        try {
            $equipo = new Equipo(
                $valores['nombre'],
                $valores['categoria'],
                $valores['extras'] !== '' ? $valores['extras'] : null,
                $valores['salon']
            );

            $equipoDAO->insertar($equipo);

            header('Location: inventario.php?ok=1');
            exit;

        } catch (Exception $e) {
            error_log('SGRSI nuevo-equipo.php insertar: ' . $e->getMessage());
            $errores['general'] = 'No se pudo registrar el equipo. Intentá de nuevo en unos minutos.';
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
    <title>SGRSI - Registro de equipos</title>
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <ul>
                <li class="sidebar-item"><a href="../dashboard/dashboard.php">Dashboard</a></li>
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
                        <li class="topbar-item activo">Registro de equipos</li>
                        <li class="topbar-item"><a href="inventario.php">Equipos</a></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>
            <section class="form-container">

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo htmlspecialchars($errores['general']); ?></p>
                <?php } ?>

                <form id="form-equipo" method="POST" action="nuevo-equipo.php">
                    <fieldset>
                        <div class="form-grupo">
                            <label for="nombre">Nombre del equipo:</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($valores['nombre']); ?>" required>
                            <p class="error-mensaje" id="error-nombre"<?php echo !empty($errores['nombre']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars($errores['nombre'] ?? ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="salon">Salón:</label>
                            <select id="salon" name="salon">
                                <option value="">— Elegí un salón —</option>
                                <?php foreach ($salones as $salon) { ?>
                                    <option value="<?php echo htmlspecialchars($salon['nombre_salon']); ?>"
                                        <?php echo $salon['nombre_salon'] === $valores['salon'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($salon['nombre_salon']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-salon"<?php echo !empty($errores['salon']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars($errores['salon'] ?? ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="categoria">Categoría:</label>
                            <select id="categoria" name="categoria">
                                <option value="">— Elegí una categoría —</option>
                                <?php foreach (CATEGORIAS_VALIDAS as $categoria) { ?>
                                    <option value="<?php echo htmlspecialchars($categoria); ?>"
                                        <?php echo $categoria === $valores['categoria'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <p class="error-mensaje" id="error-categoria"<?php echo !empty($errores['categoria']) ? ' style="display: block;"' : ''; ?>><?php echo htmlspecialchars($errores['categoria'] ?? ''); ?></p>
                        </div>

                        <div class="form-grupo">
                            <label for="extras">Extras (opcional):</label><br>
                            <input type="text" id="extras" name="extras" value="<?php echo htmlspecialchars($valores['extras']); ?>"><br><br>
                        </div>

                        <input type="submit" value="Registrar equipo">
                    </fieldset>
                </form>
            </section>
        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/inventario/nuevo-equipo.js"></script>
</body>

</html>