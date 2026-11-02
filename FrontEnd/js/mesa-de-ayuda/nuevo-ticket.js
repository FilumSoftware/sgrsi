const formulario = document.getElementById('form-ticket');
const salon = document.getElementById('salon');
const equipo = document.getElementById('equipo');
const motivo = document.getElementById('motivo');
const resultado = document.getElementById('resultado-ticket');
const boton = document.getElementById('btn-enviar');
const defecto = document.getElementById('defecto');
const detalleDefecto = document.getElementById('detalle-defecto');

function actualizarDetalleDefecto() {
    const esOtro = defecto.value === 'Otros';
    document.getElementById('grupo-detalle-defecto').classList.toggle('oculta', !esOtro);
    detalleDefecto.required = esOtro;
    if (!esOtro) detalleDefecto.value = '';
}

defecto.addEventListener('change', actualizarDetalleDefecto);
actualizarDetalleDefecto();

function leerRespuesta(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || TEXTOS.js_error_operacion);
        }
        return datos;
    });
}

function mostrarResultado(mensaje, exito) {
    resultado.textContent = TEXTOS[mensaje] || mensaje;
    resultado.classList.toggle('mensaje-exito', exito);
    resultado.style.display = 'block';
}

function cargarOpciones(select, filas, clave, etiqueta, textoVacio) {
    select.replaceChildren(new Option(textoVacio, ''));
    filas.forEach(function (fila) {
        select.add(new Option(etiqueta(fila), fila[clave]));
    });
}

fetch('../../../BackEnd/api/catalogos.php?tipo=salones', { credentials: 'same-origin' })
    .then(leerRespuesta)
    .then(function (datos) {
        cargarOpciones(salon, datos.data, 'nombre_salon', function (fila) {
            return fila.nombre_salon;
        }, TEXTOS.opt_elija_opcion);
    })
    .catch(function (error) {
        mostrarResultado(error.message, false);
    });

fetch('../../../BackEnd/api/tipos-incidencia.php', { credentials: 'same-origin' })
    .then(leerRespuesta)
    .then(function (datos) {
        cargarOpciones(defecto, datos.data, 'nombre', function (fila) {
            return fila.nombre;
        }, TEXTOS.opt_elija_opcion);
    })
    .catch(function (error) {
        mostrarResultado(error.message, false);
    });

salon.addEventListener('change', function () {
    cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, TEXTOS.cargando_equipos);
    if (!salon.value) {
        cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, TEXTOS.opt_elegi_salon_primero);
        return;
    }
    const seleccionado = salon.value;
    fetch('../../../BackEnd/api/catalogos.php?tipo=equipos&salon=' + encodeURIComponent(seleccionado), {
        credentials: 'same-origin'
    })
        .then(leerRespuesta)
        .then(function (datos) {
            if (salon.value !== seleccionado) return;
            cargarOpciones(equipo, datos.data, 'id_equipo', function (fila) {
                return fila.nombre_equipo + ' (' + TEXTOS['valor_' + fila.estado_equipo] + ')';
            }, TEXTOS.opt_elija_opcion);
        })
        .catch(function (error) {
            mostrarResultado(error.message, false);
            cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, TEXTOS.js_error_cargar_equipos);
        });
});

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!validarTexto(motivo, 'error-motivo', 10, TEXTOS.etq_motivo)) return;
    if (!formulario.reportValidity()) return;

    boton.disabled = true;
    fetch('../../../BackEnd/api/tickets.php', {
        method: 'POST',
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
            descripcion: motivo.value.trim(),
            prioridad: document.getElementById('prioridad').value
        })
    })
        .then(leerRespuesta)
        .then(function (datos) {
            mostrarResultado((TEXTOS[datos.message] || datos.message) + TEXTOS.js_num_prefijo + datos.data.id_ticket + '.', true);
            formulario.reset();
            actualizarDetalleDefecto();
            cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, TEXTOS.opt_elegi_salon_primero);
        })
        .catch(function (error) {
            mostrarResultado(error.message, false);
        })
        .finally(function () {
            boton.disabled = false;
        });
});

limpiarAlEscribir(motivo, 'error-motivo', 10);

cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, TEXTOS.opt_elegi_salon_primero);
