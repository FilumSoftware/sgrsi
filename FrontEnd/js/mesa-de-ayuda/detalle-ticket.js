const formulario = document.getElementById('form-detalle-ticket');
const inputMotivo = document.getElementById('motivo');
const salon = document.getElementById('salon');
const equipo = document.getElementById('equipo');
const resultadoGuardado = document.getElementById('resultado-guardado');
const idTicket = formulario.elements.namedItem('id').value;
let formularioModificado = false;
const defecto = document.getElementById('defecto');
const detalleDefecto = document.getElementById('detalle-defecto');

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

function actualizarDetalleDefecto() {
    const esOtro = defecto.value === 'Otros';
    document.getElementById('grupo-detalle-defecto').classList.toggle('oculta', !esOtro);
    detalleDefecto.required = esOtro;
    if (!esOtro) detalleDefecto.value = '';
}

defecto.addEventListener('change', actualizarDetalleDefecto);
actualizarDetalleDefecto();

formulario.addEventListener('input', function () { formularioModificado = true; });
formulario.addEventListener('change', function () { formularioModificado = true; });

fetch('../../../BackEnd/api/tickets.php?id=' + encodeURIComponent(idTicket), {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
})
    .then(leerRespuestaDetalle)
    .then(function (datos) {
        if (formularioModificado) return;
        const ticket = datos.data;
        document.getElementById('fecha').value = ticket.fecha_hora_alta.slice(0, 10);
        document.getElementById('hora').value = ticket.fecha_hora_alta.slice(11, 16);
        document.getElementById('solicitante').value = ticket.nombre_solicitante;
        salon.value = ticket.nombre_salon;
        if (!Array.from(equipo.options).some(function (opcion) { return opcion.value === String(ticket.id_equipo); })) {
            equipo.add(new Option(ticket.nombre_equipo, ticket.id_equipo));
        }
        equipo.value = ticket.id_equipo;
        defecto.value = ticket.tipo_de_defecto;
        detalleDefecto.value = ticket.detalle_defecto || '';
        actualizarDetalleDefecto();
        document.getElementById('prioridad').value = ticket.prioridad;
        const estado = document.getElementById('estado');
        if (ticket.estado_ticket === 'Cancelado' && !Array.from(estado.options).some(function (opcion) { return opcion.value === 'Cancelado'; })) {
            estado.add(new Option('Cancelado', 'Cancelado'));
        }
        estado.value = ticket.estado_ticket;
        document.getElementById('responsable').value = ticket.ci_responsable || '';
        inputMotivo.value = ticket.descripcion;
        document.getElementById('diagnostico').value = ticket.diagnostico || '';
        document.getElementById('solucion').value = ticket.solucion || '';
        const cancelar = document.getElementById('btn-cancelar-ticket');
        if (cancelar && (ticket.estado_ticket !== 'Pendiente' || ticket.ci_responsable)) cancelar.remove();
        if (ticket.estado_ticket === 'Cancelado') {
            formulario.querySelectorAll('input:not([type="hidden"]), select, textarea, button').forEach(function (control) {
                control.disabled = true;
            });
            const editar = formulario.querySelector('.btn-editar-form');
            if (editar) editar.remove();
        }
    })
    .catch(function (error) {
        mostrarResultadoDetalle(error.message, false);
    });

if (!inputMotivo.disabled) {
    salon.addEventListener('change', function () {
        equipo.replaceChildren(new Option('Cargando equipos...', ''));
        const seleccionado = salon.value;
        if (!seleccionado) {
            equipo.replaceChildren(new Option('Elegí primero un salón', ''));
            return;
        }
        fetch('../../../BackEnd/api/catalogos.php?tipo=equipos&salon=' + encodeURIComponent(seleccionado), {
            credentials: 'same-origin'
        })
            .then(leerRespuestaDetalle)
            .then(function (datos) {
                if (salon.value !== seleccionado) return;
                equipo.replaceChildren(new Option('Elija una opción', ''));
                datos.data.forEach(function (fila) {
                    equipo.add(new Option(fila.nombre_equipo, fila.id_equipo));
                });
            })
            .catch(function (error) {
                if (salon.value !== seleccionado) return;
                equipo.replaceChildren(new Option('No se pudieron cargar equipos', ''));
                mostrarResultadoDetalle(error.message, false);
            });
    });

    formulario.addEventListener('submit', function (evento) {
        evento.preventDefault();
        if (!validarTexto(inputMotivo, 'error-motivo', MOTIVO_MINIMO, 'el motivo')) {
            return;
        }
        if (!formulario.reportValidity()) return;

        const boton = document.getElementById('btn-enviar');
        boton.disabled = true;
        fetch('../../../BackEnd/api/tickets.php?id=' + encodeURIComponent(idTicket), {
            method: 'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': formulario.dataset.csrf
            },
            body: JSON.stringify({
                id_equipo: equipo.value,
                tipo_de_defecto: defecto.value,
                detalle_defecto: detalleDefecto.value.trim(),
                descripcion: inputMotivo.value.trim(),
                prioridad: document.getElementById('prioridad').value,
                estado_ticket: document.getElementById('estado').value,
                ci_responsable: document.getElementById('responsable').value,
                diagnostico: document.getElementById('diagnostico').value.trim(),
                solucion: document.getElementById('solucion').value.trim()
            })
        })
            .then(leerRespuestaDetalle)
            .then(function (datos) {
                mostrarResultadoDetalle(datos.message, true);
                const cancelar = document.getElementById('btn-cancelar-ticket');
                if (cancelar && (document.getElementById('estado').value !== 'Pendiente' || document.getElementById('responsable').value)) {
                    cancelar.remove();
                }
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

const botonCancelar = document.getElementById('btn-cancelar-ticket');

if (botonCancelar) {
    botonCancelar.addEventListener('click', function () {
        if (!window.confirm('¿Querés cancelar este ticket? Esta acción no se puede deshacer.')) {
            return;
        }

        const resultado = document.getElementById('resultado-cancelacion');
        botonCancelar.disabled = true;

        fetch('../../../BackEnd/api/tickets.php?id=' + encodeURIComponent(botonCancelar.dataset.id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': botonCancelar.dataset.csrf
            },
            body: JSON.stringify({ estado_ticket: 'Cancelado' }),
            credentials: 'same-origin'
        })
            .then(function (respuesta) {
                return respuesta.json().then(function (datos) {
                    if (!respuesta.ok || datos.status !== 'success') {
                        throw new Error(datos.message || 'No se pudo cancelar el ticket.');
                    }
                    return datos;
                });
            })
            .then(function (datos) {
                const estado = document.getElementById('estado');
                estado.add(new Option('Cancelado', 'Cancelado'));
                estado.value = datos.data.estado_ticket;
                document.querySelectorAll('#form-detalle-ticket input, #form-detalle-ticket select, #form-detalle-ticket textarea, #form-detalle-ticket button')
                    .forEach(function (control) { control.disabled = true; });
                botonCancelar.remove();
                resultado.textContent = datos.message;
                resultado.classList.add('mensaje-exito');
                resultado.style.display = 'block';
            })
            .catch(function (error) {
                resultado.textContent = error.message || 'No se pudo cancelar el ticket.';
                resultado.style.display = 'block';
                botonCancelar.disabled = false;
            });
    });
}
