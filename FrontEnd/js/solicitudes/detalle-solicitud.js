const formulario = document.getElementById('form-detalle-solicitud');
const inputMotivo = document.getElementById('motivo');

const MOTIVO_MINIMO = 10;

// Al solicitante el formulario le llega deshabilitado: no hay nada que validar.
if (!inputMotivo.disabled) {

    formulario.addEventListener('submit', function (evento) {
        if (!validarTexto(inputMotivo, 'error-motivo', MOTIVO_MINIMO, 'el motivo')) {
            evento.preventDefault();
        }
    });

    limpiarAlEscribir(inputMotivo, 'error-motivo', MOTIVO_MINIMO);
}
