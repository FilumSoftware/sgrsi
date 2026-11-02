const botonActualizarMetricas = document.getElementById('actualizar-metricas');
const estadoMetricas = document.getElementById('estado-metricas');

function reemplazarFilas(tbody, filas, columnas, vacio) {
    tbody.replaceChildren();

    if (!filas.length) {
        const fila = document.createElement('tr');
        const celda = document.createElement('td');
        celda.colSpan = columnas.length;
        celda.textContent = vacio;
        fila.appendChild(celda);
        tbody.appendChild(fila);
        return;
    }

    filas.forEach(function (item) {
        const fila = document.createElement('tr');
        columnas.forEach(function (columna) {
            const celda = document.createElement('td');
            celda.textContent = item[columna] == null ? '' : String(item[columna]);
            fila.appendChild(celda);
        });
        tbody.appendChild(fila);
    });
}

function cargarMetricas() {
    botonActualizarMetricas.disabled = true;
    estadoMetricas.textContent = TEXTOS.js_actualizando_metricas;

    fetch('../../../BackEnd/api/metricas.php', {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
        .then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok || datos.status !== 'success') {
                    throw new Error(datos.message || TEXTOS.js_error_metricas);
                }
                return datos.data;
            });
        })
        .then(function (datos) {
            reemplazarFilas(
                document.getElementById('equipos-mas-fallados'),
                datos.equipos_mas_fallados,
                ['nombre_equipo', 'nombre_salon', 'total_fallas'],
                TEXTOS.js_sin_fallas
            );
            reemplazarFilas(
                document.getElementById('solicitudes-por-mes'),
                datos.solicitudes_por_mes,
                ['periodo', 'cantidad'],
                TEXTOS.js_sin_solicitudes
            );
            const promedio = datos.tiempo_promedio_resolucion_horas;
            document.getElementById('promedio-resolucion').textContent =
                promedio === null ? TEXTOS.js_sin_resueltos : String(promedio) + ' ' + TEXTOS.js_horas;
            estadoMetricas.textContent = TEXTOS.js_metricas_actualizadas;
        })
        .catch(function (error) {
            estadoMetricas.textContent = TEXTOS[error.message] || error.message || TEXTOS.js_error_metricas;
        })
        .finally(function () {
            botonActualizarMetricas.disabled = false;
        });
}

botonActualizarMetricas.addEventListener('click', function (evento) {
    evento.preventDefault();
    cargarMetricas();
});
