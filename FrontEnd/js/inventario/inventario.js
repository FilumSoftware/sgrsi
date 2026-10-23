const listaEquipos = document.getElementById('lista-equipos');
const resultadoInventario = document.getElementById('resultado-inventario');

function leerRespuestaInventario(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultadoInventario(mensaje, exito) {
    resultadoInventario.textContent = mensaje;
    resultadoInventario.classList.toggle('mensaje-exito', exito);
    resultadoInventario.style.display = 'block';
}

function mostrarFilaVacia(mensaje) {
    const fila = document.createElement('tr');
    const celda = document.createElement('td');
    celda.colSpan = 6;
    celda.textContent = mensaje;
    fila.append(celda);
    listaEquipos.replaceChildren(fila);
}

function agregarCelda(fila, contenido) {
    const celda = document.createElement('td');
    if (contenido instanceof Node) {
        celda.append(contenido);
    } else {
        celda.textContent = contenido || '';
    }
    fila.append(celda);
    return celda;
}

function crearFilaEquipo(equipo) {
    const fila = document.createElement('tr');
    fila.classList.add('fila-clickeable');
    const url = 'detalle-equipo.php?id=' + encodeURIComponent(equipo.id_equipo);
    const enlace = document.createElement('a');
    enlace.className = 'tabla-enlace';
    enlace.href = url;
    enlace.textContent = equipo.id_equipo;
    agregarCelda(fila, enlace);
    agregarCelda(fila, equipo.nombre_equipo);
    agregarCelda(fila, equipo.descripcion);
    agregarCelda(fila, equipo.nombre_salon);
    const estado = agregarCelda(fila, equipo.estado_equipo);
    estado.dataset.estado = '1';

    const acciones = agregarCelda(fila, '');
    acciones.className = 'acciones';
    const editar = document.createElement('a');
    editar.className = 'btn-editar';
    editar.href = url + '&modo=editar';
    editar.textContent = 'Editar';
    acciones.append(editar);

    if (equipo.estado_equipo !== 'De baja') {
        const baja = document.createElement('button');
        baja.type = 'button';
        baja.className = 'btn-eliminar';
        baja.dataset.id = equipo.id_equipo;
        baja.textContent = 'Registrar baja';
        acciones.append(baja);
    }
    return fila;
}

function cargarInventario() {
    fetch('../../../BackEnd/api/inventario.php', { credentials: 'same-origin' })
        .then(leerRespuestaInventario)
        .then(function (datos) {
            const equipos = datos.data || [];
            if (equipos.length === 0) {
                mostrarFilaVacia('Todavía no hay equipos registrados.');
                return;
            }
            listaEquipos.replaceChildren(...equipos.map(crearFilaEquipo));
        })
        .catch(function (error) {
            mostrarFilaVacia('No se pudo cargar el listado de equipos.');
            mostrarResultadoInventario(error.message, false);
        });
}

listaEquipos.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-id]');
    if (boton) {
        if (!window.confirm('¿La administración confirmó la baja de esta PC?')) return;
        boton.disabled = true;
        fetch('../../../BackEnd/api/inventario.php?id=' + encodeURIComponent(boton.dataset.id), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': listaEquipos.dataset.csrf
            }
        })
            .then(leerRespuestaInventario)
            .then(function (datos) {
                boton.closest('tr').querySelector('[data-estado]').textContent = datos.data.estado_equipo;
                boton.remove();
                mostrarResultadoInventario(datos.message, true);
            })
            .catch(function (error) {
                mostrarResultadoInventario(error.message, false);
                boton.disabled = false;
            });
        return;
    }

    if (evento.target.closest('a, button') || window.getSelection().toString() !== '') return;
    const enlace = evento.target.closest('tr')?.querySelector('.tabla-enlace');
    if (enlace) window.location.href = enlace.href;
});

cargarInventario();
