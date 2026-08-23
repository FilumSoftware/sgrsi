const botonesEstado = document.querySelectorAll('button[data-confirmar]');

for (let i = 0; i < botonesEstado.length; i++) {
    botonesEstado[i].addEventListener('click', function (evento) {
        const accion = botonesEstado[i].dataset.confirmar;

        if (!confirm('¿Seguro que querés ' + accion + ' esta cuenta?')) {
            evento.preventDefault();
        }
    });
}
