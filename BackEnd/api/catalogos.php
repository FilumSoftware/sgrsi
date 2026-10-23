<?php

require_once __DIR__ . '/Api.php';
require_once __DIR__ . '/../logica/GestorTickets.php';

Api::iniciar();
Api::metodo(['GET']);

try {
    $gestor = new GestorTickets();
    $tipo = Api::parametro('tipo');
    if ($tipo === 'salones') {
        Api::resultado($gestor->obtenerSalones(), 'Salones cargados.');
    }
    if ($tipo === 'equipos') {
        Api::resultado($gestor->obtenerEquiposPorSalon(Api::parametro('salon')), 'Equipos cargados.');
    }
    Api::responder(400, 'error', 'Catálogo inválido.');
} catch (Throwable $e) {
    error_log('SGRSI API catálogos: ' . $e->getMessage());
    Api::responder(500, 'error', 'No se pudo cargar la lista. Intentá de nuevo en unos minutos.');
}
