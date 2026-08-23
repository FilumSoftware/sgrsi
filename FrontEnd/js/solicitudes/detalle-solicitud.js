const formulario = document.getElementById('form-detalle-solicitud');
const inputMotivo = document.getElementById('motivo');

const MOTIVO_MINIMO = 10;

if (!inputMotivo.disabled) {

    formulario.addEventListener('submit', function (evento) {
        if (!validarTexto(inputMotivo, 'error-motivo', MOTIVO_MINIMO, 'el motivo')) {
            evento.preventDefault();
        }
    });

    limpiarAlEscribir(inputMotivo, 'error-motivo', MOTIVO_MINIMO);
}
