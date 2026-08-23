const formulario = document.getElementById('form-login');
const inputCi = document.getElementById('ci');
const inputPassword = document.getElementById('password');

const CI_VALIDA = /^\d{7,8}$/;
const LARGO_MINIMO_CLAVE = 8;

function validarCi() {
    if (!CI_VALIDA.test(inputCi.value.trim())) {
        mostrarError(inputCi, 'error-ci', 'Ingresá una cédula válida, sin puntos ni guiones.');
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

formulario.addEventListener('submit', function (evento) {
    const ciValida = validarCi();
    const passwordValida = validarPassword();

    if (!ciValida || !passwordValida) {
        evento.preventDefault();
    }
});
