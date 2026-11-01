const formulario = document.getElementById('form-detalle-equipo');
const inputNombre = document.getElementById('nombre');
const resultado = document.getElementById('resultado-guardado');
const idEquipo = document.getElementById('id-equipo').value;
let formularioModificado = false;

formulario.addEventListener('input', function () { formularioModificado = true; });
formulario.addEventListener('change', function () { formularioModificado = true; });

fetch('../../../BackEnd/api/inventario.php?id=' + encodeURIComponent(idEquipo), {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
})
    .then(function (respuesta) {
        return respuesta.json().then(function (datos) {
            if (!respuesta.ok || datos.status !== 'success') throw new Error(datos.message || TEXTOS.js_error_cargar_equipo);
            return datos.data;
        });
    })
    .then(function (equipo) {
        if (formularioModificado) return;
        inputNombre.value = equipo.nombre_equipo;
        document.getElementById('salon').value = equipo.nombre_salon;
        document.getElementById('extras').value = equipo.descripcion || '';
        document.getElementById('estado').value = equipo.estado_equipo;
    })
    .catch(function (error) {
        resultado.textContent = TEXTOS[error.message] || error.message || TEXTOS.js_error_cargar_equipo;
        resultado.classList.remove('mensaje-exito');
        resultado.style.display = 'block';
    });

if (!inputNombre.disabled) {
    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        if (!validarTexto(inputNombre, 'error-nombre', 1, TEXTOS.etq_nombre_equipo)) return;
        if (!formulario.reportValidity()) return;

        const boton = document.getElementById('btn-guardar');
        boton.disabled = true;
        fetch('../../../BackEnd/api/inventario.php?id=' + encodeURIComponent(idEquipo), {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': formulario.dataset.csrf
            },
            body: JSON.stringify({
                nombre_equipo: inputNombre.value.trim(),
                nombre_salon: document.getElementById('salon').value,
                descripcion: document.getElementById('extras').value.trim(),
                estado_equipo: document.getElementById('estado').value
            })
        })
            .then(function (respuesta) {
                return respuesta.json().then(function (datos) {
                    if (!respuesta.ok || datos.status !== 'success') {
                        throw new Error(datos.message || TEXTOS.js_error_guardar_equipo);
                    }
                    return datos;
                });
            })
            .then(function (datos) {
                resultado.textContent = TEXTOS[datos.message] || datos.message;
                resultado.classList.add('mensaje-exito');
                resultado.style.display = 'block';
            })
            .catch(function (error) {
                resultado.textContent = TEXTOS[error.message] || error.message || TEXTOS.js_error_guardar_equipo;
                resultado.classList.remove('mensaje-exito');
                resultado.style.display = 'block';
            })
            .finally(function () {
                boton.disabled = false;
            });
    });

    limpiarAlEscribir(inputNombre, 'error-nombre', 1);
}
