<?php

require_once __DIR__ . '/../../../BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/../../../BackEnd/logica/GestorUsuarios.php';
require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php';

Idioma::iniciar();

ControlAcceso::exigirRol(['Coordinador'], '../../../index.php');

$gestor = new GestorUsuarios();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci = ResultadoGestion::campo($_POST, 'ci');
    $estado = ResultadoGestion::campo($_POST, 'estado');
    $resultado = Sesion::verificarTokenApi($_POST['csrf'] ?? '')
        ? $gestor->cambiarEstadoDesdeFormulario($ci, $estado, Sesion::ci())
        : ResultadoGestion::fallo(403, 'La sesión de esta página venció. Volvé a abrirla.');
    $aviso = $resultado['ok']
        ? ($estado === 'Activa' ? 'activada' : 'desactivada')
        : ($resultado['codigo'] === 409 ? 'propia' : ($resultado['codigo'] === 400 ? 'estado' : 'error'));
    header('Location: usuarios.php?aviso=' . $aviso);
    exit;
}

$avisos = [
    'activada'     => ['exito', Idioma::t('aviso_cuenta_habilitada')],
    'desactivada'  => ['exito', Idioma::t('aviso_cuenta_inhabilitada')],
    'creada'       => ['exito', Idioma::t('aviso_usuario_creado')],
    'guardada'     => ['exito', Idioma::t('aviso_guardado')],
    'propia'       => ['error', Idioma::t('aviso_estado_propio')],
    'estado'       => ['error', Idioma::t('aviso_estado_invalido')],
    'noencontrado' => ['error', Idioma::t('aviso_usuario_noexiste')],
    'error'        => ['error', Idioma::t('aviso_error_operacion')],
];

$mensaje = null;
$clave   = isset($_GET['aviso']) ? $_GET['aviso'] : '';

if (isset($avisos[$clave])) {
    $mensaje = ['tipo' => $avisos[$clave][0], 'texto' => $avisos[$clave][1]];
}

try {
    $usuarios = ResultadoGestion::valor($gestor->obtenerTodos());
} catch (Exception $e) {
    error_log('SGRSI usuarios.php obtenerTodos: ' . $e->getMessage());
    $usuarios = [];
    $mensaje  = ['tipo' => 'error', 'texto' => 'No se pudo cargar el listado de usuarios. Intentá de nuevo en unos minutos.'];
}

function v($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="<?php echo Idioma::t('html_lang'); ?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/main.css">
    <title><?php echo Idioma::e('tit_usuarios'); ?></title>
</head>

<body>
    <div class="layout">

        <nav class="sidebar">
            <ul>
                <?php if (Sesion::esCoordinador()) { ?>
                    <li class="sidebar-item"><a href="../dashboard/dashboard.php"><?php echo Idioma::e('nav_dashboard'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../uso-sala/planilla-uso-sala.php"><?php echo Idioma::e('nav_sala'); ?></a></li>
                <?php if (ControlAcceso::puedeAtender()) { ?>
                    <li class="sidebar-item"><a href="../inventario/nuevo-equipo.php"><?php echo Idioma::e('nav_inventario'); ?></a></li>
                <?php } ?>
                <li class="sidebar-item"><a href="../mesa-de-ayuda/nuevo-ticket.php"><?php echo Idioma::e('nav_mesa'); ?></a></li>
                <li class="sidebar-item"><a href="../solicitudes/nueva-solicitud.php"><?php echo Idioma::e('nav_solicitudes'); ?></a></li>
                <li class="sidebar-item activo"><?php echo Idioma::e('nav_usuarios'); ?></li>
                <li class="sidebar-item"><a href="../../cerrar-sesion.php" class="btn-salir"><?php echo Idioma::e('nav_cerrar_sesion'); ?></a></li>
            </ul>
        </nav>

        <main class="main">

            <header class="topbar-nav">
                <nav>
                    <ul>
                        <li class="topbar-item"><a href="nuevo-usuario.php"><?php echo Idioma::e('tab_nuevo_usuario'); ?></a></li>
                        <li class="topbar-item activo"><?php echo Idioma::e('nav_usuarios'); ?></li>
                    </ul>
                </nav>
                <div class="topbar-logo">
                    <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>" hreflang="<?php echo Idioma::alternativo(); ?>" title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
                    <img src="../../img/logo.png" alt="<?php echo Idioma::e('logo_alt'); ?>">
                    <span>SGRSI</span>
                </div>
            </header>

            <section class="content">

                <?php if ($mensaje) { ?>
                    <p class="error-mensaje <?php echo $mensaje['tipo'] === 'exito' ? 'mensaje-exito' : ''; ?>" style="display: block;">
                        <?php echo v($mensaje['texto']); ?>
                    </p>
                <?php } ?>
                <p id="resultado-usuarios" class="error-mensaje" role="status" aria-live="polite"></p>

                <div class="tabla-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>C.I.</th>
                                <th><?php echo Idioma::e('th_nombre'); ?></th>
                                <th><?php echo Idioma::e('th_rol'); ?></th>
                                <th><?php echo Idioma::e('th_estado'); ?></th>
                                <th><?php echo Idioma::e('th_acciones'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="lista-usuarios" data-csrf="<?php echo v(Sesion::tokenApi()); ?>" data-ci="<?php echo v(Sesion::ci()); ?>">
                            <?php if (empty($usuarios)) { ?>
                                <tr>
                                    <td colspan="5"><?php echo Idioma::e('vacio_usuarios'); ?></td>
                                </tr>
                            <?php } ?>

                            <?php foreach ($usuarios as $usuario) { ?>
                                <?php $inactiva = $usuario['estado_cuenta'] === 'Inactiva'; ?>
                                <tr<?php echo $inactiva ? ' class="fila-desactivada"' : ''; ?>>
                                    <td><a class="tabla-enlace" href="detalle-usuario.php?ci=<?php echo urlencode($usuario['ci']); ?>"><?php echo v($usuario['ci']); ?></a></td>
                                    <td><?php echo v($usuario['nombre_usuario']); ?></td>
                                    <td><?php echo v($usuario['tipo_de_usuario']); ?></td>
                                    <td><?php echo v($usuario['estado_cuenta']); ?></td>
                                    <td class="acciones">
                                        <a class="btn-editar" href="detalle-usuario.php?ci=<?php echo urlencode($usuario['ci']); ?>&amp;modo=editar"><?php echo Idioma::e('btn_editar'); ?></a>

                                        <?php if ($usuario['ci'] === Sesion::ci()) { ?>
                                            <span class="sin-accion"><?php echo Idioma::e('txt_tu_cuenta'); ?></span>
                                        <?php } else { ?>
                                            <form action="usuarios.php" method="post" class="form-en-linea">
                                                <input type="hidden" name="csrf" value="<?php echo v(Sesion::tokenApi()); ?>">
                                                <input type="hidden" name="ci" value="<?php echo v($usuario['ci']); ?>">
                                                <input type="hidden" name="estado" value="<?php echo $inactiva ? 'Activa' : 'Inactiva'; ?>">
                                                <button type="submit" class="<?php echo $inactiva ? 'btn-editar' : 'btn-eliminar'; ?>" data-confirmar="<?php echo $inactiva ? 'habilitar' : 'inhabilitar'; ?>">
                                                    <?php echo $inactiva ? Idioma::e('js_habilitar') : Idioma::e('js_inhabilitar'); ?>
                                                </button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="../../js/usuarios/usuarios.js"></script>
</body>

</html>
