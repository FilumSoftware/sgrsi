const formulario = document.getElementById('form-equipo');
const inputNombre = document.getElementById('nombre');
const resultado = document.getElementById('resultado-equipo');
const boton = document.getElementById('btn-registrar');

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!validarTexto(inputNombre, 'error-nombre', 1, 'el nombre del equipo')) return;
    if (!formulario.reportValidity()) return;

    boton.disabled = true;
    fetch('../../../BackEnd/api/inventario.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': formulario.dataset.csrf
        },
        body: JSON.stringify({
            nombre_equipo: inputNombre.value.trim(),
            nombre_salon: document.getElementById('salon').value,
            descripcion: document.getElementById('extras').value.trim()
        })
    })
        .then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok || datos.status !== 'success') {
                    throw new Error(datos.message || 'No se pudo registrar el equipo.');
                }
                return datos;
            });
        })
        .then(function (datos) {
            resultado.textContent = datos.message + ' N.º ' + datos.data.id_equipo + '.';
            resultado.classList.add('mensaje-exito');
            resultado.style.display = 'block';
            formulario.reset();
        })
        .catch(function (error) {
            resultado.textContent = error.message || 'No se pudo registrar el equipo.';
            resultado.classList.remove('mensaje-exito');
            resultado.style.display = 'block';
        })
        .finally(function () {
            boton.disabled = false;
        });
});

limpiarAlEscribir(inputNombre, 'error-nombre', 1);
