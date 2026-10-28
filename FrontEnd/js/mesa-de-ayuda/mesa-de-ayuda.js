const listaTickets = document.getElementById('lista-tickets');
const resultadoTickets = document.getElementById('resultado-tickets');

function leerRespuestaTickets(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultadoTickets(mensaje, exito) {
    resultadoTickets.textContent = mensaje;
    resultadoTickets.classList.toggle('mensaje-exito', exito);
    resultadoTickets.style.display = 'block';
}

function mostrarFilaVacia(mensaje) {
    const fila = document.createElement('tr');
    const celda = document.createElement('td');
    celda.colSpan = 8;
    celda.textContent = mensaje;
    fila.append(celda);
    listaTickets.replaceChildren(fila);
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

function crearFilaTicket(ticket) {
    const fila = document.createElement('tr');
    fila.classList.add('fila-clickeable');
    if (ticket.fuera_de_plazo) fila.classList.add('fila-ticket-vencido');
    const url = 'detalle-ticket.php?id=' + encodeURIComponent(ticket.id_ticket);
    const enlace = document.createElement('a');
    enlace.className = 'tabla-enlace';
    enlace.href = url;
    enlace.textContent = ticket.id_ticket;
    agregarCelda(fila, enlace);

    const partes = (ticket.fecha_hora_alta || '').split(' ');
    const fecha = (partes[0] || '').split('-');
    agregarCelda(fila, fecha.length === 3 ? fecha[2] + '/' + fecha[1] + '/' + fecha[0].slice(-2) : '');
    agregarCelda(fila, (partes[1] || '').slice(0, 5));
    agregarCelda(fila, ticket.nombre_salon);
    agregarCelda(fila, ticket.nombre_equipo);
    agregarCelda(fila, ticket.tipo_de_defecto === 'Otros' && ticket.detalle_defecto
        ? 'Otros: ' + ticket.detalle_defecto : ticket.tipo_de_defecto);

    const estado = document.createElement('span');
    estado.className = 'badge';
    estado.classList.add(ticket.estado_ticket === 'Cancelado' ? 'bg-secondary' :
        ticket.estado_ticket === 'Resuelto' ? 'bg-success' :
        ticket.estado_ticket === 'En proceso' ? 'bg-info' : 'bg-warning');
    if (ticket.estado_ticket === 'Pendiente' || ticket.estado_ticket === 'En proceso') {
        estado.classList.add('text-dark');
    }
    estado.textContent = ticket.estado_ticket;
    const celdaEstado = agregarCelda(fila, estado);
    if (ticket.fuera_de_plazo) {
        const avisoPlazo = document.createElement('span');
        avisoPlazo.className = 'aviso-plazo-vencido';
        avisoPlazo.textContent = 'Fuera de plazo';
        celdaEstado.append(avisoPlazo);
    }

    const acciones = agregarCelda(fila, '');
    acciones.className = 'acciones';
    const atender = listaTickets.dataset.atender === '1' && ticket.estado_ticket !== 'Cancelado';
    const editar = document.createElement('a');
    editar.className = 'btn-editar';
    editar.href = url + (atender ? '&modo=editar' : '');
    editar.textContent = atender ? 'Atender' : 'Ver';
    acciones.append(editar);

    if (listaTickets.dataset.coordinador === '1') {
        const eliminar = document.createElement('button');
        eliminar.type = 'button';
        eliminar.className = 'btn-eliminar';
        eliminar.dataset.id = ticket.id_ticket;
        eliminar.textContent = 'Eliminar';
        acciones.append(eliminar);
    }
    return fila;
}

function cargarTickets() {
    fetch('../../../BackEnd/api/tickets.php', { credentials: 'same-origin' })
        .then(leerRespuestaTickets)
        .then(function (datos) {
            const tickets = datos.data || [];
            if (tickets.length === 0) {
                mostrarFilaVacia('Todavía no hay tickets registrados.');
                return;
            }
            listaTickets.replaceChildren(...tickets.map(crearFilaTicket));
        })
        .catch(function (error) {
            mostrarFilaVacia('No se pudo cargar el historial de tickets.');
            mostrarResultadoTickets(error.message, false);
        });
}

listaTickets.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-id]');
    if (boton) {
        if (!window.confirm('¿Eliminar este ticket? No se puede deshacer.')) return;
        boton.disabled = true;
        fetch('../../../BackEnd/api/tickets.php?id=' + encodeURIComponent(boton.dataset.id) + '&accion=eliminar', {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': listaTickets.dataset.csrf
            }
        })
            .then(leerRespuestaTickets)
            .then(function (datos) {
                boton.closest('tr').remove();
                if (!listaTickets.querySelector('tr')) mostrarFilaVacia('Todavía no hay tickets registrados.');
                mostrarResultadoTickets(datos.message, true);
            })
            .catch(function (error) {
                mostrarResultadoTickets(error.message, false);
                boton.disabled = false;
            });
        return;
    }

    if (evento.target.closest('a, button') || window.getSelection().toString() !== '') return;
    const enlace = evento.target.closest('tr')?.querySelector('.tabla-enlace');
    if (enlace) window.location.href = enlace.href;
});

cargarTickets();
