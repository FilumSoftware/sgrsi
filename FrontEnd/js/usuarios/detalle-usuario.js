const formulario = document.getElementById('form-detalle-usuario');
const inputNombre = document.getElementById('nombre');
const resultadoGuardado = document.getElementById('resultado-guardado');
const ciUsuario = formulario.elements.namedItem('ci').value;
let formularioModificado = false;

formulario.addEventListener('input', function () { formularioModificado = true; });
formulario.addEventListener('change', function () { formularioModificado = true; });

fetch('../../../BackEnd/api/usuarios.php?ci=' + encodeURIComponent(ciUsuario), {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
})
    .then(function (respuesta) {
        return respuesta.json().then(function (datos) {
            if (!respuesta.ok || datos.status !== 'success') throw new Error(datos.message || 'No se pudo cargar el usuario.');
            return datos.data;
        });
    })
    .then(function (usuario) {
        if (formularioModificado) return;
        inputNombre.value = usuario.nombre_usuario;
        document.getElementById('tipo-usuario').value = usuario.tipo_de_usuario;
        document.getElementById('estado').value = usuario.estado_cuenta;
    })
    .catch(function (error) {
        resultadoGuardado.textContent = error.message || 'No se pudo cargar el usuario.';
        resultadoGuardado.classList.remove('mensaje-exito');
        resultadoGuardado.style.display = 'block';
    });

formulario.addEventListener('submit', function (evento) {
    evento.preventDefault();
    if (!validarTexto(inputNombre, 'error-nombre', 1, 'el nombre') || !formulario.reportValidity()) return;

    const datos = { nombre_usuario: inputNombre.value.trim() };
    const rol = document.getElementById('tipo-usuario');
    const estado = document.getElementById('estado');
    if (!rol.disabled) {
        datos.tipo_de_usuario = rol.value;
        datos.estado_cuenta = estado.value;
    }
    const boton = document.getElementById('btn-guardar');
    boton.disabled = true;
    fetch('../../../BackEnd/api/usuarios.php?ci=' + encodeURIComponent(ciUsuario), {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': formulario.dataset.csrf
        },
        body: JSON.stringify(datos)
    })
        .then(function (respuesta) {
            return respuesta.json().then(function (datosRespuesta) {
                if (!respuesta.ok || datosRespuesta.status !== 'success') throw new Error(datosRespuesta.message || 'No se pudo guardar el usuario.');
                return datosRespuesta;
            });
        })
        .then(function (datosRespuesta) {
            resultadoGuardado.textContent = datosRespuesta.message;
            resultadoGuardado.classList.add('mensaje-exito');
            resultadoGuardado.style.display = 'block';
        })
        .catch(function (error) {
            resultadoGuardado.textContent = error.message || 'No se pudo guardar el usuario.';
            resultadoGuardado.classList.remove('mensaje-exito');
            resultadoGuardado.style.display = 'block';
        })
        .finally(function () {
            boton.disabled = false;
        });
});

limpiarAlEscribir(inputNombre, 'error-nombre', 1);
