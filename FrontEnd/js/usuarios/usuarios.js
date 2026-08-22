const ids = [1, 2, 3, 4, 5];

const filas = document.querySelectorAll('tbody tr');
const botonesEditar = document.querySelectorAll('.btn-editar');
const botonesDesactivar = document.querySelectorAll('.btn-eliminar');
const botonesReiniciar = document.querySelectorAll('.btn-reiniciar');

for (let i = 0; i < botonesEditar.length; i++) {
    botonesEditar[i].addEventListener('click', function () {
        window.location.href = 'detalle-usuario.html?id=' + ids[i];
    });
}

for (let i = 0; i < botonesDesactivar.length; i++) {
    botonesDesactivar[i].addEventListener('click', function () {
        const confirmar = confirm('¿Desactivar este usuario?');
        if (confirmar) {
            filas[i].classList.add('fila-desactivada');
        }
    });
}

for (let i = 0; i < botonesReiniciar.length; i++) {
    botonesReiniciar[i].addEventListener('click', function () {
        const confirmar = confirm('¿Reiniciar la contraseña de este usuario?');
        if (confirmar) {
            alert('Se envió el reinicio de contraseña');
        }
    });
}
