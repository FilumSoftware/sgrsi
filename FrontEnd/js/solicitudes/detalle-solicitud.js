const formulario = document.getElementById('form-detalle-solicitud');
const inputMotivo = document.getElementById('motivo');
const resultadoGuardado = document.getElementById('resultado-guardado');

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
        fetch('../../../BackEnd/api/solicitudes.php?id=' + encodeURIComponent(formulario.elements.namedItem('id').value), {
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
