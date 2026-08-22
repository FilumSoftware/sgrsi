const formulario = document.getElementById('form-uso-sala');
const horaEntrada = document.getElementById('hora-entrada');
const horaSalida = document.getElementById('hora-salida');
const errorSalida = document.getElementById('error-hora-salida');

const botonAgregarPc = document.getElementById('agregar-pc');
const filasPc = document.querySelectorAll('.fila-pc');
let pcsVisibles = 1;

botonAgregarPc.addEventListener('click', function () {
    if (pcsVisibles < filasPc.length) {
        filasPc[pcsVisibles].classList.remove('oculta');
        pcsVisibles = pcsVisibles + 1;
    }

    if (pcsVisibles === filasPc.length) {
        botonAgregarPc.classList.add('oculta');
    }
});

formulario.addEventListener('submit', function (evento) {
    if (horaEntrada.value !== '' && horaSalida.value !== '' && horaSalida.value <= horaEntrada.value) {
        errorSalida.textContent = 'La hora de salida debe ser posterior a la de entrada.';
        errorSalida.style.display = 'block';
        horaSalida.classList.add('input-error');
        evento.preventDefault();
    } else {
        errorSalida.style.display = 'none';
        horaSalida.classList.remove('input-error');
    }
});
