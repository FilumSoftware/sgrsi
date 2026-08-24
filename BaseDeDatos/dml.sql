-- SGRSI - Filum Software
-- Datos de prueba. Requiere haber ejecutado ddl.sql.
-- La contraseña de todos los usuarios es: Sgrsi.2026

USE sgrsi;

SET NAMES utf8mb4;


-- usuarios -------------------------------------------------------------
-- Esteban Quito queda Inactivo para probar la baja lógica.

INSERT INTO usuario (ci, nombre_usuario, contrasena, tipo_de_usuario, estado_cuenta) VALUES
('52618740', 'Luis Martínez',  '$2y$10$LltYL72bjZ/ts31kylDIqueujG.AObyxazdL83C3mBclTe9ftv4qC', 'Coordinador', 'Activa'),
('39482156', 'Carlos López',   '$2y$10$aHFk3heTJgkM7cuI7MHLnezg1GikFbXOpVqcgJEICgdkOhau0PwY6', 'Asistente',   'Activa'),
('44905381', 'Ana Rodríguez',  '$2y$10$Tu6Sv8TrKeggydJHOeqzhOnieiqIwvfQiq1IqlNBJPpFh/cGQuEoy', 'Asistente',   'Activa'),
('48219073', 'Juan Pérez',     '$2y$10$yN37wikwWgH12/GuVLgKce.mwioyGG0JbydufKCcA0Qcn9TW8ez96', 'Solicitante', 'Activa'),
('51730864', 'María García',   '$2y$10$xgACGhAOBYrp4Du2uT8zZuk3eNM7VD3FmdAt68WCw4kRx4dEY4nYO', 'Solicitante', 'Activa'),
('41073925', 'Marcia Antúnez', '$2y$10$w8jLaHma1WoPcnnA47FEhOhGJ1X01JMHIaD..NzX98nIPQyXTzdf2', 'Solicitante', 'Activa'),
('37514962', 'Mario Neta',     '$2y$10$T75D9SlL8qvAY6td3IYO4OfXB0mPucvzRk1F8yC75Hd4fwW8kOkEC', 'Solicitante', 'Activa'),
('46283015', 'Esteban Quito',  '$2y$10$QiAqCf7jmxIcYeWeNrLinOXF5gZ0tdgRtD61pebQnLL8uWPyENwfa', 'Solicitante', 'Inactiva');


-- salones --------------------------------------------------------------

INSERT INTO salon (nombre_salon, descripcion) VALUES
('Laboratorio 1', 'Laboratorio de informática, planta baja, 20 puestos'),
('Laboratorio 2', 'Laboratorio de informática, planta baja, 20 puestos'),
('Laboratorio 3', 'Laboratorio de informática, primer piso, 25 puestos'),
('Laboratorio 4', 'Laboratorio de informática, primer piso, 25 puestos'),
('Laboratorio 5', 'Laboratorio de redes, primer piso, 15 puestos'),
('Laboratorio 6', 'Laboratorio de electrónica, segundo piso, 15 puestos'),
('Taller 1',      'Taller, planta baja'),
('Taller 2',      'Taller de electricidad, planta baja'),
('Taller 3',      'Taller de mecánica, planta baja'),
('Depósito',      'Depósito de equipos en reserva y en trámite de baja');


-- equipos --------------------------------------------------------------
-- Ids explícitos para que las FK de este script se lean solas.

INSERT INTO equipo (id_equipo, nombre_equipo, categoria, descripcion, nombre_salon, estado_equipo) VALUES
( 1, 'Lab1-01', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 1', 'Operativo'),
( 2, 'Lab1-02', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 1', 'Operativo'),
( 3, 'Lab1-03', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 1', 'Operativo'),
( 4, 'Lab1-04', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 1', 'Operativo'),
( 5, 'Lab1-05', 'PC de escritorio', 'Monitor, teclado y mouse. Sin placa de red', 'Laboratorio 1', 'En reparación'),
( 6, 'Lab3-01', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 3', 'Operativo'),
( 7, 'Lab3-02', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 3', 'Operativo'),
( 8, 'Lab3-03', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 3', 'En reparación'),
( 9, 'Lab3-04', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 3', 'Operativo'),
(10, 'Lab3-05', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 3', 'Operativo'),
(11, 'Tal1-01', 'PC de escritorio', 'Monitor y teclado',                     'Taller 1',      'Operativo'),
(12, 'Tal1-02', 'PC de escritorio', 'Monitor y teclado',                     'Taller 1',      'Operativo'),
(13, 'Tal1-03', 'PC de escritorio', 'Monitor y teclado. Teclado trabado',    'Taller 1',      'Operativo'),
(14, 'Lab2-01', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 2', 'Operativo'),
(15, 'Lab2-02', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 2', 'Operativo'),
(16, 'Lab2-03', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 2', 'En reparación'),
(17, 'Lab2-04', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 2', 'Operativo'),
(18, 'Lab4-01', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 4', 'Operativo'),
(19, 'Lab4-02', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 4', 'Operativo'),
(20, 'Lab4-03', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 4', 'Operativo'),
(21, 'Lab4-04', 'PC de escritorio', 'Monitor, teclado, mouse y auriculares', 'Laboratorio 4', 'En trámite de baja'),
(22, 'Lab5-01', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 5', 'Operativo'),
(23, 'Lab5-02', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 5', 'Operativo'),
(24, 'Lab5-03', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 5', 'Operativo'),
(25, 'Lab5-04', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 5', 'En reparación'),
(26, 'Lab6-01', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 6', 'Operativo'),
(27, 'Lab6-02', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 6', 'Operativo'),
(28, 'Lab6-03', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 6', 'Operativo'),
(29, 'Lab6-04', 'PC de escritorio', 'Monitor, teclado y mouse',              'Laboratorio 6', 'Operativo'),
(30, 'Tal2-01', 'PC de escritorio', 'Monitor y teclado',                     'Taller 2',      'Operativo'),
(31, 'Tal2-02', 'PC de escritorio', 'Monitor y teclado',                     'Taller 2',      'Operativo'),
(32, 'Tal2-03', 'PC de escritorio', 'Monitor y teclado',                     'Taller 2',      'Operativo'),
(33, 'Tal2-04', 'PC de escritorio', 'Monitor y teclado',                     'Taller 2',      'Operativo'),
(34, 'Tal3-01', 'PC de escritorio', 'Monitor y teclado',                     'Taller 3',      'Operativo'),
(35, 'Tal3-02', 'PC de escritorio', 'Monitor y teclado',                     'Taller 3',      'Operativo'),
(36, 'Tal3-03', 'PC de escritorio', 'Monitor y teclado',                     'Taller 3',      'En reparación'),
(37, 'Tal3-04', 'PC de escritorio', 'Monitor y teclado',                     'Taller 3',      'Operativo'),
(38, 'Dep-01',  'PC de escritorio', 'Monitor y teclado. En reserva',         'Depósito',      'Operativo'),
(39, 'Dep-02',  'PC de escritorio', 'Sin disco. Fuera de servicio',          'Depósito',      'En trámite de baja'),
(40, 'Dep-03',  'PC de escritorio', 'Placa madre quemada. Sin reparación',   'Depósito',      'De baja');


-- tickets --------------------------------------------------------------

INSERT INTO ticket (id_ticket, fecha_hora_alta, ci_solicitante, id_equipo, tipo_de_defecto, descripcion, prioridad, estado_ticket, ci_responsable, diagnostico, solucion, fecha_hora_cierre) VALUES
(1, '2026-06-24 12:35:00', '48219073',  8, 'Sin imagen',
    'La PC no reconoce el proyector cuando se conecta el cable HDMI.',
    'Prioritario', 'Pendiente', NULL, NULL, NULL, NULL),

(2, '2026-07-01 15:45:00', '51730864', 13, 'Periférico dañado',
    'El teclado tiene varias teclas trabadas y no responden.',
    'Normal', 'Resuelto', '39482156',
    'Suciedad acumulada bajo las teclas por derrame de líquido.',
    'Se limpió el teclado y se reemplazaron dos teclas. Funcionando.',
    '2026-07-03 10:20:00'),

(3, '2026-07-10 19:45:00', '41073925',  5, 'Sin red',
    'El equipo no obtiene dirección IP y no accede a la red del instituto.',
    'Urgente', 'En proceso', '44905381',
    'La placa de red integrada no es detectada por el sistema operativo.',
    NULL, NULL),

(4, '2026-07-22 08:15:00', '37514962',  1, 'No enciende',
    'La máquina no da señal de encendido al presionar el botón.',
    'Urgente', 'Resuelto', '39482156',
    'Fuente de alimentación quemada.',
    'Se sustituyó la fuente por una de repuesto del depósito.',
    '2026-07-22 16:40:00'),

(5, '2026-08-05 11:10:00', '48219073',  2, 'Otros',
    'El botón de encendido del gabinete quedó hundido y hay que forzarlo para arrancar.',
    'Normal', 'En proceso', '44905381',
    'Botonera frontal del gabinete rota. Requiere repuesto externo.',
    NULL, NULL),

(6, '2026-08-14 14:00:00', '51730864',  6, 'Software',
    'No abre el entorno de desarrollo, muestra un error de licencia.',
    'Prioritario', 'Pendiente', NULL, NULL, NULL, NULL);


-- solicitudes ----------------------------------------------------------

INSERT INTO solicitud (id_solicitud, fecha_hora_alta, ci_solicitante, nombre_salon, descripcion, prioridad, estado_solicitud, ci_responsable, fecha_hora_cierre) VALUES
(1, '2026-06-01 09:30:00', '48219073', 'Laboratorio 1',
    'Instalar MySQL Workbench en todos los equipos antes de la clase del martes.',
    'Prioritario', 'Resuelta', '39482156', '2026-06-05 17:00:00'),

(2, '2026-06-02 10:15:00', '51730864', 'Taller 2',
    'Preparar el proyector y verificar el audio para la exposición de proyectos.',
    'Normal', 'Resuelta', '44905381', '2026-06-02 18:30:00'),

(3, '2026-07-03 08:45:00', '41073925', 'Laboratorio 3',
    'Configurar acceso a la red del instituto en los equipos nuevos.',
    'Urgente', 'En proceso', '39482156', NULL),

(4, '2026-07-28 16:20:00', '37514962', 'Depósito',
    'Inventariar los equipos recibidos en la última compra.',
    'Normal', 'Pendiente', NULL, NULL),

(5, '2026-08-11 13:05:00', '48219073', 'Laboratorio 5',
    'Instalar el simulador de redes para el módulo de conectividad.',
    'Prioritario', 'Pendiente', NULL, NULL);


-- uso de salas ---------------------------------------------------------

INSERT INTO uso_sala (id_uso, fecha, hora_inicio, hora_fin, nombre_salon, ci_solicitante, asignatura, grupo, turno) VALUES
(1, '2026-05-17', '18:30:00', '21:05:00', 'Laboratorio 3', '41073925', 'Sistemas Operativos', '1MI', 'Nocturno'),
(2, '2026-05-30', '13:00:00', '16:20:00', 'Taller 1',      '37514962', 'Electricidad',        '2ME', 'Vespertino'),
(3, '2026-07-10', '07:30:00', '11:00:00', 'Laboratorio 1', '46283015', 'Soporte IT',          '3MI', 'Matutino');


-- detalle de uso -------------------------------------------------------

INSERT INTO detalle_uso (id_uso, id_equipo, nombre_alumno) VALUES
(1,  6, 'Juan Pérez'),
(1,  7, 'María Rodríguez'),
(1,  9, 'Lucas Espósito'),
(1, 10, 'Valentina Suárez'),
(2, 11, 'Nicolás Ferreira'),
(2, 12, 'Camila Olivera'),
(2, 13, NULL),
(3,  1, 'Sofía Méndez'),
(3,  2, 'Matías Cabrera'),
(3,  3, 'Agustina Rossi'),
(3,  4, 'Bruno Silveira');
