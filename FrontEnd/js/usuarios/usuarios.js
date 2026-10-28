const listaUsuarios = document.getElementById('lista-usuarios');
const resultadoUsuarios = document.getElementById('resultado-usuarios');

function leerRespuestaUsuarios(respuesta) {
    return respuesta.json().then(function (datos) {
        if (!respuesta.ok || datos.status !== 'success') {
            throw new Error(datos.message || 'No se pudo completar la operación.');
        }
        return datos;
    });
}

function mostrarResultadoUsuarios(mensaje, exito) {
    resultadoUsuarios.textContent = mensaje;
    resultadoUsuarios.classList.toggle('mensaje-exito', exito);
    resultadoUsuarios.style.display = 'block';
}

function celda(fila, contenido) {
    const elemento = document.createElement('td');
    if (contenido instanceof Node) elemento.append(contenido);
    else elemento.textContent = contenido == null ? '' : String(contenido);
    fila.append(elemento);
    return elemento;
}

function crearFilaUsuario(usuario) {
    const fila = document.createElement('tr');
    const url = 'detalle-usuario.php?ci=' + encodeURIComponent(usuario.ci);
    const enlace = document.createElement('a');
    enlace.className = 'tabla-enlace';
    enlace.href = url;
    enlace.textContent = usuario.ci;
    celda(fila, enlace);
    celda(fila, usuario.nombre_usuario);
    celda(fila, usuario.tipo_de_usuario);
    const estado = celda(fila, usuario.estado_cuenta);
    estado.dataset.estado = '1';
    fila.classList.toggle('fila-desactivada', usuario.estado_cuenta === 'Inactiva');
    fila.classList.add('fila-clickeable');

    const acciones = celda(fila, '');
    acciones.className = 'acciones';
    const editar = document.createElement('a');
    editar.className = 'btn-editar';
    editar.href = url + '&modo=editar';
    editar.textContent = 'Editar';
    acciones.append(editar);

    if (usuario.ci === listaUsuarios.dataset.ci) {
        const propia = document.createElement('span');
        propia.className = 'sin-accion';
        propia.textContent = 'Tu cuenta';
        acciones.append(propia);
    } else {
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.dataset.ci = usuario.ci;
        boton.dataset.estado = usuario.estado_cuenta;
        boton.className = usuario.estado_cuenta === 'Inactiva' ? 'btn-editar' : 'btn-eliminar';
        boton.textContent = usuario.estado_cuenta === 'Inactiva' ? 'Habilitar' : 'Inhabilitar';
        acciones.append(boton);
    }
    return fila;
}

function cargarUsuarios() {
    fetch('../../../BackEnd/api/usuarios.php', { credentials: 'same-origin' })
        .then(leerRespuestaUsuarios)
        .then(function (datos) {
            const usuarios = datos.data || [];
            if (usuarios.length) listaUsuarios.replaceChildren(...usuarios.map(crearFilaUsuario));
            else {
                const fila = document.createElement('tr');
                const vacio = celda(fila, 'No hay usuarios registrados.');
                vacio.colSpan = 5;
                listaUsuarios.replaceChildren(fila);
            }
        })
        .catch(function (error) {
            mostrarResultadoUsuarios(error.message, false);
        });
}

listaUsuarios.addEventListener('click', function (evento) {
    const boton = evento.target.closest('button[data-ci]');
    if (boton) {
        if (!window.confirm('¿Seguro que querés cambiar el estado de esta cuenta?')) return;
        const inactiva = boton.dataset.estado === 'Inactiva';
        const nuevoEstado = inactiva ? 'Activa' : 'Inactiva';
        const url = '../../../BackEnd/api/usuarios.php?ci=' + encodeURIComponent(boton.dataset.ci);
        boton.disabled = true;
        fetch(url, {
            method: inactiva ? 'PUT' : 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': listaUsuarios.dataset.csrf,
                ...(inactiva ? { 'Content-Type': 'application/json' } : {})
            },
            ...(inactiva ? { body: JSON.stringify({ estado_cuenta: nuevoEstado }) } : {})
        })
            .then(leerRespuestaUsuarios)
            .then(function (datos) {
                const fila = boton.closest('tr');
                fila.querySelector('[data-estado]').textContent = nuevoEstado;
                fila.classList.toggle('fila-desactivada', nuevoEstado === 'Inactiva');
                boton.dataset.estado = nuevoEstado;
                boton.className = nuevoEstado === 'Inactiva' ? 'btn-editar' : 'btn-eliminar';
                boton.textContent = nuevoEstado === 'Inactiva' ? 'Habilitar' : 'Inhabilitar';
                mostrarResultadoUsuarios(datos.message, true);
            })
            .catch(function (error) {
                mostrarResultadoUsuarios(error.message, false);
            })
            .finally(function () {
                boton.disabled = false;
            });
        return;
    }
    if (evento.target.closest('a, button') || window.getSelection().toString() !== '') return;
    const enlace = evento.target.closest('tr')?.querySelector('.tabla-enlace');
    if (enlace) window.location.href = enlace.href;
});

cargarUsuarios();
