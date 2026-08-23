const formulario = document.getElementById('form-registro');
const inputCi = document.getElementById('ci');
const inputNombre = document.getElementById('nombre');
const inputPassword = document.getElementById('password');
const inputConfirmar = document.getElementById('confirmar-password');

const CI_VALIDA = /^\d{8}$/;
const LARGO_MINIMO_CLAVE = 8;

function validarCi() {
    if (!CI_VALIDA.test(inputCi.value.trim())) {
        mostrarError(inputCi, 'error-ci', 'La cédula son ocho dígitos, sin puntos ni guiones.');
        return false;
    }

    limpiarError(inputCi, 'error-ci');
    return true;
}

function validarPassword() {
    if (inputPassword.value.length < LARGO_MINIMO_CLAVE) {
        mostrarError(inputPassword, 'error-password', 'La contraseña debe tener al menos ' + LARGO_MINIMO_CLAVE + ' caracteres.');
        return false;
    }

    limpiarError(inputPassword, 'error-password');
    return true;
}

function validarConfirmacion() {
    if (inputConfirmar.value !== inputPassword.value) {
        mostrarError(inputConfirmar, 'error-confirmar-password', 'Las contraseñas no coinciden.');
        return false;
    }

    limpiarError(inputConfirmar, 'error-confirmar-password');
    return true;
}

formulario.addEventListener('submit', function (evento) {
    const ciOk = validarCi();
    const nombreOk = validarTexto(inputNombre, 'error-nombre', 1, 'el nombre');
    const claveOk = validarPassword();
    const confirmacionOk = validarConfirmacion();

    if (!ciOk || !nombreOk || !claveOk || !confirmacionOk) {
        evento.preventDefault();
    }
});

limpiarAlEscribir(inputNombre, 'error-nombre', 1);
