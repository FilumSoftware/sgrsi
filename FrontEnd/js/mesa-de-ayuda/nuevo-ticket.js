const formulario = document.getElementById('form-ticket');
const salon = document.getElementById('salon');
const equipo = document.getElementById('equipo');
const motivo = document.getElementById('motivo');
const resultado = document.getElementById('resultado-ticket');
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
        }, 'Elija una opción');
    })
    .catch(function (error) {
        mostrarResultado(error.message, false);
    });

salon.addEventListener('change', function () {
    cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, 'Cargando equipos...');
    if (!salon.value) {
        cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, 'Elegí primero un salón');
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
                return fila.nombre_equipo + ' (' + fila.estado_equipo + ')';
            }, 'Elija una opción');
        })
        .catch(function (error) {
            mostrarResultado(error.message, false);
            cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, 'No se pudieron cargar equipos');
        });
});

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!validarTexto(motivo, 'error-motivo', 10, 'el motivo')) return;
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
            tipo_de_defecto: document.getElementById('defecto').value,
            descripcion: motivo.value.trim(),
            prioridad: document.getElementById('prioridad').value
        })
    })
        .then(leerRespuesta)
        .then(function (datos) {
            mostrarResultado(datos.message + ' N.º ' + datos.data.id_ticket + '.', true);
            formulario.reset();
            cargarOpciones(equipo, [], 'id_equipo', function () { return ''; }, 'Elegí primero un salón');
        })
        .catch(function (error) {
            mostrarResultado(error.message, false);
        })
        .finally(function () {
            boton.disabled = false;
        });
});

limpiarAlEscribir(motivo, 'error-motivo', 10);
