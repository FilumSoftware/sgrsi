function mostrarError(campo, idError, mensaje) {
    const error = document.getElementById(idError);
    error.textContent = mensaje;
    error.style.display = 'block';
    campo.classList.add('input-error');
}

function limpiarError(campo, idError) {
    const error = document.getElementById(idError);
    error.style.display = 'none';
    campo.classList.remove('input-error');
}

function validarTexto(campo, idError, minimo, etiqueta) {
    const texto = campo.value.trim();

    if (texto === '') {
        mostrarError(campo, idError, 'Completá ' + etiqueta + '.');
        return false;
    }

    if (texto.length < minimo) {
        mostrarError(campo, idError, 'Escribí al menos ' + minimo + ' caracteres.');
        return false;
    }

    limpiarError(campo, idError);
    return true;
}

function limpiarAlEscribir(campo, idError, minimo) {
    campo.addEventListener('input', function () {
        if (campo.value.trim().length >= minimo) {
            limpiarError(campo, idError);
        }
    });
}
