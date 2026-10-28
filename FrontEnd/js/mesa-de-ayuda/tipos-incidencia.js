const formularioTipo = document.getElementById('form-tipo');
const listaTipos = document.getElementById('lista-tipos');
const resultadoTipos = document.getElementById('resultado-tipos');
const nombreTipo = document.getElementById('nombre-tipo');
const nombreOriginal = document.getElementById('nombre-original');
const estadoTipo = document.getElementById('estado-tipo');
const guardarTipo = document.getElementById('guardar-tipo');
const cancelarEdicionTipo = document.getElementById('cancelar-edicion-tipo');
const urlTipos = '../../../BackEnd/api/tipos-incidencia.php';

function leerRespuestaTipos(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultadoTipos(mensaje, exito) {
    resultadoTipos.textContent = mensaje;
    resultadoTipos.classList.toggle('mensaje-exito', exito);
    resultadoTipos.style.display = 'block';
}

function restablecerFormularioTipo() {
    formularioTipo.reset();
    nombreOriginal.value = '';
    estadoTipo.disabled = true;
    guardarTipo.textContent = 'Agregar tipo';
    cancelarEdicionTipo.classList.add('oculta');
}

function crearFilaTipo(tipo) {
    const fila = document.createElement('tr');
    const nombre = document.createElement('td');
    nombre.textContent = tipo.nombre;
    const estado = document.createElement('td');
    estado.textContent = tipo.estado;
    const acciones = document.createElement('td');
    acciones.className = 'acciones';

    if (tipo.nombre === 'Otros') {
        const fijo = document.createElement('span');
        fijo.className = 'sin-accion';
        fijo.textContent = 'Siempre activo';
        acciones.append(fijo);
    } else {
        const editar = document.createElement('button');
        editar.type = 'button';
        editar.className = 'btn-editar';
        editar.dataset.accion = 'editar';
        editar.textContent = 'Editar';
        acciones.append(editar);

        const cambiarEstado = document.createElement('button');
        cambiarEstado.type = 'button';
        cambiarEstado.className = tipo.estado === 'Activo' ? 'btn-eliminar' : 'btn-editar';
        cambiarEstado.dataset.accion = tipo.estado === 'Activo' ? 'inactivar' : 'activar';
        cambiarEstado.textContent = tipo.estado === 'Activo' ? 'Inactivar' : 'Activar';
        acciones.append(cambiarEstado);
    }

    fila.dataset.nombre = tipo.nombre;
    fila.dataset.estado = tipo.estado;
    fila.append(nombre, estado, acciones);
    return fila;
}

function cargarTipos() {
    return fetch(urlTipos + '?todos=1', { credentials: 'same-origin' })
        .then(leerRespuestaTipos)
        .then(function (datos) {
            const tipos = datos.data || [];
            if (tipos.length) {
                listaTipos.replaceChildren(...tipos.map(crearFilaTipo));
            } else {
                const fila = document.createElement('tr');
                const celda = document.createElement('td');
                celda.colSpan = 3;
                celda.textContent = 'Todavía no hay tipos de incidencia.';
                fila.append(celda);
                listaTipos.replaceChildren(fila);
            }
        });
}

formularioTipo.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!formularioTipo.reportValidity()) return;

    const actual = nombreOriginal.value;
    const nuevo = nombreTipo.value.trim();
    if (!nuevo) return;
    guardarTipo.disabled = true;
    fetch(urlTipos + (actual ? '?nombre=' + encodeURIComponent(actual) : ''), {
        method: actual ? 'PUT' : 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': formularioTipo.dataset.csrf
        },
        body: JSON.stringify(actual
            ? { nombre: nuevo, estado: estadoTipo.value }
            : { nombre: nuevo })
    })
        .then(leerRespuestaTipos)
        .then(function (datos) {
            restablecerFormularioTipo();
            mostrarResultadoTipos(datos.message, true);
            return cargarTipos();
        })
        .catch(function (error) {
            mostrarResultadoTipos(error.message, false);
        })
        .finally(function () {
            guardarTipo.disabled = false;
        });
});

cancelarEdicionTipo.addEventListener('click', restablecerFormularioTipo);

listaTipos.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton) return;
    const fila = boton.closest('tr');
    const nombre = fila.dataset.nombre;
    const estado = fila.dataset.estado;

    if (boton.dataset.accion === 'editar') {
        nombreOriginal.value = nombre;
        nombreTipo.value = nombre;
        estadoTipo.value = estado;
        estadoTipo.disabled = false;
        guardarTipo.textContent = 'Guardar cambios';
        cancelarEdicionTipo.classList.remove('oculta');
        nombreTipo.focus();
        return;
    }

    const inactivar = boton.dataset.accion === 'inactivar';
    if (inactivar && !window.confirm('¿Inactivar este tipo? Los tickets existentes conservarán su tipo de falla.')) return;
    boton.disabled = true;
    fetch(urlTipos + '?nombre=' + encodeURIComponent(nombre), {
        method: inactivar ? 'DELETE' : 'PUT',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-Token': formularioTipo.dataset.csrf,
            ...(!inactivar ? { 'Content-Type': 'application/json' } : {})
        },
        ...(!inactivar ? { body: JSON.stringify({ nombre: nombre, estado: 'Activo' }) } : {})
    })
        .then(leerRespuestaTipos)
        .then(function (datos) {
            if (nombreOriginal.value === nombre) restablecerFormularioTipo();
            mostrarResultadoTipos(datos.message, true);
            return cargarTipos();
        })
        .catch(function (error) {
            mostrarResultadoTipos(error.message, false);
            boton.disabled = false;
        });
});

cargarTipos().catch(function (error) {
    mostrarResultadoTipos(error.message, false);
});
