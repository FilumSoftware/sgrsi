const formulario = document.getElementById('form-usuario');
const inputCi = document.getElementById('ci');
const inputNombre = document.getElementById('nombre');
const password = document.getElementById('password');
const confirmarPassword = document.getElementById('confirmar-password');
const resultadoUsuario = document.getElementById('resultado-usuario');

const CI_VALIDA = /^\d{8}$/;
const LARGO_MINIMO_CLAVE = 8;

function validarCi() {
    if (!CI_VALIDA.test(inputCi.value.trim())) {
        mostrarError(inputCi, 'error-ci', TEXTOS.js_ci_invalida);
        return false;
    }

    limpiarError(inputCi, 'error-ci');
    return true;
}

function validarPassword() {
    if (password.value.length < LARGO_MINIMO_CLAVE) {
        mostrarError(password, 'error-password', TEXTOS.js_clave_corta.replace('{n}', LARGO_MINIMO_CLAVE));
        return false;
    }

    limpiarError(password, 'error-password');
    return true;
}

function validarConfirmacion() {
    if (confirmarPassword.value !== password.value) {
        mostrarError(confirmarPassword, 'error-confirmar-password', TEXTOS.js_claves_no_coinciden);
        return false;
    }

    limpiarError(confirmarPassword, 'error-confirmar-password');
    return true;
}

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    const ciOk = validarCi();
    const nombreOk = validarTexto(inputNombre, 'error-nombre', 1, TEXTOS.etq_nombre);
    const claveOk = validarPassword();
    const confirmacionOk = validarConfirmacion();

    if (!ciOk || !nombreOk || !claveOk || !confirmacionOk || !formulario.reportValidity()) return;

    const boton = formulario.querySelector('[type="submit"]');
    boton.disabled = true;
    fetch('../../../BackEnd/api/usuarios.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': formulario.dataset.csrf
        },
        body: JSON.stringify({
            ci: inputCi.value.trim(),
            nombre_usuario: inputNombre.value.trim(),
            tipo_de_usuario: document.getElementById('tipo-usuario').value,
            contrasena: password.value
        })
    })
        .then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok || datos.status !== 'success') throw new Error(datos.message || TEXTOS.js_error_crear_usuario);
                return datos;
            });
        })
        .then(function (datos) {
            resultadoUsuario.textContent = TEXTOS[datos.message] || datos.message;
            resultadoUsuario.classList.add('mensaje-exito');
            resultadoUsuario.style.display = 'block';
            formulario.reset();
        })
        .catch(function (error) {
            resultadoUsuario.textContent = TEXTOS[error.message] || error.message || TEXTOS.js_error_crear_usuario;
            resultadoUsuario.classList.remove('mensaje-exito');
            resultadoUsuario.style.display = 'block';
        })
        .finally(function () {
            boton.disabled = false;
        });
});

limpiarAlEscribir(inputNombre, 'error-nombre', 1);
