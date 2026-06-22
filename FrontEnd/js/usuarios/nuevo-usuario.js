document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('form-usuario');

    formulario.addEventListener('submit', function(evento) {
        let formularioValido = true;

        const nombre = document.getElementById('nombre');
        const errorNombre = document.getElementById('error-nombre');
        if (nombre.value.trim() === '') {
            errorNombre.textContent = 'El nombre es obligatorio.';
            errorNombre.style.display = 'block';
            nombre.classList.add('input-error');
            formularioValido = false;
        } else {
            errorNombre.style.display = 'none';
            nombre.classList.remove('input-error');
        }

        const email = document.getElementById('email');
        const errorEmail = document.getElementById('error-email');
        if (email.value.trim() === '' || !email.value.includes('@')) {
            errorEmail.textContent = 'Ingresá un correo válido.';
            errorEmail.style.display = 'block';
            email.classList.add('input-error');
            formularioValido = false;
        } else {
            errorEmail.style.display = 'none';
            email.classList.remove('input-error');
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

        const confirmarPassword = document.getElementById('confirmar-password');
        const errorConfirmar = document.getElementById('error-confirmar-password');
        if (confirmarPassword.value !== password.value || confirmarPassword.value === '') {
            errorConfirmar.textContent = 'Las contraseñas no coinciden.';
            errorConfirmar.style.display = 'block';
            confirmarPassword.classList.add('input-error');
            formularioValido = false;
        } else {
            errorConfirmar.style.display = 'none';
            confirmarPassword.classList.remove('input-error');
        }

        if (!formularioValido) {
            evento.preventDefault();
        }
    });
});