const formulario = document.getElementById('form-solicitud');
const inputMotivo = document.getElementById('motivo');

const MOTIVO_MINIMO = 10;

formulario.addEventListener('submit', function (evento) {
    if (!validarTexto(inputMotivo, 'error-motivo', MOTIVO_MINIMO, 'el motivo')) {
        evento.preventDefault();
    }
});

limpiarAlEscribir(inputMotivo, 'error-motivo', MOTIVO_MINIMO);
