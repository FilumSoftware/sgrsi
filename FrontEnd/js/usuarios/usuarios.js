function editar(id) {
    window.location.href = `detalle-usuario.html?id=${id}`;
}

function desactivar(id) {
    const confirmar = confirm('¿Desactivar este usuario?');
    if (!confirmar) return;

    const fila = document.querySelector(`button[onclick="desactivar(${id})"]`).closest('tr');
    fila.style.opacity = '0.5';
}
