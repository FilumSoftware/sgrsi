<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI - Acceso denegado</title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/login.css">
</head>

<body>
    <main class="pagina">

        <section class="login-box">

            <header>
                <h2>Acceso denegado</h2>
                <p class="subtitulo">Tu rol no tiene permiso para entrar a esta pantalla.</p>
            </header>

            <p class="pie"><a href="../../../<?php echo ControlAcceso::pantallaInicial(); ?>">Volver al panel</a></p>

        </section>

        <footer>
            <p class="pie">SGRSI v2.0 — FILUM SOFTWARE © 2026</p>
        </footer>

    </main>
</body>

</html>
