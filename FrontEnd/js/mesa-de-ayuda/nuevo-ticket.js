const formulario = document.getElementById('form-ticket');
const inputFecha = document.getElementById('fecha');
const inputHora = document.getElementById('hora');
const inputMotivo = document.getElementById('motivo');

const MOTIVO_MINIMO = 10;

function dosDigitos(numero) {
    if (numero < 10) {
        return '0' + numero;
    }
    return '' + numero;
}

function ponerFechaYHoraDeHoy() {
    const hoy = new Date();
    const anio = hoy.getFullYear();
    const mes = dosDigitos(hoy.getMonth() + 1);
    const dia = dosDigitos(hoy.getDate());
    const horas = dosDigitos(hoy.getHours());
    const minutos = dosDigitos(hoy.getMinutes());

    inputFecha.value = anio + '-' + mes + '-' + dia;
    inputHora.value = horas + ':' + minutos;
}

ponerFechaYHoraDeHoy();

formulario.addEventListener('submit', function (evento) {
    if (!validarTexto(inputMotivo, 'error-motivo', MOTIVO_MINIMO, 'el motivo')) {
        evento.preventDefault();
    }
});

limpiarAlEscribir(inputMotivo, 'error-motivo', MOTIVO_MINIMO);
