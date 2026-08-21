const params = new URLSearchParams(window.location.search);
const id = params.get('id');

const usuarios = {
    1: { nombre: 'Juan Perez', email: 'jperez@itr.edu.uy', tipo: 'solicitante' },
    2: { nombre: 'Maria Garcia', email: 'mgarcia@itr.edu.uy', tipo: 'solicitante' },
    3: { nombre: 'Carlos Lopez', email: 'clopez@itr.edu.uy', tipo: 'asistente' },
    4: { nombre: 'Ana Rodriguez', email: 'arodriguez@itr.edu.uy', tipo: 'asistente' },
    5: { nombre: 'Luis Martinez', email: 'lmartinez@itr.edu.uy', tipo: 'coordinador' }
};

const usuario = usuarios[id];

if (usuario) {
    document.getElementById('nombre').value = usuario.nombre;
    document.getElementById('email').value = usuario.email;
    document.getElementById('tipo-usuario').value = usuario.tipo;
}

document.getElementById('form-detalle-usuario').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Guardado (pendiente de conexión con el backend)');
});