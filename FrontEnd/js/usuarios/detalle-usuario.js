const formulario = document.getElementById('form-detalle-usuario');
const inputNombre = document.getElementById('nombre');

formulario.addEventListener('submit', function (evento) {
    if (!validarTexto(inputNombre, 'error-nombre', 1, 'el nombre')) {
        evento.preventDefault();
    }
});

limpiarAlEscribir(inputNombre, 'error-nombre', 1);
