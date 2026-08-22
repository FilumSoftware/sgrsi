// Los id de cada fila, en el mismo orden en que aparecen en la tabla.
// Son datos de prueba: cuando el backend arme la tabla, estos valores
// los va a escribir PHP.
const ids = [1, 2, 3];

const filas = document.querySelectorAll('tbody tr');
const botonesEditar = document.querySelectorAll('.btn-editar');
const botonesEliminar = document.querySelectorAll('.btn-eliminar');

for (let i = 0; i < botonesEditar.length; i++) {
    botonesEditar[i].addEventListener('click', function () {
        window.location.href = 'detalle-ticket.html?id=' + ids[i];
    });
}

for (let i = 0; i < botonesEliminar.length; i++) {
    botonesEliminar[i].addEventListener('click', function () {
        const confirmar = confirm('¿Eliminar este ticket?');
        if (confirmar) {
            filas[i].classList.add('oculta');
        }
    });
}
