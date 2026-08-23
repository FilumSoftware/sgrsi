<?php

// Página de diagnóstico del entorno. No es parte del sistema: sirve para
// confirmar que el servidor, PHP, PDO y la base están bien conectados.

require_once __DIR__ . '/../logica/ControlAcceso.php';

// Diagnostico reservado al coordinador: expone datos del entorno.
ControlAcceso::exigirSesion('../../index.php');

if (!Sesion::esCoordinador()) {
    header('Location: ../../FrontEnd/paginas/dashboard/dashboard.php');
    exit;
}

require_once __DIR__ . '/../dao/UsuarioDAO.php';
require_once __DIR__ . '/../dao/TicketDAO.php';
require_once __DIR__ . '/../dao/SalonDAO.php';

$chequeos = [];
$tickets  = [];

$chequeos[] = ['El servidor ejecuta PHP', true, 'versión ' . PHP_VERSION];

$tienePdo = extension_loaded('pdo_mysql');
$chequeos[] = ['La extensión pdo_mysql está cargada', $tienePdo,
    $tienePdo ? 'ok' : 'falta descomentar extension=pdo_mysql en el php.ini'];

try {
    $pdo = Conexion::getInstancia()->getPdo();
    $chequeos[] = ['Conecta con MariaDB', true,
        'servidor ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)];

    $mismaInstancia = Conexion::getInstancia() === Conexion::getInstancia();
    $chequeos[] = ['El Singleton devuelve una sola conexión', $mismaInstancia, 'ok'];

    $usuarioDAO = new UsuarioDAO();
    $salonDAO   = new SalonDAO();
    $ticketDAO  = new TicketDAO();

    $usuarios = $usuarioDAO->obtenerTodos();
    $salones  = $salonDAO->obtenerTodos();
    $tickets  = $ticketDAO->obtenerTodos();

    $chequeos[] = ['Los DAO leen datos', count($usuarios) > 0,
        count($usuarios) . ' usuarios, ' . count($salones) . ' salones, ' . count($tickets) . ' tickets'];

    $conTilde = false;
    foreach ($usuarios as $u) {
        if (strpos($u['nombre_usuario'], 'í') !== false || strpos($u['nombre_usuario'], 'é') !== false) {
            $conTilde = true;
        }
    }
    $chequeos[] = ['Las tildes llegan bien', $conTilde, 'utf8mb4 de punta a punta'];

} catch (Exception $e) {
    $chequeos[] = ['Conecta con MariaDB', false, $e->getMessage()];
}

$todoBien = true;
foreach ($chequeos as $c) {
    if (!$c[1]) {
        $todoBien = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI - Verificación del entorno</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem auto; max-width: 60rem; padding: 0 1rem; }
        h1 { font-size: 1.4rem; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 2rem; }
        th, td { border: 1px solid #ccc; padding: .5rem .7rem; text-align: left; font-size: .9rem; }
        th { background: #f2f2f2; }
        .ok { color: #14612b; font-weight: bold; }
        .mal { color: #a11; font-weight: bold; }
        .resumen { padding: 1rem; border-radius: .4rem; margin-bottom: 2rem; }
        .resumen.ok { background: #e6f5ea; }
        .resumen.mal { background: #fbe9e9; }
    </style>
</head>

<body>
    <h1>Verificación del entorno SGRSI</h1>

    <div class="resumen <?php echo $todoBien ? 'ok' : 'mal'; ?>">
        <?php if ($todoBien) { ?>
            Todo conectado. El navegador habló con el servidor, el servidor ejecutó PHP,
            PHP consultó MariaDB por PDO y los datos volvieron hasta esta tabla.
        <?php } else { ?>
            Hay algo cortado en la cadena. Mirá cuál fila da error.
        <?php } ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Qué se verifica</th>
                <th>Resultado</th>
                <th>Detalle</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($chequeos as $c) { ?>
                <tr>
                    <td><?php echo $c[0]; ?></td>
                    <td class="<?php echo $c[1] ? 'ok' : 'mal'; ?>">
                        <?php echo $c[1] ? 'OK' : 'FALLA'; ?>
                    </td>
                    <td><?php echo htmlspecialchars($c[2]); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php if (count($tickets) > 0) { ?>
        <h1>Tickets, salidos de la base</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Salón</th>
                    <th>Equipo</th>
                    <th>Defecto</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Solicitante</th>
                    <th>Responsable</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t) { ?>
                    <tr>
                        <td><?php echo $t['id_ticket']; ?></td>
                        <td><?php echo date('d/m/y H:i', strtotime($t['fecha_hora_alta'])); ?></td>
                        <td><?php echo htmlspecialchars($t['nombre_salon']); ?></td>
                        <td><?php echo htmlspecialchars($t['nombre_equipo']); ?></td>
                        <td><?php echo htmlspecialchars($t['tipo_de_defecto']); ?></td>
                        <td><?php echo htmlspecialchars($t['prioridad']); ?></td>
                        <td><?php echo htmlspecialchars($t['estado_ticket']); ?></td>
                        <td><?php echo htmlspecialchars($t['nombre_solicitante']); ?></td>
                        <td><?php echo $t['nombre_responsable'] === null ? '—' : htmlspecialchars($t['nombre_responsable']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</body>

</html>
