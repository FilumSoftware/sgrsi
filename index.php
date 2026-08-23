<?php

require_once __DIR__ . '/BackEnd/logica/Autenticador.php';
require_once __DIR__ . '/BackEnd/logica/ControlAcceso.php';

Sesion::iniciar();

if (Sesion::hayUsuario()) {
    header('Location: ' . ControlAcceso::pantallaInicial());
    exit;
}

$mensaje = '';
$ciEnviada = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ciEnviada = isset($_POST['ci']) ? trim($_POST['ci']) : '';
    $clave     = isset($_POST['password']) ? $_POST['password'] : '';

    $auth      = new Autenticador();
    $resultado = $auth->ingresar($ciEnviada, $clave);

    if ($resultado['ok']) {
        header('Location: ' . ControlAcceso::pantallaInicial());
        exit;
    }

    http_response_code($resultado['codigo']);
    $mensaje = $resultado['mensaje'];
} elseif (isset($_GET['motivo']) && $_GET['motivo'] === 'sesion') {
    http_response_code(401);
    $mensaje = 'Iniciá sesión para entrar al sistema.';
} elseif (isset($_GET['motivo']) && $_GET['motivo'] === 'salida') {
    $mensaje = 'Cerraste sesión.';
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="FrontEnd/css/main.css">
    <link rel="stylesheet" href="FrontEnd/css/login.css">
</head>

<body>
    <main class="pagina">

        <section class="login-box">

            <header>
                <img src="FrontEnd/img/logo.png" alt="Filum Software" class="logo">
                <h2>SGRSI</h2>
                <p class="subtitulo">Ingresá tus datos para acceder al sistema</p>
            </header>

            <?php if ($mensaje !== '') { ?>
                <p class="aviso"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } ?>

            <form id="form-login" action="index.php" method="post">
                <label for="ci">Cédula</label>
                <input type="text" id="ci" name="ci" placeholder="52618740"
                       value="<?php echo htmlspecialchars($ciEnviada, ENT_QUOTES, 'UTF-8'); ?>" required>
                <p class="error-mensaje" id="error-ci"></p>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <p class="error-mensaje" id="error-password"></p>

                <button type="submit" class="btn-ingresar">Iniciar sesión</button>
            </form>

        </section>

        <footer>
            <p class="pie">SGRSI v2.0 — FILUM SOFTWARE © 2026</p>
        </footer>

    </main>
    <script src="FrontEnd/js/validacion.js"></script>
    <script src="FrontEnd/js/login.js"></script>
</body>

</html>
