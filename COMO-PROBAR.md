# Cómo probar el sistema

## Por qué ya no alcanza con abrir el archivo

El frontend se probaba abriendo `index.html` porque el HTML lo interpreta el
navegador solo. PHP no: el código se ejecuta **en el servidor**, que recién
después manda HTML al navegador. Si abrís un `.php` con doble clic, el navegador
no tiene a quién pedirle que lo ejecute y te muestra el código o te lo baja.

Así que a partir de ahora hay que levantar un servidor y entrar por
`http://localhost`, no por `file:///`.

## Levantar el servidor

Dos opciones. Cualquiera sirve.

### Servidor de PHP, sin configurar nada

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -S localhost:8080 -t C:\Users\dario\Documents\Proyecto\Codigo
```

Se deja esa ventana abierta y se entra a `http://localhost:8080`. Para apagarlo,
Ctrl+C.

### Laragon, que es lo que va a usar el proyecto

Abrir Laragon y darle **Start All**. Levanta Apache y sirve desde
`C:\laragon\www`, así que hay que hacer que el proyecto sea visible desde ahí.
Lo más simple es una unión de directorios, en PowerShell como administrador:

```bash
New-Item -ItemType Junction -Path C:\laragon\www\sgrsi -Target C:\Users\dario\Documents\Proyecto\Codigo
```

Después se entra a `http://localhost/sgrsi`.

## Verificar que está todo conectado

```
http://localhost:8080/BackEnd/prueba/verificacion.php
```

Esa página revisa la cadena entera y muestra en verde o rojo cada eslabón: que
el servidor ejecute PHP, que `pdo_mysql` esté cargada, que conecte con MariaDB,
que el Singleton devuelva una sola conexión, que los DAO lean y que las tildes
lleguen bien. Abajo lista los tickets salidos de la base.

Si algo se rompe más adelante, esa página dice cuál eslabón se cortó antes de
ponerse a revisar código.

## Qué se puede probar hoy y qué no

| | Estado |
|---|---|
| Servidor, PHP, PDO, conexión a MariaDB | Andando |
| Modelos y DAO de usuarios, salones, equipos, tickets, solicitudes y uso de sala | Andando |
| Las pantallas del front | **Todavía no.** Siguen siendo `.html` con datos escritos a mano. |
| Guardar un formulario y que impacte en la base | **Todavía no.** Falta que los formularios posteen a PHP. |
| Login, logout y control por rol | **Todavía no.** Quedó para después de las pantallas. |

O sea: la cañería está probada y funciona, pero las pantallas todavía no están
enchufadas. Eso es la conversión de `.html` a `.php` que falta.

## Requisito del entorno

`pdo_mysql` tiene que estar habilitada en el `php.ini`. Ya quedó configurado en
`C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini`. Si alguien clona el
repositorio en otra máquina y le falla la conexión, es lo primero a revisar.

## Volver la base al estado inicial

Se puede romper todo sin miedo. Para reponer los datos de prueba:

```bash
"C:\Program Files\MariaDB 12.3\bin\mariadb.exe" -h 127.0.0.1 -P 3307 -u root < BaseDeDatos\ddl.sql
```

```bash
"C:\Program Files\MariaDB 12.3\bin\mariadb.exe" -h 127.0.0.1 -P 3307 -u root -D sgrsi < BaseDeDatos\dml.sql
```
