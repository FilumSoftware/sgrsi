const formulario = document.getElementById('form-ticket');
const inputFecha = document.getElementById('fecha');
const inputHora = document.getElementById('hora');
const selectSalon = document.getElementById('salon');
const selectEquipo = document.getElementById('equipo');
const inputMotivo = document.getElementById('motivo');

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

function mostrarError(campo, idError, mensaje) {
    const error = document.getElementById(idError);
    error.textContent = mensaje;
    error.style.display = 'block';
    campo.classList.add('input-error');
}

function limpiarError(campo, idError) {
    const error = document.getElementById(idError);
    error.style.display = 'none';
    campo.classList.remove('input-error');
}

function validarFormulario() {
    let valido = true;

    if (inputFecha.value === '') {
        mostrarError(inputFecha, 'error-fecha', 'La fecha es obligatoria.');
        valido = false;
    } else {
        limpiarError(inputFecha, 'error-fecha');
    }

    if (inputHora.value === '') {
        mostrarError(inputHora, 'error-hora', 'La hora es obligatoria.');
        valido = false;
    } else {
        limpiarError(inputHora, 'error-hora');
    }

    if (selectSalon.value === '') {
        mostrarError(selectSalon, 'error-salon', 'Elegí un salón.');
        valido = false;
    } else {
        limpiarError(selectSalon, 'error-salon');
    }

    if (selectEquipo.value === '') {
        mostrarError(selectEquipo, 'error-equipo', 'Elegí un equipo.');
        valido = false;
    } else {
        limpiarError(selectEquipo, 'error-equipo');
    }

    if (inputMotivo.value.trim() === '') {
        mostrarError(inputMotivo, 'error-motivo', 'El motivo es obligatorio.');
        valido = false;
    } else if (inputMotivo.value.trim().length < 10) {
        mostrarError(inputMotivo, 'error-motivo', 'El motivo debe tener al menos 10 caracteres.');
        valido = false;
    } else {
        limpiarError(inputMotivo, 'error-motivo');
    }

    return valido;
}

ponerFechaYHoraDeHoy();

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();

    if (validarFormulario()) {
        alert('Ticket registrado correctamente.');
        formulario.reset();
        ponerFechaYHoraDeHoy();
    }
});

inputFecha.addEventListener('input', function () {
    limpiarError(inputFecha, 'error-fecha');
});

inputHora.addEventListener('input', function () {
    limpiarError(inputHora, 'error-hora');
});

selectSalon.addEventListener('change', function () {
    limpiarError(selectSalon, 'error-salon');
});

selectEquipo.addEventListener('change', function () {
    limpiarError(selectEquipo, 'error-equipo');
});

inputMotivo.addEventListener('input', function () {
    limpiarError(inputMotivo, 'error-motivo');
});
