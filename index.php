<?php

require_once __DIR__ . '/BackEnd/logica/Autenticador.php';
require_once __DIR__ . '/BackEnd/logica/ControlAcceso.php';
require_once __DIR__ . '/BackEnd/logica/Idioma.php';

Sesion::iniciar();
Idioma::iniciar();

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
    $mensaje = Idioma::t($resultado['mensaje']);
} elseif (isset($_GET['motivo']) && $_GET['motivo'] === 'sesion') {
    http_response_code(401);
    $mensaje = Idioma::t('login_motivo_sesion');
} elseif (isset($_GET['motivo']) && $_GET['motivo'] === 'salida') {
    $mensaje = Idioma::t('login_motivo_salida');
}

?>
<!DOCTYPE html>
<html lang="<?php echo Idioma::t('html_lang'); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Idioma::e('login_titulo'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="FrontEnd/css/main.css">
    <link rel="stylesheet" href="FrontEnd/css/login.css">
</head>

<body>
    <main class="pagina">

        <section class="login-box">

            <p class="idioma-barra">
                <a class="btn-idioma" href="<?php echo Idioma::hrefAlternativo(); ?>"
                   hreflang="<?php echo Idioma::alternativo(); ?>"
                   title="<?php echo Idioma::e('idioma_titulo'); ?>"><?php echo Idioma::e('idioma_boton'); ?></a>
            </p>

            <header>
                <img src="FrontEnd/img/logo.png" alt="<?php echo Idioma::e('logo_login_alt'); ?>" class="logo">
                <h2>SGRSI</h2>
                <p class="subtitulo"><?php echo Idioma::e('login_subtitulo'); ?></p>
            </header>

            <?php if ($mensaje !== '') { ?>
                <p class="aviso"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php } ?>

            <form id="form-login" action="index.php" method="post">
                <label for="ci"><?php echo Idioma::e('login_cedula'); ?></label>
                <input type="text" id="ci" name="ci" placeholder="52618740"
                       value="<?php echo htmlspecialchars($ciEnviada, ENT_QUOTES, 'UTF-8'); ?>" required>
                <p class="error-mensaje" id="error-ci"></p>

                <label for="password"><?php echo Idioma::e('login_contrasena'); ?></label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <p class="error-mensaje" id="error-password"></p>

                <button type="submit" class="btn-ingresar"><?php echo Idioma::e('login_ingresar'); ?></button>
            </form>

        </section>

        <footer>
            <p class="pie"><?php echo Idioma::e('pie'); ?></p>
        </footer>

    </main>
    <script>const TEXTOS = <?php echo Idioma::json(); ?>;</script>
    <script src="FrontEnd/js/validacion.js"></script>
    <script src="FrontEnd/js/login.js"></script>
</body>

</html>
