const formulario = document.getElementById('form-equipo');
const inputNombre = document.getElementById('nombre');
const resultado = document.getElementById('resultado-equipo');
const boton = document.getElementById('btn-registrar');

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!validarTexto(inputNombre, 'error-nombre', 1, TEXTOS.etq_nombre_equipo)) return;
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
                    throw new Error(datos.message || TEXTOS.js_error_registrar_equipo);
                }
                return datos;
            });
        })
        .then(function (datos) {
            resultado.textContent = (TEXTOS[datos.message] || datos.message) + TEXTOS.js_num_prefijo + datos.data.id_equipo + '.';
            resultado.classList.add('mensaje-exito');
            resultado.style.display = 'block';
            formulario.reset();
        })
        .catch(function (error) {
            resultado.textContent = TEXTOS[error.message] || error.message || TEXTOS.js_error_registrar_equipo;
            resultado.classList.remove('mensaje-exito');
            resultado.style.display = 'block';
        })
        .finally(function () {
            boton.disabled = false;
        });
});

limpiarAlEscribir(inputNombre, 'error-nombre', 1);
