const inputFecha = document.getElementById('fecha');
const inputHora = document.getElementById('hora');

function dosDigitos(numero) {
    if (numero < 10) {
        return '0' + numero;
    }
    return '' + numero;
}

const hoy = new Date();
const anio = hoy.getFullYear();
const mes = dosDigitos(hoy.getMonth() + 1);
const dia = dosDigitos(hoy.getDate());
const horas = dosDigitos(hoy.getHours());
const minutos = dosDigitos(hoy.getMinutes());

inputFecha.value = anio + '-' + mes + '-' + dia;
inputHora.value = horas + ':' + minutos;
