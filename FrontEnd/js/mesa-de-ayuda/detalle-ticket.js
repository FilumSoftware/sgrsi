document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('form-detalle-ticket');

    formulario.addEventListener('submit', function(evento) {
        let formularioValido = true;

        const responsable = document.getElementById('responsable');
        const errorResponsable = document.getElementById('error-responsable');
        if (responsable.value === '') {
            errorResponsable.textContent = 'Asigná un técnico responsable.';
            errorResponsable.style.display = 'block';
            responsable.classList.add('input-error');
            formularioValido = false;
        } else {
            errorResponsable.style.display = 'none';
            responsable.classList.remove('input-error');
        }

        if (!formularioValido) {
            evento.preventDefault();
        }
    });
});