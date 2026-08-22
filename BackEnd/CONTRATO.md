# Contrato de la capa de datos

Para trabajar en paralelo: acá están los métodos que van a existir y qué
devuelve cada uno. Las vistas se pueden escribir contra esto antes de que los
DAO estén implementados.

## Cómo se usa desde una vista

```php
require_once __DIR__ . '/../../BackEnd/dao/TicketDAO.php';

$dao     = new TicketDAO();
$tickets = $dao->obtenerTodos();

foreach ($tickets as $t) {
    echo $t->getIdTicket();
    echo $t->getNombreSalon();
    echo $t->getEstadoTicket();
}
```

La vista nunca escribe SQL ni instancia `Conexion`. Pide el DAO y muestra.

## Qué devuelven

Los DAO devuelven **objetos**, no arrays. Un `obtenerTodos()` devuelve un array
de objetos; un `obtenerPorId()` devuelve un objeto o `null` si no existe.

Los modelos tienen los atributos privados y un getter por cada uno. Cuando la
consulta trae datos de una tabla vecina por JOIN, el modelo los expone como un
getter más: por ejemplo `Ticket::getNombreSalon()` viene de `equipo`, y
`Ticket::getNombreResponsable()` de `usuario`.

## Métodos por DAO

### UsuarioDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `Usuario[]` |
| `obtenerPorCi($ci)` | `Usuario` o `null` |
| `obtenerTecnicos()` | `Usuario[]`, solo Asistentes y Coordinadores. Es el dropdown de responsable. |
| `insertar(Usuario $u, $claveEnClaro)` | `bool` |
| `actualizar(Usuario $u)` | `bool` |
| `cambiarEstado($ci, $estado)` | `bool`, para la baja lógica |

### SalonDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `Salon[]`, para llenar los `<select>` de salón |
| `obtenerPorNombre($nombre)` | `Salon` o `null` |

### EquipoDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `Equipo[]` |
| `obtenerPorId($id)` | `Equipo` o `null` |
| `obtenerPorSalon($nombreSalon)` | `Equipo[]`, para el `<select>` de equipo dependiente del salón |
| `insertar(Equipo $e)` | `bool` |
| `actualizar(Equipo $e)` | `bool` |
| `eliminar($id)` | `bool` |

### TicketDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `Ticket[]`, con salón y nombres de solicitante y responsable resueltos |
| `obtenerPorId($id)` | `Ticket` o `null` |
| `obtenerPorSolicitante($ci)` | `Ticket[]`, lo que ve un Solicitante |
| `insertar(Ticket $t)` | `int`, el id nuevo |
| `actualizar(Ticket $t)` | `bool` |

### SolicitudDAO
Mismos métodos que `TicketDAO`, con `Solicitud`.

### UsoSalaDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `UsoSala[]`, para el historial |
| `obtenerPorId($id)` | `UsoSala` o `null` |
| `obtenerDetalle($idUso)` | `DetalleUso[]`, las filas de PC y alumno |
| `insertar(UsoSala $u, array $detalles)` | `int`. Cabecera y detalle en una transacción: o entran los dos o no entra ninguno. |

### PrestamoDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `Prestamo[]` |
| `obtenerVigentes()` | `Prestamo[]`, los que no tienen devolución |
| `insertar(Prestamo $p)` | `int` |
| `registrarDevolucion($idPrestamo, $fechaHora)` | `bool` |

### IntervencionDAO
| Método | Devuelve |
|---|---|
| `obtenerPorEquipo($idEquipo)` | `Intervencion[]`, el historial del equipo |
| `insertar(Intervencion $i)` | `int` |

## Valores de los dominios

Los `<select>` y los badges tienen que usar exactamente estos valores, que son
los del diccionario de datos y los que acepta la base:

| Campo | Valores |
|---|---|
| Rol | Solicitante, Asistente, Coordinador |
| Estado de cuenta | Activa, Inactiva |
| Estado de equipo | Operativo, En reparación, Derivado, En trámite de baja, De baja |
| Categoría de equipo | PC de escritorio, Laptop, Proyector, Impresora, Otro |
| Estado de ticket | Pendiente, En proceso, Resuelto |
| Estado de solicitud | Pendiente, En proceso, Resuelta |
| Prioridad | Urgente, Prioritario, Normal |
| Tipo de defecto | No enciende, Sin imagen, Sin red, Sin audio, Periférico dañado, Software, Otros |
| Turno | Matutino, Vespertino, Nocturno |

Los `<select>` de salón y de equipo no se escriben a mano: salen de
`SalonDAO::obtenerTodos()` y `EquipoDAO::obtenerPorSalon()`.
