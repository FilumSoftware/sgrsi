const ids = [1, 2, 3, 4, 5];
const nombres = ['Juan Perez', 'Maria Garcia', 'Carlos Lopez', 'Ana Rodriguez', 'Luis Martinez'];
const emails = ['jperez@itr.edu.uy', 'mgarcia@itr.edu.uy', 'clopez@itr.edu.uy', 'arodriguez@itr.edu.uy', 'lmartinez@itr.edu.uy'];
const tipos = ['solicitante', 'solicitante', 'asistente', 'asistente', 'coordinador'];

const direccion = window.location.search;
const idBuscado = Number(direccion.substring(4));

let posicion = -1;
for (let i = 0; i < ids.length; i++) {
    if (ids[i] === idBuscado) {
        posicion = i;
    }
}

if (posicion !== -1) {
    document.getElementById('nombre').value = nombres[posicion];
    document.getElementById('email').value = emails[posicion];
    document.getElementById('tipo-usuario').value = tipos[posicion];
}
