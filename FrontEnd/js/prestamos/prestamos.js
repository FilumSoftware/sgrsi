const formulario = document.getElementById('form-prestamo');
const equipo = document.getElementById('equipo');
const solicitante = document.getElementById('solicitante');
const fechaPrevista = document.getElementById('fecha-prevista');
const observaciones = document.getElementById('observaciones');
const lista = document.getElementById('lista-prestamos');
const resultado = document.getElementById('resultado-prestamo');
const guardar = document.getElementById('guardar-prestamo');
const cancelarEdicion = document.getElementById('cancelar-edicion');
const titulo = document.getElementById('titulo-formulario');
const esCoordinador = document.body.dataset.coordinador === '1';
let prestamos = [];
let catalogos = { equipos: [], usuarios: [] };
let idEditado = null;

function pedir(metodo, ruta, cuerpo) {
    const opciones = {
        method: metodo,
        credentials: 'same-origin',
        headers: { Accept: 'application/json' }
    };
    if (metodo !== 'GET') opciones.headers['X-CSRF-Token'] = formulario.dataset.csrf;
    if (cuerpo !== undefined) {
        opciones.headers['Content-Type'] = 'application/json';
        opciones.body = JSON.stringify(cuerpo);
    }
    return fetch('../../../BackEnd/api/prestamos.php' + ruta, opciones)
        .then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok || datos.status !== 'success') {
                    throw new Error(datos.message || 'No se pudo completar la operación.');
                }
                return datos.data;
            });
        });
}

function mostrar(mensaje, exito) {
    resultado.textContent = mensaje;
    resultado.classList.toggle('mensaje-exito', exito);
    resultado.style.display = 'block';
}

function opcionesSelect(select, filas, valor, etiqueta, vacio) {
    select.replaceChildren(new Option(vacio, ''));
    filas.forEach(function (fila) {
        select.add(new Option(etiqueta(fila), fila[valor]));
    });
}

function cargarCatalogos() {
    opcionesSelect(equipo, catalogos.equipos, 'id_equipo', function (fila) {
        return fila.nombre_equipo + ' · ' + fila.nombre_salon;
    }, catalogos.equipos.length ? 'Elegí un equipo' : 'No hay equipos disponibles');
    opcionesSelect(solicitante, catalogos.usuarios, 'ci', function (fila) {
        return fila.nombre_usuario + ' · ' + fila.ci;
    }, 'Elegí un usuario');
}

function celda(fila, valor) {
    const td = document.createElement('td');
    td.textContent = valor === null || valor === undefined || valor === '' ? '—' : String(valor);
    fila.appendChild(td);
    return td;
}

function botonAccion(acciones, texto, accion, id, clase) {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = clase;
    boton.textContent = texto;
    boton.dataset.accion = accion;
    boton.dataset.id = id;
    acciones.appendChild(boton);
}

function dibujar() {
    lista.replaceChildren();
    if (!prestamos.length) {
        const fila = document.createElement('tr');
        const vacio = celda(fila, 'Todavía no hay préstamos registrados.');
        vacio.colSpan = 7;
        lista.appendChild(fila);
        return;
    }
    prestamos.forEach(function (prestamo) {
        const fila = document.createElement('tr');
        celda(fila, prestamo.id_prestamo);
        celda(fila, prestamo.nombre_equipo + ' · ' + prestamo.nombre_salon);
        celda(fila, prestamo.nombre_solicitante);
        celda(fila, prestamo.fecha_hora_prestamo.slice(0, 16));
        celda(fila, prestamo.fecha_devolucion_prevista);
        celda(fila, prestamo.estado_prestamo);
        const acciones = celda(fila, '');
        acciones.className = 'acciones';
        acciones.textContent = '';
        if (prestamo.estado_prestamo === 'Activo') {
            botonAccion(acciones, 'Editar', 'editar', prestamo.id_prestamo, 'btn-editar');
            botonAccion(acciones, 'Devolver', 'devolver', prestamo.id_prestamo, 'btn-editar');
            if (esCoordinador) {
                botonAccion(acciones, 'Anular', 'anular', prestamo.id_prestamo, 'btn-eliminar');
            }
        } else {
            acciones.textContent = '—';
        }
        lista.appendChild(fila);
    });
}

function cargar() {
    return Promise.all([pedir('GET', '', undefined), pedir('GET', '?catalogos=1', undefined)])
        .then(function (datos) {
            prestamos = datos[0];
            catalogos = datos[1];
            if (idEditado === null) cargarCatalogos();
            dibujar();
        })
        .catch(function (error) {
            mostrar(error.message, false);
        });
}

function salirDeEdicion() {
    idEditado = null;
    formulario.reset();
    equipo.disabled = false;
    solicitante.disabled = false;
    titulo.textContent = 'Registrar préstamo';
    guardar.textContent = 'Registrar préstamo';
    cancelarEdicion.classList.add('oculta');
    cargarCatalogos();
}

cancelarEdicion.addEventListener('click', salirDeEdicion);

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!formulario.reportValidity()) return;
    guardar.disabled = true;
    const editando = idEditado !== null;
    const datos = {
        fecha_devolucion_prevista: fechaPrevista.value,
        observaciones: observaciones.value.trim()
    };
    if (!editando) {
        datos.id_equipo = equipo.value;
        datos.ci_solicitante = solicitante.value;
    }
    pedir(editando ? 'PUT' : 'POST', editando ? '?id=' + encodeURIComponent(idEditado) : '', datos)
        .then(function () {
            salirDeEdicion();
            mostrar(editando ? 'Préstamo actualizado.' : 'Préstamo registrado.', true);
            return cargar();
        })
        .catch(function (error) {
            mostrar(error.message, false);
        })
        .finally(function () {
            guardar.disabled = false;
        });
});

lista.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton) return;
    const prestamo = prestamos.find(function (fila) {
        return String(fila.id_prestamo) === boton.dataset.id;
    });
    if (!prestamo) return;

    if (boton.dataset.accion === 'editar') {
        idEditado = prestamo.id_prestamo;
        equipo.replaceChildren(new Option(prestamo.nombre_equipo, prestamo.id_equipo));
        solicitante.replaceChildren(new Option(prestamo.nombre_solicitante, prestamo.ci_solicitante));
        equipo.disabled = true;
        solicitante.disabled = true;
        fechaPrevista.value = prestamo.fecha_devolucion_prevista || '';
        observaciones.value = prestamo.observaciones || '';
        titulo.textContent = 'Editar préstamo n.º ' + idEditado;
        guardar.textContent = 'Guardar cambios';
        cancelarEdicion.classList.remove('oculta');
        formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
        return;
    }

    const devolucion = boton.dataset.accion === 'devolver';
    const pregunta = devolucion ? '¿Registrar la devolución de este equipo?' : '¿Anular este préstamo?';
    if (!window.confirm(pregunta)) return;
    boton.disabled = true;
    pedir(devolucion ? 'PUT' : 'DELETE', '?id=' + encodeURIComponent(prestamo.id_prestamo),
        devolucion ? { accion: 'devolver' } : undefined)
        .then(function () {
            if (String(idEditado) === String(prestamo.id_prestamo)) salirDeEdicion();
            mostrar(devolucion ? 'Devolución registrada.' : 'Préstamo anulado.', true);
            return cargar();
        })
        .catch(function (error) {
            mostrar(error.message, false);
            boton.disabled = false;
        });
});

cargar();
