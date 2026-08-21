function editar(id) {
    window.location.href = `detalle-ticket.html?id=${id}`;
}

function eliminar(id) {
    const confirmar = confirm('¿Eliminar este registro del historial?');
    if (!confirmar) return;

    const fila = document.querySelector(`button[onclick="eliminar(${id})"]`).closest('tr');
    fila.remove();
}