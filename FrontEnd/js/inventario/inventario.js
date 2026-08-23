const ids = [1, 2, 3];

const filas = document.querySelectorAll('tbody tr');
const botonesEditar = document.querySelectorAll('.btn-editar');
const botonesEliminar = document.querySelectorAll('.btn-eliminar');

for (let i = 0; i < botonesEditar.length; i++) {
    botonesEditar[i].addEventListener('click', function () {
        window.location.href = 'detalle-equipo.html?id=' + ids[i];
    });
}

for (let i = 0; i < botonesEliminar.length; i++) {
    botonesEliminar[i].addEventListener('click', function () {
        const confirmar = confirm('¿Eliminar este equipo del inventario?');
        if (confirmar) {
            filas[i].classList.add('oculta');
        }
    });
}
