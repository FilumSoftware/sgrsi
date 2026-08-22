# Base de datos SGRSI

| Archivo | Qué hace |
|---|---|
| `ddl.sql` | Crea la base `sgrsi` y sus nueve tablas con PKs, FKs y restricciones. |
| `dml.sql` | Carga los datos de prueba de los seis módulos y los tres roles. |

`ddl.sql` arranca con `DROP DATABASE IF EXISTS sgrsi`, así que volver a
ejecutarlo reconstruye todo desde cero.

## Tablas

| Tabla | Qué guarda |
|---|---|
| `usuario` | Actores del sistema. La cédula es la clave primaria. |
| `salon` | Laboratorios, talleres y depósito. |
| `equipo` | Inventario, cada equipo en un salón. |
| `intervencion` | Historial técnico por equipo. |
| `ticket` | Incidencias sobre un equipo: Pendiente, En proceso, Resuelto. |
| `solicitud` | Pedidos de servicio sobre un salón. |
| `prestamo` | Préstamos y devoluciones. |
| `uso_sala` | Cabecera de la planilla de uso de salas. |
| `detalle_uso` | Entidad débil: qué equipo ocupó cada alumno. |

## Entorno

MariaDB 12.3 corre como servicio `MariaDB` en el **puerto 3307**. El 3306 sigue
siendo del servicio `MySQL80` de la materia Base de Datos, así que conviven sin
pisarse. Todo lo que apunte a la base del proyecto tiene que usar el 3307,
incluida la cadena de conexión de PDO.

Cliente: `C:\Program Files\MariaDB 12.3\bin\mariadb.exe`

## Ejecutar los scripts

Desde esta carpeta:

```bash
"C:\Program Files\MariaDB 12.3\bin\mariadb.exe" -h 127.0.0.1 -P 3307 -u root -p < ddl.sql
```

```bash
"C:\Program Files\MariaDB 12.3\bin\mariadb.exe" -h 127.0.0.1 -P 3307 -u root -p -D sgrsi < dml.sql
```

`-p` pide la contraseña de root que se definió al instalar MariaDB. También se
pueden abrir los dos archivos en HeidiSQL y ejecutarlos con F9.

## Verificar que PDO tenga el driver

El PHP de Laragon no traía `php.ini` propio, y sin él `pdo_mysql` no carga y
`new PDO('mysql:...')` falla con *could not find driver*. Para confirmar:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -m
```

Tiene que aparecer `pdo_mysql` en la lista. Si no está, hay que descomentar
`extension=pdo_mysql` en el `php.ini` que use Laragon.

## Usuarios de prueba

La contraseña de todos es `Sgrsi.2026`.

| Cédula | Nombre | Rol | Cuenta |
|---|---|---|---|
| 52618740 | Luis Martínez | Coordinador | Activa |
| 39482156 | Carlos López | Asistente | Activa |
| 44905381 | Ana Rodríguez | Asistente | Activa |
| 48219073 | Juan Pérez | Solicitante | Activa |
| 51730864 | María García | Solicitante | Activa |
| 41073925 | Marcia Antúnez | Solicitante | Activa |
| 37514962 | Mario Neta | Solicitante | Activa |
| 46283015 | Esteban Quito | Solicitante | **Inactiva** |

Esteban Quito está inactivo a propósito: sirve para probar que el login rechaza
una cuenta con baja lógica y que sus registros históricos siguen existiendo.

Las contraseñas se guardan como resúmenes bcrypt de `password_hash()`. Los ocho
son distintos aunque la clave sea la misma, porque bcrypt le agrega una sal
aleatoria a cada uno.

## Pruebas hechas sobre estos scripts

Se ejecutaron contra un servidor limpio y se verificó que:

- Las nueve tablas se crean y quedan cargadas: 8 usuarios, 10 salones,
  17 equipos, 6 tickets, 5 solicitudes, 6 intervenciones, 3 préstamos,
  3 registros de uso y 11 detalles.
- Los acentos y las eñes se guardan y se leen bien.
- El motor rechaza cédulas que no sean ocho dígitos, correos mal formados o
  duplicados, valores fuera del ENUM, tickets Resueltos sin responsable ni
  cierre, horarios con fin anterior al inicio, referencias a equipos
  inexistentes y el borrado de un salón que todavía tiene equipos.
- Borrar un `uso_sala` arrastra sus filas de `detalle_uso`.
- El login por PDO con `password_verify()` acepta las claves correctas y rechaza
  la equivocada, la cédula inexistente y la cuenta inactiva.
- Un intento de inyección SQL en el campo cédula queda neutralizado por el
  prepared statement.
