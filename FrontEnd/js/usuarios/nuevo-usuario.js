const formulario = document.getElementById('form-usuario');
const password = document.getElementById('password');
const confirmarPassword = document.getElementById('confirmar-password');
const errorConfirmar = document.getElementById('error-confirmar-password');

// El resto de los campos los valida el navegador con required. Que las
// dos contrasenas sean iguales es lo unico que no se puede pedir con un
// atributo de HTML, asi que se comprueba aca.
formulario.addEventListener('submit', function (evento) {
    if (confirmarPassword.value !== password.value) {
        errorConfirmar.textContent = 'Las contraseñas no coinciden.';
        errorConfirmar.style.display = 'block';
        confirmarPassword.classList.add('input-error');
        evento.preventDefault();
    } else {
        errorConfirmar.style.display = 'none';
        confirmarPassword.classList.remove('input-error');
    }
});
