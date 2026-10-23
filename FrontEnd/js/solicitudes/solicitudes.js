const listaSolicitudes = document.getElementById('lista-solicitudes');
const resultadoSolicitudes = document.getElementById('resultado-solicitudes');

function leerRespuestaSolicitudes(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultadoSolicitudes(mensaje, exito) {
    resultadoSolicitudes.textContent = mensaje;
    resultadoSolicitudes.classList.toggle('mensaje-exito', exito);
    resultadoSolicitudes.style.display = 'block';
}

function mostrarFilaVacia(mensaje) {
    const fila = document.createElement('tr');
    const celda = document.createElement('td');
    celda.colSpan = 9;
    celda.textContent = mensaje;
    fila.append(celda);
    listaSolicitudes.replaceChildren(fila);
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

function crearFilaSolicitud(solicitud) {
    const fila = document.createElement('tr');
    fila.classList.add('fila-clickeable');
    const url = 'detalle-solicitud.php?id=' + encodeURIComponent(solicitud.id_solicitud);
    const enlace = document.createElement('a');
    enlace.className = 'tabla-enlace';
    enlace.href = url;
    enlace.textContent = solicitud.id_solicitud;
    agregarCelda(fila, enlace);

    const fecha = (solicitud.fecha_hora_alta || '').split(' ')[0].split('-');
    agregarCelda(fila, fecha.length === 3 ? fecha[2] + '/' + fecha[1] + '/' + fecha[0].slice(-2) : '');
    agregarCelda(fila, solicitud.nombre_salon);
    agregarCelda(fila, solicitud.descripcion);

    const estado = document.createElement('span');
    estado.className = 'badge';
    estado.classList.add(solicitud.estado_solicitud === 'Resuelta' ? 'bg-success' :
        solicitud.estado_solicitud === 'En proceso' ? 'bg-info' : 'bg-warning');
    if (solicitud.estado_solicitud !== 'Resuelta') estado.classList.add('text-dark');
    estado.textContent = solicitud.estado_solicitud;
    agregarCelda(fila, estado);

    agregarCelda(fila, solicitud.prioridad);
    agregarCelda(fila, solicitud.nombre_solicitante);
    agregarCelda(fila, solicitud.nombre_responsable || '—');

    const acciones = agregarCelda(fila, '');
    acciones.className = 'acciones';
    const editar = document.createElement('a');
    editar.className = 'btn-editar';
    editar.href = url + (listaSolicitudes.dataset.atender === '1' ? '&modo=editar' : '');
    editar.textContent = listaSolicitudes.dataset.atender === '1' ? 'Atender' : 'Ver';
    acciones.append(editar);

    if (listaSolicitudes.dataset.coordinador === '1') {
        const eliminar = document.createElement('button');
        eliminar.type = 'button';
        eliminar.className = 'btn-eliminar';
        eliminar.dataset.id = solicitud.id_solicitud;
        eliminar.textContent = 'Eliminar';
        acciones.append(eliminar);
    }
    return fila;
}

function cargarSolicitudes() {
    fetch('../../../BackEnd/api/solicitudes.php', { credentials: 'same-origin' })
        .then(leerRespuestaSolicitudes)
        .then(function (datos) {
            const solicitudes = datos.data || [];
            if (solicitudes.length === 0) {
                mostrarFilaVacia('Todavía no hay solicitudes registradas.');
                return;
            }
            listaSolicitudes.replaceChildren(...solicitudes.map(crearFilaSolicitud));
        })
        .catch(function (error) {
            mostrarFilaVacia('No se pudo cargar el listado de solicitudes.');
            mostrarResultadoSolicitudes(error.message, false);
        });
}

listaSolicitudes.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-id]');
    if (boton) {
        if (!window.confirm('¿Eliminar esta solicitud? No se puede deshacer.')) return;
        boton.disabled = true;
        fetch('../../../BackEnd/api/solicitudes.php?id=' + encodeURIComponent(boton.dataset.id), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': listaSolicitudes.dataset.csrf
            }
        })
            .then(leerRespuestaSolicitudes)
            .then(function (datos) {
                boton.closest('tr').remove();
                if (!listaSolicitudes.querySelector('tr')) mostrarFilaVacia('Todavía no hay solicitudes registradas.');
                mostrarResultadoSolicitudes(datos.message, true);
            })
            .catch(function (error) {
                mostrarResultadoSolicitudes(error.message, false);
                boton.disabled = false;
            });
        return;
    }

    if (evento.target.closest('a, button') || window.getSelection().toString() !== '') return;
    const enlace = evento.target.closest('tr')?.querySelector('.tabla-enlace');
    if (enlace) window.location.href = enlace.href;
});

cargarSolicitudes();
