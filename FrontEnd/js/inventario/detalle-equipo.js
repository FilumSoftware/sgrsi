const formulario = document.getElementById('form-detalle-equipo');
const inputNombre = document.getElementById('nombre');

formulario.addEventListener('submit', function (evento) {
    if (!validarTexto(inputNombre, 'error-nombre', 1, 'el nombre del equipo')) {
        evento.preventDefault();
    }
});

limpiarAlEscribir(inputNombre, 'error-nombre', 1);
