document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('form-uso-sala');

    formulario.addEventListener('submit', function(evento) {
        let formularioValido = true;

        const campos = ['fecha', 'hora-entrada', 'hora-salida', 'turno', 'salon'];

        for (let i = 0; i < campos.length; i++) {
            const campo = document.getElementById(campos[i]);
            const errorCampo = document.getElementById('error-' + campos[i]);

            if (campo.value.trim() === '') {
                errorCampo.textContent = 'Este campo es obligatorio.';
                errorCampo.style.display = 'block';
                campo.classList.add('input-error');
                formularioValido = false;
            } else {
                errorCampo.style.display = 'none';
                campo.classList.remove('input-error');
            }
        }

        const horaEntrada = document.getElementById('hora-entrada');
        const horaSalida = document.getElementById('hora-salida');
        const errorSalida = document.getElementById('error-hora-salida');
        if (horaEntrada.value && horaSalida.value && horaSalida.value <= horaEntrada.value) {
            errorSalida.textContent = 'La hora de salida debe ser posterior a la de entrada.';
            errorSalida.style.display = 'block';
            horaSalida.classList.add('input-error');
            formularioValido = false;
        }

        evento.preventDefault();

        if (formularioValido) {
            alert('Planilla registrada correctamente.');
        }
    });
});