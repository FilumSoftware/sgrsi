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
    estadoMetricas.textContent = 'Actualizando métricas…';

    fetch('../../../BackEnd/api/metricas.php', {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    })
        .then(function (respuesta) {
            return respuesta.json().then(function (datos) {
                if (!respuesta.ok || datos.status !== 'success') {
                    throw new Error(datos.message || 'No se pudieron cargar las métricas.');
                }
                return datos.data;
            });
        })
        .then(function (datos) {
            reemplazarFilas(
                document.getElementById('equipos-mas-fallados'),
                datos.equipos_mas_fallados,
                ['nombre_equipo', 'nombre_salon', 'total_fallas'],
                'Todavía no hay fallas registradas.'
            );
            reemplazarFilas(
                document.getElementById('solicitudes-por-mes'),
                datos.solicitudes_por_mes,
                ['periodo', 'cantidad'],
                'Todavía no hay solicitudes registradas.'
            );
            const promedio = datos.tiempo_promedio_resolucion_horas;
            document.getElementById('promedio-resolucion').textContent =
                promedio === null ? 'Sin tickets resueltos' : String(promedio) + ' horas';
            estadoMetricas.textContent = 'Métricas actualizadas.';
        })
        .catch(function (error) {
            estadoMetricas.textContent = error.message || 'No se pudieron cargar las métricas.';
        })
        .finally(function () {
            botonActualizarMetricas.disabled = false;
        });
}

botonActualizarMetricas.addEventListener('click', cargarMetricas);
cargarMetricas();
