document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('form-solicitud');

    formulario.addEventListener('submit', function(evento) {
        let formularioValido = true;

        const fecha = document.getElementById('fecha');
        const errorFecha = document.getElementById('error-fecha');
        if (fecha.value.trim() === '') {
            errorFecha.textContent = 'La fecha es obligatoria.';
            errorFecha.style.display = 'block';
            fecha.classList.add('input-error');
            formularioValido = false;
        } else {
            errorFecha.style.display = 'none';
            fecha.classList.remove('input-error');
        }

        const motivo = document.getElementById('motivo');
        const errorMotivo = document.getElementById('error-motivo');
        if (motivo.value.trim() === '') {
            errorMotivo.textContent = 'El motivo es obligatorio.';
            errorMotivo.style.display = 'block';
            motivo.classList.add('input-error');
            formularioValido = false;
        } else {
            errorMotivo.style.display = 'none';
            motivo.classList.remove('input-error');
        }

        if (!formularioValido) {
            evento.preventDefault();
        }
    });
});