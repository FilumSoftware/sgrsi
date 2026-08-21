function editar(id) {
    window.location.href = `detalle-equipo.html?id=${id}`;
}

function eliminar(id) {
    const confirmar = confirm('¿Eliminar este equipo del inventario?');
    if (!confirmar) return;

    const fila = document.querySelector(`button[onclick="eliminar(${id})"]`).closest('tr');
    fila.remove();
}