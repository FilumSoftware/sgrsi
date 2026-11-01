const listaUsos = document.getElementById('lista-usos');
const resultadoHistorial = document.getElementById('resultado-historial');

function leerRespuestaHistorial(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || TEXTOS.js_error_operacion);
        }
        return datos;
    });
}

function mostrarResultadoHistorial(mensaje, exito) {
    resultadoHistorial.textContent = TEXTOS[mensaje] || mensaje;
    resultadoHistorial.classList.toggle('mensaje-exito', exito);
    resultadoHistorial.style.display = 'block';
}

function celdaUso(fila, contenido) {
    const celda = document.createElement('td');
    if (contenido instanceof Node) celda.append(contenido);
    else celda.textContent = contenido == null ? '' : String(contenido);
    fila.append(celda);
    return celda;
}

function fechaCorta(fecha) {
    const partes = fecha.split('-');
    return partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0].slice(2) : fecha;
}

function crearFilaUso(uso) {
    const fila = document.createElement('tr');
    fila.classList.add('fila-clickeable');
    const url = 'detalle-uso-sala.php?id=' + encodeURIComponent(uso.id_uso);
    const enlace = document.createElement('a');
    enlace.className = 'tabla-enlace';
    enlace.href = url;
    enlace.textContent = fechaCorta(uso.fecha);
    celdaUso(fila, enlace);
    celdaUso(fila, uso.hora_inicio.slice(0, 5) + TEXTOS.js_a_horas + uso.hora_fin.slice(0, 5));
    celdaUso(fila, uso.nombre_docente);
    celdaUso(fila, uso.asignatura);
    celdaUso(fila, uso.grupo);
    celdaUso(fila, TEXTOS['valor_' + uso.turno]);
    celdaUso(fila, uso.nombre_salon);
    const acciones = celdaUso(fila, '');
    acciones.className = 'acciones';
    const editar = document.createElement('a');
    editar.className = 'btn-editar';
    editar.href = url + '&modo=editar';
    editar.textContent = TEXTOS.btn_editar;
    acciones.append(editar);
    if (listaUsos.dataset.coordinador === '1') {
        const eliminar = document.createElement('button');
        eliminar.type = 'button';
        eliminar.className = 'btn-eliminar';
        eliminar.dataset.id = uso.id_uso;
        eliminar.textContent = TEXTOS.btn_eliminar;
        acciones.append(eliminar);
    }
    return fila;
}

function mostrarVacio() {
    const fila = document.createElement('tr');
    const vacio = celdaUso(fila, TEXTOS.vacio_uso_sala);
    vacio.colSpan = 8;
    listaUsos.replaceChildren(fila);
}

function cargarUsos() {
    fetch('../../../BackEnd/api/uso-sala.php', { credentials: 'same-origin' })
        .then(leerRespuestaHistorial)
        .then(function (datos) {
            const usos = datos.data || [];
            if (usos.length) listaUsos.replaceChildren(...usos.map(crearFilaUso));
            else mostrarVacio();
        })
        .catch(function (error) {
            mostrarResultadoHistorial(error.message, false);
        });
}

listaUsos.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-id]');
    if (boton) {
        if (!window.confirm(TEXTOS.js_confirm_eliminar_registro)) return;
        boton.disabled = true;
        fetch('../../../BackEnd/api/uso-sala.php?id=' + encodeURIComponent(boton.dataset.id), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': listaUsos.dataset.csrf
            }
        })
            .then(leerRespuestaHistorial)
            .then(function (datos) {
                boton.closest('tr').remove();
                if (!listaUsos.querySelector('tr')) mostrarVacio();
                mostrarResultadoHistorial(datos.message, true);
            })
            .catch(function (error) {
                mostrarResultadoHistorial(error.message, false);
                boton.disabled = false;
            });
        return;
    }
    if (evento.target.closest('a, button') || window.getSelection().toString() !== '') return;
    const enlace = evento.target.closest('tr')?.querySelector('.tabla-enlace');
    if (enlace) window.location.href = enlace.href;
});

cargarUsos();
