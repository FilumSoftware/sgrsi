<?php

require_once __DIR__ . '/../BackEnd/logica/Autenticador.php';

$auth = new Autenticador();
$auth->salir();

header('Location: ../index.php?motivo=salida');
exit;
