const formulario = document.getElementById('form-uso-sala');
const horaEntrada = document.getElementById('entrada');
const horaSalida = document.getElementById('salida');
const errorSalida = document.getElementById('error-salida');

const botonAgregarPc = document.getElementById('agregar-pc');
const filasPc = document.querySelectorAll('.fila-pc');

// Las filas las arma el servidor con los equipos del salón elegido, así que
// puede no haber ninguna todavía.
if (botonAgregarPc !== null) {

    let pcsVisibles = document.querySelectorAll('.fila-pc:not(.oculta)').length;

    if (pcsVisibles === filasPc.length) {
        botonAgregarPc.classList.add('oculta');
    }

    botonAgregarPc.addEventListener('click', function () {
        if (pcsVisibles < filasPc.length) {
            filasPc[pcsVisibles].classList.remove('oculta');
            pcsVisibles = pcsVisibles + 1;
        }

        if (pcsVisibles === filasPc.length) {
            botonAgregarPc.classList.add('oculta');
        }
    });
}

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
