const formulario = document.getElementById('form-uso-sala');
const horaEntrada = document.getElementById('entrada');
const horaSalida = document.getElementById('salida');
const errorSalida = document.getElementById('error-salida');
const salon = document.getElementById('salon');
const listaPcs = document.getElementById('lista-pcs');
const resultadoUsoSala = document.getElementById('resultado-uso-sala');
const idRegistro = formulario.elements.namedItem('id');
let botonAgregarPc = document.getElementById('agregar-pc');
let cargaSalon = 0;

function leerRespuestaUsoSala(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultadoUsoSala(mensaje, exito) {
    resultadoUsoSala.textContent = mensaje;
    resultadoUsoSala.classList.toggle('mensaje-exito', exito);
    resultadoUsoSala.style.display = 'block';
}

function actualizarBotonAgregar() {
    if (!botonAgregarPc) return;
    botonAgregarPc.classList.toggle('oculta', !listaPcs.querySelector('.fila-pc.oculta'));
}

function crearFilaPc(equipos, numero) {
    const fila = document.createElement('div');
    fila.className = 'fila-pc' + (numero > 1 ? ' oculta' : '');
    fila.dataset.index = numero;

    const campoPc = document.createElement('div');
    campoPc.className = 'campo';
    const etiquetaPc = document.createElement('label');
    etiquetaPc.htmlFor = 'pc-' + numero;
    etiquetaPc.textContent = 'PC';
    const selector = document.createElement('select');
    selector.id = 'pc-' + numero;
    selector.name = 'pc[' + numero + ']';
    selector.add(new Option('-- Seleccioná una PC --', ''));
    equipos.forEach(function (equipo) {
        selector.add(new Option(equipo.nombre_equipo, equipo.id_equipo));
    });
    campoPc.append(etiquetaPc, selector);

    const campoAlumno = document.createElement('div');
    campoAlumno.className = 'campo';
    const etiquetaAlumno = document.createElement('label');
    etiquetaAlumno.htmlFor = 'alumno-' + numero;
    etiquetaAlumno.textContent = 'Alumno';
    const alumno = document.createElement('input');
    alumno.type = 'text';
    alumno.id = 'alumno-' + numero;
    alumno.name = 'alumno[' + numero + ']';
    alumno.placeholder = 'Nombre del alumno';
    campoAlumno.append(etiquetaAlumno, alumno);
    fila.append(campoPc, campoAlumno);
    return fila;
}

function mostrarEquipos(equipos) {
    listaPcs.querySelectorAll('.fila-pc, .sin-accion').forEach(function (elemento) { elemento.remove(); });
    if (!equipos.length) {
        const aviso = document.createElement('p');
        aviso.className = 'sin-accion';
        aviso.textContent = 'Elegí un salón para asignar las PC.';
        listaPcs.insertBefore(aviso, botonAgregarPc || document.getElementById('error-pcs'));
    } else {
        const referencia = botonAgregarPc || document.getElementById('error-pcs');
        equipos.forEach(function (equipo, indice) {
            listaPcs.insertBefore(crearFilaPc(equipos, indice + 1), referencia);
        });
        if (!botonAgregarPc && equipos.length > 1) {
            botonAgregarPc = document.createElement('button');
            botonAgregarPc.type = 'button';
            botonAgregarPc.id = 'agregar-pc';
            botonAgregarPc.textContent = '+ Agregar PC';
            listaPcs.insertBefore(botonAgregarPc, document.getElementById('error-pcs'));
        }
    }
    actualizarBotonAgregar();
}

listaPcs.addEventListener('click', function (evento) {
    if (!evento.target.closest('#agregar-pc')) return;
    const siguiente = listaPcs.querySelector('.fila-pc.oculta');
    if (siguiente) siguiente.classList.remove('oculta');
    actualizarBotonAgregar();
});
actualizarBotonAgregar();

salon.addEventListener('change', function () {
    const solicitud = ++cargaSalon;
    if (!salon.value) {
        mostrarEquipos([]);
        return;
    }
    fetch('../../../BackEnd/api/catalogos.php?tipo=equipos&salon=' + encodeURIComponent(salon.value), {
        credentials: 'same-origin'
    })
        .then(leerRespuestaUsoSala)
        .then(function (datos) {
            if (solicitud === cargaSalon) mostrarEquipos(datos.data || []);
        })
        .catch(function (error) {
            if (solicitud !== cargaSalon) return;
            mostrarEquipos([]);
            mostrarResultadoUsoSala(error.message, false);
        });
});

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (horaEntrada.value !== '' && horaSalida.value !== '' && horaSalida.value <= horaEntrada.value) {
        errorSalida.textContent = 'La hora de salida debe ser posterior a la de entrada.';
        errorSalida.style.display = 'block';
        horaSalida.classList.add('input-error');
        return;
    }
    errorSalida.style.display = 'none';
    horaSalida.classList.remove('input-error');
    if (!formulario.reportValidity()) return;

    const equipos = Array.from(listaPcs.querySelectorAll('.fila-pc:not(.oculta)'))
        .map(function (fila) {
            return {
                id_equipo: fila.querySelector('select').value,
                nombre_alumno: fila.querySelector('input').value.trim()
            };
        })
        .filter(function (equipo) { return equipo.id_equipo !== ''; });
    if (!equipos.length) {
        const errorPcs = document.getElementById('error-pcs');
        errorPcs.textContent = 'Asigná al menos una PC.';
        errorPcs.style.display = 'block';
        return;
    }
    document.getElementById('error-pcs').style.display = 'none';
    const botonEnviar = document.getElementById('btn-enviar');
    if (!botonEnviar) return;
    botonEnviar.disabled = true;
    const url = '../../../BackEnd/api/uso-sala.php' + (idRegistro ? '?id=' + encodeURIComponent(idRegistro.value) : '');
    fetch(url, {
        method: idRegistro ? 'PUT' : 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': formulario.dataset.csrf
        },
        body: JSON.stringify({
            fecha: document.getElementById('fecha').value,
            hora_inicio: horaEntrada.value,
            hora_fin: horaSalida.value,
            nombre_salon: salon.value,
            asignatura: document.getElementById('asignatura').value.trim(),
            grupo: document.getElementById('grupo').value.trim(),
            turno: document.getElementById('turno').value,
            equipos: equipos
        })
    })
        .then(leerRespuestaUsoSala)
        .then(function (datos) {
            mostrarResultadoUsoSala(datos.message, true);
            if (!idRegistro) {
                formulario.reset();
                mostrarEquipos([]);
            }
        })
        .catch(function (error) {
            mostrarResultadoUsoSala(error.message, false);
        })
        .finally(function () {
            botonEnviar.disabled = false;
        });
});
