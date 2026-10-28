const formulario = document.getElementById('form-detalle-solicitud');
const inputMotivo = document.getElementById('motivo');
const resultadoGuardado = document.getElementById('resultado-guardado');
const idSolicitud = formulario.elements.namedItem('id').value;
let formularioModificado = false;

const MOTIVO_MINIMO = 10;

function leerRespuestaDetalle(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultadoDetalle(mensaje, exito) {
    resultadoGuardado.textContent = mensaje;
    resultadoGuardado.classList.toggle('mensaje-exito', exito);
    resultadoGuardado.style.display = 'block';
}

formulario.addEventListener('input', function () { formularioModificado = true; });
formulario.addEventListener('change', function () { formularioModificado = true; });

fetch('../../../BackEnd/api/solicitudes.php?id=' + encodeURIComponent(idSolicitud), {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
})
    .then(leerRespuestaDetalle)
    .then(function (datos) {
        if (formularioModificado) return;
        const solicitud = datos.data;
        document.getElementById('solicitante').value = solicitud.nombre_solicitante;
        document.getElementById('fecha').value = solicitud.fecha_hora_alta.slice(0, 10);
        document.getElementById('estado').value = solicitud.estado_solicitud;
        document.getElementById('prioridad').value = solicitud.prioridad;
        document.getElementById('responsable').value = solicitud.ci_responsable || '';
        document.getElementById('salon').value = solicitud.nombre_salon;
        inputMotivo.value = solicitud.descripcion;
    })
    .catch(function (error) {
        mostrarResultadoDetalle(error.message, false);
    });

if (!inputMotivo.disabled) {
    document.getElementById('responsable').addEventListener('change', function (evento) {
        if (evento.target.value) limpiarError(evento.target, 'error-responsable');
    });

    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        if (!validarTexto(inputMotivo, 'error-motivo', MOTIVO_MINIMO, 'el motivo')) {
            return;
        }
        if (!formulario.reportValidity()) return;

        const estado = document.getElementById('estado');
        const responsable = document.getElementById('responsable');
        if (estado.value === 'Resuelta' && !responsable.value) {
            mostrarError(responsable, 'error-responsable', 'Para cerrar la solicitud hay que asignar un responsable.');
            return;
        }

        const boton = document.getElementById('btn-guardar');
        boton.disabled = true;
        fetch('../../../BackEnd/api/solicitudes.php?id=' + encodeURIComponent(idSolicitud), {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': formulario.dataset.csrf
            },
            body: JSON.stringify({
                nombre_salon: document.getElementById('salon').value,
                descripcion: inputMotivo.value.trim(),
                prioridad: document.getElementById('prioridad').value,
                estado_solicitud: estado.value,
                ci_responsable: responsable.value
            })
        })
            .then(leerRespuestaDetalle)
            .then(function (datos) {
                mostrarResultadoDetalle(datos.message, true);
            })
            .catch(function (error) {
                mostrarResultadoDetalle(error.message, false);
            })
            .finally(function () {
                boton.disabled = false;
            });
    });

    limpiarAlEscribir(inputMotivo, 'error-motivo', MOTIVO_MINIMO);
}
