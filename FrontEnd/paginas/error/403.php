<?php require_once __DIR__ . '/../../../BackEnd/logica/Idioma.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo Idioma::t('html_lang'); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI - <?php echo Idioma::e('error403_titulo'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/login.css">
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
                <h2><?php echo Idioma::e('error403_titulo'); ?></h2>
                <p class="subtitulo"><?php echo Idioma::e('error403_detalle'); ?></p>
            </header>

            <p class="pie"><a href="../../../<?php echo ControlAcceso::pantallaInicial(); ?>"><?php echo Idioma::e('error403_volver'); ?></a></p>

        </section>

        <footer>
            <p class="pie"><?php echo Idioma::e('pie'); ?></p>
        </footer>

    </main>
</body>

</html>
