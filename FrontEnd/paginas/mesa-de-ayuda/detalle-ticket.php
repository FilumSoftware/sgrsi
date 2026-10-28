<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorTickets.php';
require_once __DIR__ . '/../../../BackEnd/logica/Dominio.php';

ControlAcceso::exigirSesion('../../../index.php');

$gestor = new GestorTickets();

$id = '';

if (isset($_POST['id'])) {
    $id = trim((string) $_POST['id']);
} elseif (isset($_GET['id'])) {
    $id = trim((string) $_GET['id']);
}

if ($id === '') {
    header('Location: mesa-de-ayuda.php?aviso=noexiste');
    exit;
}

try {
    $fila = ResultadoGestion::valor($gestor->obtenerPorId($id));
} catch (Exception $e) {
    error_log('SGRSI detalle-ticket.php obtenerPorId: ' . $e->getMessage());
    header('Location: mesa-de-ayuda.php?aviso=error');
    exit;
}

if (!$fila) {
    header('Location: mesa-de-ayuda.php?aviso=noexiste');
    exit;
}

$puedeAtender = ControlAcceso::puedeAtender();

if (!$puedeAtender && $fila['ci_solicitante'] !== Sesion::ci()) {
    header('Location: mesa-de-ayuda.php?aviso=ajeno');
    exit;
}

$modoVer = $_SERVER['REQUEST_METHOD'] !== 'POST'
    && (!isset($_GET['modo']) || $_GET['modo'] !== 'editar');

$editable = $puedeAtender && !$modoVer && $fila['estado_ticket'] !== 'Cancelado';
$puedeCancelar = $fila['ci_solicitante'] === Sesion::ci()
    && $fila['estado_ticket'] === 'Pendiente'
    && empty($fila['ci_responsable']);

$errores = [];
$valores = [
    'salon'       => $fila['nombre_salon'],
    'equipo'      => $fila['id_equipo'],
    'defecto'     => $fila['tipo_de_defecto'],
    'prioridad'   => $fila['prioridad'],
    'estado'      => $fila['estado_ticket'],
    'responsable' => isset($fila['ci_responsable']) ? $fila['ci_responsable'] : '',
    'motivo'      => isset($fila['descripcion']) ? $fila['descripcion'] : '',
    'diagnostico' => isset($fila['diagnostico']) ? $fila['diagnostico'] : '',
    'solucion'    => isset($fila['solucion']) ? $fila['solucion'] : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $valorActual) {
        $valores[$campo] = ResultadoGestion::campo($_POST, $campo);
    }
}

try {
    $salones = ResultadoGestion::valor($gestor->obtenerSalones());
    $equipos = $valores['salon'] !== '' ? ResultadoGestion::valor($gestor->obtenerEquiposPorSalon($valores['salon'])) : [];
    $tecnicos = ResultadoGestion::valor($gestor->obtenerTecnicos());
} catch (Exception $e) {
    error_log('SGRSI detalle-ticket.php listas: ' . $e->getMessage());
    $salones  = [];
    $equipos  = [];
    $tecnicos = [];
    $errores['general'] = 'No se pudieron cargar las listas. Intentá de nuevo en unos minutos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['accion']) ? $_POST['accion'] : '') === 'guardar') {
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->actualizarDesdeFormulario($id, $_POST, $puedeAtender)
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    if ($resultado['ok']) {
        header('Location: mesa-de-ayuda.php?aviso=guardado');
        exit;
    }
    http_response_code($resultado['codigo']);
    $errores['general'] = $resultado['mensaje'];
}

$alta = strtotime($fila['fecha_hora_alta']);

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
    <title>SGRSI - Detalle de ticket</title>
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
                        <li class="topbar-item"><a href="mesa-de-ayuda.php">Historial de tickets</a></li>
                        <li class="topbar-item"><a href="nuevo-ticket.php">Nuevo ticket</a></li>
                        <li class="topbar-item activo">Detalle de ticket</li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <img src="../../img/logo.png" alt="Logo SGRSI">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="form-container">
                <h1>Ticket n.º <?php echo v($fila['id_ticket']); ?></h1>

                <?php if (!empty($errores['general'])) { ?>
                    <p class="error-mensaje" style="display: block;"><?php echo v($errores['general']); ?></p>
                <?php } ?>
                <p id="resultado-guardado" class="error-mensaje" role="status" aria-live="polite"></p>

                <?php if (!$puedeAtender) { ?>
                    <p class="error-mensaje" style="display: block;">Este ticket es tuyo, pero solo lo puede modificar un técnico.</p>
                <?php } ?>

                <form id="form-detalle-ticket" action="detalle-ticket.php" method="post" data-csrf="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                    <input type="hidden" name="id" value="<?php echo v($fila['id_ticket']); ?>">

                    <div class="form-grupo">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" value="<?php echo v(date('Y-m-d', $alta)); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="hora">Hora</label>
                        <input type="time" id="hora" value="<?php echo v(date('H:i', $alta)); ?>" readonly>
                    </div>
                    <div class="form-grupo">
                        <label for="solicitante">Solicitante</label>
                        <input type="text" id="solicitante" value="<?php echo v($fila['nombre_solicitante']); ?>" readonly>
                    </div>

                    <div class="form-grupo">
                        <label for="salon">Salón</label>
                        <select name="salon" id="salon" required<?php echo $editable ? '' : ' disabled'; ?>>
                            <?php foreach ($salones as $salon) { ?>
                                <option value="<?php echo v($salon['nombre_salon']); ?>"<?php echo $valores['salon'] === $salon['nombre_salon'] ? ' selected' : ''; ?>><?php echo v($salon['nombre_salon']); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-salon"<?php echo claseError($errores, 'salon'); ?>><?php echo v(isset($errores['salon']) ? $errores['salon'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="equipo">Equipo</label>
                        <select name="equipo" id="equipo" required<?php echo $editable ? '' : ' disabled'; ?>>
                            <?php foreach ($equipos as $equipo) { ?>
                                <option value="<?php echo v($equipo['id_equipo']); ?>"<?php echo (int) $valores['equipo'] === (int) $equipo['id_equipo'] ? ' selected' : ''; ?>><?php echo v($equipo['nombre_equipo']); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-equipo"<?php echo claseError($errores, 'equipo'); ?>><?php echo v(isset($errores['equipo']) ? $errores['equipo'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="defecto">Tipo de falla</label>
                        <select name="defecto" id="defecto"<?php echo $editable ? '' : ' disabled'; ?>>
                            <?php foreach (Dominio::TIPOS_DE_DEFECTO as $tipo) { ?>
                                <option value="<?php echo v($tipo); ?>"<?php echo $valores['defecto'] === $tipo ? ' selected' : ''; ?>><?php echo v($tipo); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-defecto"<?php echo claseError($errores, 'defecto'); ?>><?php echo v(isset($errores['defecto']) ? $errores['defecto'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="prioridad">Prioridad</label>
                        <select name="prioridad" id="prioridad"<?php echo $editable ? '' : ' disabled'; ?>>
                            <?php foreach (Dominio::PRIORIDADES as $prioridad) { ?>
                                <option value="<?php echo v($prioridad); ?>"<?php echo $valores['prioridad'] === $prioridad ? ' selected' : ''; ?>><?php echo v($prioridad); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-prioridad"<?php echo claseError($errores, 'prioridad'); ?>><?php echo v(isset($errores['prioridad']) ? $errores['prioridad'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado"<?php echo $editable ? '' : ' disabled'; ?>>
                            <?php foreach (Dominio::ESTADOS_TICKET as $estado) { ?>
                                <?php if ($estado === 'Cancelado' && $fila['estado_ticket'] !== 'Cancelado') { continue; } ?>
                                <option value="<?php echo v($estado); ?>"<?php echo $valores['estado'] === $estado ? ' selected' : ''; ?>><?php echo v($estado); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-estado"<?php echo claseError($errores, 'estado'); ?>><?php echo v(isset($errores['estado']) ? $errores['estado'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="responsable">Responsable</label>
                        <select name="responsable" id="responsable"<?php echo $editable ? '' : ' disabled'; ?>>
                            <option value="">— Seleccionar técnico —</option>
                            <?php foreach ($tecnicos as $tecnico) { ?>
                                <option value="<?php echo v($tecnico['ci']); ?>"<?php echo $valores['responsable'] === $tecnico['ci'] ? ' selected' : ''; ?>><?php echo v($tecnico['nombre_usuario']); ?></option>
                            <?php } ?>
                        </select>
                        <p class="error-mensaje" id="error-responsable"<?php echo claseError($errores, 'responsable'); ?>><?php echo v(isset($errores['responsable']) ? $errores['responsable'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="motivo">Motivo</label>
                        <textarea name="motivo" id="motivo" rows="4"<?php echo $editable ? '' : ' disabled'; ?>><?php echo v($valores['motivo']); ?></textarea>
                        <p class="error-mensaje" id="error-motivo"<?php echo claseError($errores, 'motivo'); ?>><?php echo v(isset($errores['motivo']) ? $errores['motivo'] : ''); ?></p>
                    </div>
                    <div class="form-grupo">
                        <label for="diagnostico">Diagnóstico</label>
                        <textarea name="diagnostico" id="diagnostico" rows="3"<?php echo $editable ? '' : ' disabled'; ?>><?php echo v($valores['diagnostico']); ?></textarea>
                    </div>
                    <div class="form-grupo">
                        <label for="solucion">Solución</label>
                        <textarea name="solucion" id="solucion" rows="3"<?php echo $editable ? '' : ' disabled'; ?>><?php echo v($valores['solucion']); ?></textarea>
                    </div>

                    <?php if ($editable) { ?>
                        <button type="submit" id="btn-enviar" name="accion" value="guardar">Guardar cambios</button>
                    <?php } elseif ($puedeAtender && $fila['estado_ticket'] !== 'Cancelado') { ?>
                        <a class="btn-editar-form" href="detalle-ticket.php?id=<?php echo urlencode($id); ?>&amp;modo=editar">Editar</a>
                    <?php } ?>

                </form>

                <?php if ($puedeCancelar) { ?>
                    <div class="form-grupo">
                        <button type="button" id="btn-cancelar-ticket" class="btn-eliminar" data-id="<?php echo v($fila['id_ticket']); ?>" data-csrf="<?php echo v(Sesion::tokenCancelarTicket()); ?>">Cancelar este ticket</button>
                        <p id="resultado-cancelacion" class="error-mensaje" role="status" aria-live="polite"></p>
                    </div>
                <?php } ?>
            </section>
        </main>
    </div>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/mesa-de-ayuda/detalle-ticket.js"></script>
</body>

</html>
