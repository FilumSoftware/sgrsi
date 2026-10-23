const formulario = document.getElementById('form-detalle-ticket');
const inputMotivo = document.getElementById('motivo');

const MOTIVO_MINIMO = 10;

if (!inputMotivo.disabled) {

    formulario.addEventListener('submit', function (evento) {
        if (!validarTexto(inputMotivo, 'error-motivo', MOTIVO_MINIMO, 'el motivo')) {
            evento.preventDefault();
        }
    });

    limpiarAlEscribir(inputMotivo, 'error-motivo', MOTIVO_MINIMO);
}

const botonCancelar = document.getElementById('btn-cancelar-ticket');

if (botonCancelar) {
    botonCancelar.addEventListener('click', function () {
        if (!window.confirm('¿Querés cancelar este ticket? Esta acción no se puede deshacer.')) {
            return;
        }

        const resultado = document.getElementById('resultado-cancelacion');
        botonCancelar.disabled = true;

        fetch('../../../BackEnd/api/tickets.php?id=' + encodeURIComponent(botonCancelar.dataset.id), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': botonCancelar.dataset.csrf
            },
            credentials: 'same-origin'
        })
            .then(function (respuesta) {
                return respuesta.json().then(function (datos) {
                    if (!respuesta.ok || datos.status !== 'success') {
                        throw new Error(datos.message || 'No se pudo cancelar el ticket.');
                    }
                    return datos;
                });
            })
            .then(function (datos) {
                const estado = document.getElementById('estado');
                estado.add(new Option('Cancelado', 'Cancelado'));
                estado.value = datos.data.estado_ticket;
                document.querySelectorAll('#form-detalle-ticket input, #form-detalle-ticket select, #form-detalle-ticket textarea, #form-detalle-ticket button')
                    .forEach(function (control) { control.disabled = true; });
                botonCancelar.remove();
                resultado.textContent = datos.message;
                resultado.classList.add('mensaje-exito');
                resultado.style.display = 'block';
            })
            .catch(function (error) {
                resultado.textContent = error.message || 'No se pudo cancelar el ticket.';
                resultado.style.display = 'block';
                botonCancelar.disabled = false;
            });
    });
}
