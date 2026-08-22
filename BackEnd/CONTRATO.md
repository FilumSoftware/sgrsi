# Contrato de la capa de datos

Para trabajar en paralelo: qué métodos existen y qué devuelve cada uno.

## Cómo se usa desde una vista

```php
require_once __DIR__ . '/../../BackEnd/dao/TicketDAO.php';

$dao     = new TicketDAO();
$tickets = $dao->obtenerTodos();
?>
<?php foreach ($tickets as $t) { ?>
    <tr>
        <td><?php echo $t['id_ticket']; ?></td>
        <td><?php echo $t['nombre_salon']; ?></td>
        <td><?php echo $t['estado_ticket']; ?></td>
    </tr>
<?php } ?>
```

La vista no escribe SQL ni instancia `Conexion`. Pide el DAO y muestra.

## Qué devuelven

Los DAO devuelven **arrays asociativos**, igual que el `ProductoDAO` del
teórico. Las claves son los nombres de las columnas.

- `obtenerTodos()` y similares devuelven un array de filas. Si no hay nada,
  array vacío, así que el `foreach` no rompe.
- `obtenerPorId()` devuelve una fila, o **`false`** si no existe. Es lo que
  devuelve `fetch()` de PDO. Chequear con `if (!$fila)`.
- `insertar()` devuelve el id nuevo. `actualizar()` y `eliminar()` devuelven
  `true` o `false`.

Los modelos (`models/`) se usan al revés: se arma el objeto y se le pasa al DAO
para insertar o actualizar. Además tienen los métodos de negocio, por ejemplo
`Usuario::esTecnico()` o `Equipo::estaDeBaja()`.

## Métodos por DAO

### UsuarioDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | filas con `ci, nombre_usuario, email, tipo_de_usuario, estado_cuenta` |
| `obtenerPorCi($ci)` | una fila o `false` |
| `obtenerParaLogin($ci)` | igual pero incluye `contrasena`. Solo la usa el login. |
| `obtenerTecnicos()` | Asistentes y Coordinadores activos. Es el dropdown de responsable. |
| `insertar(Usuario $u, $claveEnClaro)` | `bool`. Hashea la clave adentro. |
| `actualizar(Usuario $u)` | `bool` |
| `cambiarEstado($ci, $estado)` | `bool`, para la baja lógica |
| `cambiarContrasena($ci, $claveEnClaro)` | `bool` |

El hash **no** sale en `obtenerTodos()` ni en `obtenerPorCi()`. Solo en
`obtenerParaLogin()`.

### SalonDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `nombre_salon, descripcion`. Llena los `<select>` de salón. |
| `obtenerPorNombre($nombre)` | una fila o `false` |

### EquipoDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | `id_equipo, nombre_equipo, categoria, descripcion, nombre_salon, estado_equipo` |
| `obtenerPorId($id)` | una fila o `false` |
| `obtenerPorSalon($nombreSalon)` | para el `<select>` de equipo dependiente del salón |
| `insertar(Equipo $e)` | id nuevo |
| `actualizar(Equipo $e)` | `bool` |
| `eliminar($id)` | `bool` |

### TicketDAO
Las filas traen, además de las columnas de `ticket`: `nombre_equipo`,
`nombre_salon`, `nombre_solicitante` y `nombre_responsable`.

`nombre_responsable` viene en `null` si el ticket todavía no tiene técnico
asignado. La fila igual aparece, es un `LEFT JOIN`.

| Método | Devuelve |
|---|---|
| `obtenerTodos()` | todas, de la más nueva a la más vieja |
| `obtenerPorId($id)` | una fila o `false` |
| `obtenerPorSolicitante($ci)` | lo que ve un Solicitante |
| `obtenerPorEstado($estado)` | filtro de la bandeja |
| `insertar(Ticket $t)` | id nuevo |
| `actualizar(Ticket $t)` | `bool` |
| `eliminar($id)` | `bool` |
| `contarPorEstado()` | `estado_ticket, cantidad`. Para el dashboard. |

### SolicitudDAO
Mismos métodos que `TicketDAO`, con `nombre_solicitante` y
`nombre_responsable` resueltos. El salón es columna propia de la tabla.

### UsoSalaDAO
| Método | Devuelve |
|---|---|
| `obtenerTodos()` | cabeceras con `nombre_docente` resuelto |
| `obtenerPorId($id)` | una cabecera o `false` |
| `obtenerDetalle($idUso)` | `id_equipo, nombre_equipo, nombre_alumno` |
| `insertar(UsoSala $u, $detalles)` | id nuevo. `$detalles` es un array de `DetalleUso`. |
| `eliminar($idUso)` | `bool`. La base borra el detalle en cascada. |

`insertar()` va en transacción: si falla una fila del detalle, no queda la
cabecera suelta. Si algo sale mal lanza una `Exception` con mensaje genérico,
hay que envolverla en `try/catch` y mostrar el mensaje al usuario.

`nombre_alumno` puede venir `null`: el docente registra la máquina sin
identificar quién la usó.

## Valores de los dominios

Los `<select>` y los badges tienen que usar exactamente estos valores, que son
los que acepta la base:

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

## Todavía sin hacer

`PrestamoDAO` e `IntervencionDAO`. No los necesita ninguna pantalla del front,
por eso quedaron para el final.
