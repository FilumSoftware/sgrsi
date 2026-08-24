const filas = document.querySelectorAll('tbody tr');

for (let i = 0; i < filas.length; i++) {
    const enlace = filas[i].querySelector('.tabla-enlace');

    if (!enlace) {
        continue;
    }

    filas[i].classList.add('fila-clickeable');

    filas[i].addEventListener('click', function (evento) {
        if (evento.target.closest('a, button, input, select, textarea, label, form')) {
            return;
        }

        if (window.getSelection().toString() !== '') {
            return;
        }

        window.location.href = enlace.href;
    });
}
