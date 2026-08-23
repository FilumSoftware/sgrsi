const botonesEstado = document.querySelectorAll('button[data-confirmar]');

for (let i = 0; i < botonesEstado.length; i++) {
    botonesEstado[i].addEventListener('click', function (evento) {
        if (!confirm('¿Seguro que querés cambiar el estado de esta cuenta?')) {
            evento.preventDefault();
        }
    });
}
