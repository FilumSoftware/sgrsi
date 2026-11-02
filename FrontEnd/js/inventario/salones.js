const formularioSalon = document.getElementById('form-salon');
const listaSalones = document.getElementById('lista-salones');
const resultadoSalones = document.getElementById('resultado-salones');
const nombreSalon = document.getElementById('nombre-salon');
const descripcionSalon = document.getElementById('descripcion-salon');
const nombreSalonOriginal = document.getElementById('nombre-original');
const guardarSalon = document.getElementById('guardar-salon');
const cancelarEdicionSalon = document.getElementById('cancelar-edicion-salon');
const urlSalones = '../../../BackEnd/api/salones.php';

function leerRespuestaSalones(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || TEXTOS.js_error_operacion);
        }
        return datos;
    });
}

function mostrarResultadoSalones(mensaje, exito) {
    resultadoSalones.textContent = TEXTOS[mensaje] || mensaje;
    resultadoSalones.classList.toggle('mensaje-exito', exito);
    resultadoSalones.style.display = 'block';
}

function restablecerFormularioSalon() {
    formularioSalon.reset();
    nombreSalon.value = '';
    descripcionSalon.value = '';
    nombreSalonOriginal.value = '';
    nombreSalon.readOnly = false;
    guardarSalon.textContent = TEXTOS.btn_agregar_salon;
    cancelarEdicionSalon.classList.add('oculta');
}

function crearFilaSalon(salon) {
    const fila = document.createElement('tr');

    const nombre = document.createElement('td');
    nombre.textContent = salon.nombre_salon;

    const descripcion = document.createElement('td');
    descripcion.textContent = salon.descripcion || '';

    const acciones = document.createElement('td');
    acciones.className = 'acciones';

    const editar = document.createElement('button');
    editar.type = 'button';
    editar.className = 'btn-editar';
    editar.dataset.accion = 'editar';
    editar.textContent = TEXTOS.btn_editar;
    acciones.append(editar);

    const eliminar = document.createElement('button');
    eliminar.type = 'button';
    eliminar.className = 'btn-eliminar';
    eliminar.dataset.accion = 'eliminar';
    eliminar.textContent = TEXTOS.btn_eliminar;
    acciones.append(eliminar);

    fila.dataset.nombre = salon.nombre_salon;
    fila.dataset.descripcion = salon.descripcion || '';
    fila.append(nombre, descripcion, acciones);
    return fila;
}

function cargarSalones() {
    return fetch(urlSalones, { credentials: 'same-origin' })
        .then(leerRespuestaSalones)
        .then(function (datos) {
            const salones = datos.data || [];
            if (salones.length) {
                listaSalones.replaceChildren(...salones.map(crearFilaSalon));
            } else {
                const fila = document.createElement('tr');
                const celda = document.createElement('td');
                celda.colSpan = 3;
                celda.textContent = TEXTOS.js_sin_salones;
                fila.append(celda);
                listaSalones.replaceChildren(fila);
            }
        });
}

formularioSalon.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!formularioSalon.reportValidity()) return;

    const actual = nombreSalonOriginal.value;
    const nombre = nombreSalon.value.trim();
    if (!nombre) return;

    guardarSalon.disabled = true;
    fetch(urlSalones + (actual ? '?nombre=' + encodeURIComponent(actual) : ''), {
        method: actual ? 'PUT' : 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': formularioSalon.dataset.csrf
        },
        body: JSON.stringify(actual
            ? { descripcion: descripcionSalon.value.trim() }
            : { nombre_salon: nombre, descripcion: descripcionSalon.value.trim() })
    })
        .then(leerRespuestaSalones)
        .then(function (datos) {
            restablecerFormularioSalon();
            mostrarResultadoSalones(datos.message, true);
            return cargarSalones();
        })
        .catch(function (error) {
            mostrarResultadoSalones(error.message, false);
        })
        .finally(function () {
            guardarSalon.disabled = false;
        });
});

cancelarEdicionSalon.addEventListener('click', function (evento) {
    evento.preventDefault();
    restablecerFormularioSalon();
});

listaSalones.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton) return;
    const fila = boton.closest('tr');
    const nombre = fila.dataset.nombre;

    if (boton.dataset.accion === 'editar') {
        nombreSalonOriginal.value = nombre;
        nombreSalon.value = nombre;
        nombreSalon.readOnly = true;
        descripcionSalon.value = fila.dataset.descripcion;
        guardarSalon.textContent = TEXTOS.btn_guardar_cambios;
        cancelarEdicionSalon.classList.remove('oculta');
        descripcionSalon.focus();
        return;
    }

    if (!window.confirm(TEXTOS.js_confirm_eliminar_salon)) return;
    boton.disabled = true;
    fetch(urlSalones + '?nombre=' + encodeURIComponent(nombre), {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-Token': formularioSalon.dataset.csrf
        }
    })
        .then(leerRespuestaSalones)
        .then(function (datos) {
            if (nombreSalonOriginal.value === nombre) restablecerFormularioSalon();
            mostrarResultadoSalones(datos.message, true);
            return cargarSalones();
        })
        .catch(function (error) {
            mostrarResultadoSalones(error.message, false);
            boton.disabled = false;
        });
});

cargarSalones().catch(function (error) {
    mostrarResultadoSalones(error.message, false);
});
