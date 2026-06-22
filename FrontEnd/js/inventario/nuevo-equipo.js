document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('form-equipo');

    formulario.addEventListener('submit', function(evento) {
        let formularioValido = true;

        const nombre = document.getElementById('nombre');
        const errorNombre = document.getElementById('error-nombre');
        if (nombre.value.trim() === '') {
            errorNombre.textContent = 'El nombre del equipo es obligatorio.';
            errorNombre.style.display = 'block';
            nombre.classList.add('input-error');
            formularioValido = false;
        } else {
            errorNombre.style.display = 'none';
            nombre.classList.remove('input-error');
        }

        if (!formularioValido) {
            evento.preventDefault();
        }
    });
});