document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('form-login');

    formulario.addEventListener('submit', function(evento) {
        let formularioValido = true;

        const ci = document.getElementById('ci');
        const errorCi = document.getElementById('error-ci');
        const ciLimpia = ci.value.trim();
        if (!/^\d{7,8}$/.test(ciLimpia)) {
            errorCi.textContent = 'Ingresá una cédula válida (sin puntos ni guiones).';
            errorCi.style.display = 'block';
            ci.classList.add('input-error');
            formularioValido = false;
        } else {
            errorCi.style.display = 'none';
            ci.classList.remove('input-error');
        }

        const password = document.getElementById('password');
        const errorPassword = document.getElementById('error-password');
        if (password.value.length < 8) {
            errorPassword.textContent = 'La contraseña debe tener al menos 8 caracteres.';
            errorPassword.style.display = 'block';
            password.classList.add('input-error');
            formularioValido = false;
        } else {
            errorPassword.style.display = 'none';
            password.classList.remove('input-error');
        }

        if (!formularioValido) {
            evento.preventDefault();
        }
    });
});