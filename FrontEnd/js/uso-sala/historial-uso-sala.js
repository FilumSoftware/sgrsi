const botonesEliminar = document.querySelectorAll('button[data-confirmar]');

for (let i = 0; i < botonesEliminar.length; i++) {
    botonesEliminar[i].addEventListener('click', function (evento) {
        if (!confirm('¿Eliminar este registro del historial? No se puede deshacer.')) {
            evento.preventDefault();
        }
    });
}
