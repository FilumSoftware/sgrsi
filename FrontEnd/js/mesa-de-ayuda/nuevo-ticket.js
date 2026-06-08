const form = document.querySelector('form');
const inputFecha = document.getElementById('fecha');
const inputHora = document.getElementById('hora');
const selectSalon = document.getElementById('salon');
const selectEquipo = document.getElementById('equipo');
const inputMotivo  = document.getElementById('motivo');
const hoy = new Date();
const anio = hoy.getFullYear();
const mes = String(hoy.getMonth() + 1).padStart(2, '0');
const dia = String(hoy.getDate()).padStart(2, '0');
const horas = String(hoy.getHours()).padStart(2, '0');
const minutos = String(hoy.getMinutes()).padStart(2, '0');

inputFecha.value = anio + '-' + mes + '-' + dia;
inputHora.value  = horas + ':' + minutos;

function mostrarError(campo, mensaje) {

    const errorExistente = campo.parentElement.querySelector('.error-msg');

    if (!errorExistente) {
        const parrafoError = document.createElement('p');
        parrafoError.classList.add('error-msg');
        parrafoError.textContent = mensaje;
        campo.parentElement.appendChild(parrafoError);
    }
    campo.classList.add('input-error');
}

function limpiarError(campo) {
    const errorExistente = campo.parentElement.querySelector('.error-msg');

    if (errorExistente) {
        errorExistente.remove();
    }

    campo.classList.remove('input-error');
}

function validarFormulario() {
    let valido = true;

    if (inputFecha.value === '') {
        mostrarError(inputFecha, 'La fecha es obligatoria.');
        valido = false;
    } else {
        limpiarError(inputFecha);
    }

    if (inputHora.value === '') {
        mostrarError(inputHora, 'La hora es obligatoria.');
        valido = false;
    } else {
        limpiarError(inputHora);
    }

    if (selectSalon.value === '') {
        mostrarError(selectSalon, 'Elegí un salón.');
        valido = false;
    } else {
        limpiarError(selectSalon);
    }

    if (selectEquipo.value === '') {
        mostrarError(selectEquipo, 'Elegí un equipo.');
        valido = false;
    } else {
        limpiarError(selectEquipo);
    }

    if (inputMotivo.value.trim() === '') {
        mostrarError(inputMotivo, 'El motivo es obligatorio.');
        valido = false;
    } else if (inputMotivo.value.trim().length < 10) {
        mostrarError(inputMotivo, 'El motivo debe tener al menos 10 caracteres.');
        valido = false;
    } else {
        limpiarError(inputMotivo);
    }

    return valido;
}

form.addEventListener('submit', function(evento) {

    evento.preventDefault();

    const esValido = validarFormulario();

    if (esValido) {
        alert('Ticket registrado correctamente.');
        form.reset();
        inputFecha.value = anio + '-' + mes + '-' + dia;
        inputHora.value  = horas + ':' + minutos;
    }
});

inputFecha.addEventListener('input', function() { limpiarError(inputFecha); });
inputHora.addEventListener('input', function() { limpiarError(inputHora); });
selectSalon.addEventListener('change', function() { limpiarError(selectSalon); });
selectEquipo.addEventListener('change', function() { limpiarError(selectEquipo); });
inputMotivo.addEventListener('input', function() { limpiarError(inputMotivo); });
