const formulario = document.getElementById('form-solicitud');
const salon = document.getElementById('salon');
const motivo = document.getElementById('motivo');
const resultado = document.getElementById('resultado-solicitud');
const boton = document.getElementById('btn-enviar');

function leerRespuesta(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultado(mensaje, exito) {
    resultado.textContent = mensaje;
    resultado.classList.toggle('mensaje-exito', exito);
    resultado.style.display = 'block';
}

fetch('../../../BackEnd/api/catalogos.php?tipo=salones', { credentials: 'same-origin' })
    .then(leerRespuesta)
    .then(function (datos) {
        salon.replaceChildren(new Option('Elija una opción', ''));
        datos.data.forEach(function (fila) {
            salon.add(new Option(fila.nombre_salon, fila.nombre_salon));
        });
    })
    .catch(function (error) {
        mostrarResultado(error.message, false);
    });

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!validarTexto(motivo, 'error-motivo', 10, 'el motivo')) return;
    if (!formulario.reportValidity()) return;

    boton.disabled = true;
    fetch('../../../BackEnd/api/solicitudes.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': formulario.dataset.csrf
        },
        body: JSON.stringify({
            nombre_salon: salon.value,
            prioridad: document.getElementById('prioridad').value,
            descripcion: motivo.value.trim()
        })
    })
        .then(leerRespuesta)
        .then(function (datos) {
            mostrarResultado(datos.message + ' N.º ' + datos.data.id_solicitud + '.', true);
            formulario.reset();
        })
        .catch(function (error) {
            mostrarResultado(error.message, false);
        })
        .finally(function () {
            boton.disabled = false;
        });
});

limpiarAlEscribir(motivo, 'error-motivo', 10);
