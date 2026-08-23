<?php

require_once __DIR__ . '/../../../BackEnd/logica/Autenticador.php';

Sesion::iniciar();

if (Sesion::hayUsuario()) {
    header('Location: ../dashboard/dashboard.php');
    exit;
}

$mensaje = '';
$exito   = false;
$datos   = ['ci' => '', 'nombre' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos['ci']     = isset($_POST['ci']) ? trim($_POST['ci']) : '';
    $datos['nombre'] = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $clave           = isset($_POST['password']) ? $_POST['password'] : '';
    $claveRepetida   = isset($_POST['confirmar-password']) ? $_POST['confirmar-password'] : '';

    $auth      = new Autenticador();
    $resultado = $auth->registrar($datos['ci'], $datos['nombre'], $clave, $claveRepetida);

    http_response_code($resultado['codigo']);
    $mensaje = $resultado['mensaje'];
    $exito   = $resultado['ok'];

    if ($exito) {
        $datos = ['ci' => '', 'nombre' => ''];
    }
}

function v($texto)
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI - Crear cuenta</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/login.css">
</head>

<body>
    <main class="pagina">

        <section class="login-box">

            <header>
                <img src="../../img/logo.png" alt="Filum Software" class="logo">
                <h2>Crear cuenta</h2>
                <p class="subtitulo">La cuenta queda pendiente hasta que el coordinador la habilite</p>
            </header>

            <?php if ($mensaje !== '') { ?>
                <p class="aviso"><?php echo v($mensaje); ?></p>
            <?php } ?>

            <?php if (!$exito) { ?>
                <form id="form-registro" action="registro.php" method="post">
                    <label for="ci">Cédula</label>
                    <input type="text" id="ci" name="ci" placeholder="52618740"
                           value="<?php echo v($datos['ci']); ?>" required>
                    <p class="error-mensaje" id="error-ci"></p>

                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre"
                           value="<?php echo v($datos['nombre']); ?>" required>
                    <p class="error-mensaje" id="error-nombre"></p>

                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                    <p class="error-mensaje" id="error-password"></p>

                    <label for="confirmar-password">Repetir contraseña</label>
                    <input type="password" id="confirmar-password" name="confirmar-password" required>
                    <p class="error-mensaje" id="error-confirmar-password"></p>

                    <button type="submit" class="btn-ingresar">Crear cuenta</button>
                </form>
            <?php } ?>

            <p class="pie"><a href="../../../index.php">Volver al inicio</a></p>

        </section>

        <footer>
            <p class="pie">SGRSI v2.0 — FILUM SOFTWARE © 2026</p>
        </footer>

    </main>
    <script src="../../js/validacion.js"></script>
    <script src="../../js/usuarios/registro.js"></script>
</body>

</html>
