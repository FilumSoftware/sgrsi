-- SGRSI - Filum Software
-- Creación del esquema. MariaDB 10.x / MySQL 8.x, InnoDB, utf8mb4.
-- Las justificaciones de diseño están en la carpeta de documentación.

DROP DATABASE IF EXISTS sgrsi;

CREATE DATABASE sgrsi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE sgrsi;


-- usuario --------------------------------------------------------------

CREATE TABLE usuario (
    ci               CHAR(8)      NOT NULL,
    nombre_usuario   VARCHAR(60)  NOT NULL,
    email            VARCHAR(120) NOT NULL,
    contrasena       VARCHAR(255) NOT NULL,
    tipo_de_usuario  ENUM('Solicitante', 'Asistente', 'Coordinador')
                     NOT NULL DEFAULT 'Solicitante',
    estado_cuenta    ENUM('Activa', 'Inactiva')
                     NOT NULL DEFAULT 'Activa',

    CONSTRAINT pk_usuario       PRIMARY KEY (ci),
    CONSTRAINT uq_usuario_email UNIQUE (email),
    CONSTRAINT ck_usuario_ci    CHECK (ci REGEXP '^[0-9]{8}$'),
    CONSTRAINT ck_usuario_email CHECK (email LIKE '%_@_%._%')
) ENGINE = InnoDB;


-- salon ----------------------------------------------------------------

CREATE TABLE salon (
    nombre_salon VARCHAR(30)  NOT NULL,
    descripcion  VARCHAR(500) NULL,

    CONSTRAINT pk_salon PRIMARY KEY (nombre_salon)
) ENGINE = InnoDB;


-- equipo ---------------------------------------------------------------

CREATE TABLE equipo (
    id_equipo     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_equipo VARCHAR(60)  NOT NULL,
    categoria     ENUM('PC de escritorio', 'Laptop', 'Proyector',
                       'Impresora', 'Otro')
                  NOT NULL DEFAULT 'PC de escritorio',
    descripcion   VARCHAR(500) NULL,
    nombre_salon  VARCHAR(30)  NOT NULL,
    estado_equipo ENUM('Operativo', 'En reparación', 'Derivado',
                       'En trámite de baja', 'De baja')
                  NOT NULL DEFAULT 'Operativo',

    CONSTRAINT pk_equipo       PRIMARY KEY (id_equipo),
    CONSTRAINT fk_equipo_salon FOREIGN KEY (nombre_salon)
        REFERENCES salon (nombre_salon)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE INDEX idx_equipo_estado ON equipo (estado_equipo);


-- intervencion ---------------------------------------------------------

CREATE TABLE intervencion (
    id_intervencion   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_equipo         INT UNSIGNED NOT NULL,
    fecha             DATE         NOT NULL,
    ci_responsable    CHAR(8)      NOT NULL,
    tipo_intervencion ENUM('Diagnóstico', 'Reparación', 'Derivación',
                           'Retorno', 'Baja') NOT NULL,
    descripcion       VARCHAR(500) NOT NULL,

    CONSTRAINT pk_intervencion             PRIMARY KEY (id_intervencion),
    CONSTRAINT fk_intervencion_equipo      FOREIGN KEY (id_equipo)
        REFERENCES equipo (id_equipo)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_intervencion_responsable FOREIGN KEY (ci_responsable)
        REFERENCES usuario (ci)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB;

CREATE INDEX idx_intervencion_fecha ON intervencion (fecha);


-- ticket ---------------------------------------------------------------
-- No guarda el salón: lo determina el equipo, y duplicarlo rompería 3FN.

CREATE TABLE ticket (
    id_ticket         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fecha_hora_alta   DATETIME     NOT NULL,
    ci_solicitante    CHAR(8)      NOT NULL,
    id_equipo         INT UNSIGNED NOT NULL,
    tipo_de_defecto   ENUM('No enciende', 'Sin imagen', 'Sin red',
                           'Sin audio', 'Periférico dañado', 'Software',
                           'Otros') NOT NULL,
    descripcion       VARCHAR(500) NULL,
    prioridad         ENUM('Urgente', 'Prioritario', 'Normal')
                      NOT NULL DEFAULT 'Normal',
    estado_ticket     ENUM('Pendiente', 'En proceso', 'Resuelto')
                      NOT NULL DEFAULT 'Pendiente',
    ci_responsable    CHAR(8)      NULL,
    diagnostico       VARCHAR(500) NULL,
    solucion          VARCHAR(500) NULL,
    fecha_hora_cierre DATETIME     NULL,

    CONSTRAINT pk_ticket             PRIMARY KEY (id_ticket),
    CONSTRAINT fk_ticket_solicitante FOREIGN KEY (ci_solicitante)
        REFERENCES usuario (ci)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    -- RESTRICT en ambas acciones y no CASCADE: el motor rechaza un CHECK
    -- sobre una columna con acción referencial distinta de RESTRICT.
    CONSTRAINT fk_ticket_responsable FOREIGN KEY (ci_responsable)
        REFERENCES usuario (ci)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_ticket_equipo      FOREIGN KEY (id_equipo)
        REFERENCES equipo (id_equipo)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT ck_ticket_cierre CHECK (
        fecha_hora_cierre IS NULL OR fecha_hora_cierre >= fecha_hora_alta
    ),
    CONSTRAINT ck_ticket_resuelto CHECK (
        estado_ticket <> 'Resuelto'
        OR (ci_responsable IS NOT NULL AND fecha_hora_cierre IS NOT NULL)
    )
) ENGINE = InnoDB;

CREATE INDEX idx_ticket_estado ON ticket (estado_ticket);
CREATE INDEX idx_ticket_alta   ON ticket (fecha_hora_alta);


-- solicitud ------------------------------------------------------------
-- Acá el salón sí es dato propio: la solicitud es sobre el espacio, no
-- sobre un equipo.

CREATE TABLE solicitud (
    id_solicitud      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fecha_hora_alta   DATETIME     NOT NULL,
    ci_solicitante    CHAR(8)      NOT NULL,
    nombre_salon      VARCHAR(30)  NOT NULL,
    descripcion       VARCHAR(500) NOT NULL,
    prioridad         ENUM('Urgente', 'Prioritario', 'Normal')
                      NOT NULL DEFAULT 'Normal',
    estado_solicitud  ENUM('Pendiente', 'En proceso', 'Resuelta')
                      NOT NULL DEFAULT 'Pendiente',
    ci_responsable    CHAR(8)      NULL,
    fecha_hora_cierre DATETIME     NULL,

    CONSTRAINT pk_solicitud             PRIMARY KEY (id_solicitud),
    CONSTRAINT fk_solicitud_solicitante FOREIGN KEY (ci_solicitante)
        REFERENCES usuario (ci)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitud_responsable FOREIGN KEY (ci_responsable)
        REFERENCES usuario (ci)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_solicitud_salon       FOREIGN KEY (nombre_salon)
        REFERENCES salon (nombre_salon)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT ck_solicitud_cierre CHECK (
        fecha_hora_cierre IS NULL OR fecha_hora_cierre >= fecha_hora_alta
    ),
    CONSTRAINT ck_solicitud_resuelta CHECK (
        estado_solicitud <> 'Resuelta'
        OR (ci_responsable IS NOT NULL AND fecha_hora_cierre IS NOT NULL)
    )
) ENGINE = InnoDB;

CREATE INDEX idx_solicitud_estado ON solicitud (estado_solicitud);
CREATE INDEX idx_solicitud_alta   ON solicitud (fecha_hora_alta);


-- prestamo -------------------------------------------------------------
-- fecha_hora_devolucion nula = préstamo vigente.
-- Se registra el tipo de documento dejado en garantía, nunca el número.

CREATE TABLE prestamo (
    id_prestamo               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_equipo                 INT UNSIGNED NOT NULL,
    ci_solicitante            CHAR(8)      NOT NULL,
    ci_responsable            CHAR(8)      NOT NULL,
    fecha_hora_entrega        DATETIME     NOT NULL,
    fecha_devolucion_prevista DATE         NOT NULL,
    documento_garantia        ENUM('Cédula', 'Carné estudiantil', 'Otro')
                              NOT NULL,
    fecha_hora_devolucion     DATETIME     NULL,

    CONSTRAINT pk_prestamo             PRIMARY KEY (id_prestamo),
    CONSTRAINT fk_prestamo_equipo      FOREIGN KEY (id_equipo)
        REFERENCES equipo (id_equipo)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_solicitante FOREIGN KEY (ci_solicitante)
        REFERENCES usuario (ci)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_responsable FOREIGN KEY (ci_responsable)
        REFERENCES usuario (ci)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT ck_prestamo_devolucion CHECK (
        fecha_hora_devolucion IS NULL
        OR fecha_hora_devolucion >= fecha_hora_entrega
    )
) ENGINE = InnoDB;

CREATE INDEX idx_prestamo_vigente ON prestamo (fecha_hora_devolucion);


-- uso_sala -------------------------------------------------------------

CREATE TABLE uso_sala (
    id_uso         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fecha          DATE         NOT NULL,
    hora_inicio    TIME         NOT NULL,
    hora_fin       TIME         NOT NULL,
    nombre_salon   VARCHAR(30)  NOT NULL,
    ci_solicitante CHAR(8)      NOT NULL,
    asignatura     VARCHAR(60)  NOT NULL,
    grupo          VARCHAR(10)  NOT NULL,
    turno          ENUM('Matutino', 'Vespertino', 'Nocturno') NOT NULL,

    CONSTRAINT pk_uso_sala             PRIMARY KEY (id_uso),
    CONSTRAINT fk_uso_sala_salon       FOREIGN KEY (nombre_salon)
        REFERENCES salon (nombre_salon)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_uso_sala_solicitante FOREIGN KEY (ci_solicitante)
        REFERENCES usuario (ci)
        ON UPDATE CASCADE ON DELETE RESTRICT,

    CONSTRAINT ck_uso_sala_horario CHECK (hora_fin > hora_inicio)
) ENGINE = InnoDB;

CREATE INDEX idx_uso_sala_fecha ON uso_sala (fecha);


-- detalle_uso ----------------------------------------------------------
-- Entidad débil de uso_sala. Los alumnos no son usuarios del sistema, por
-- eso el nombre es texto libre y admite nulo.

CREATE TABLE detalle_uso (
    id_uso        INT UNSIGNED NOT NULL,
    id_equipo     INT UNSIGNED NOT NULL,
    nombre_alumno VARCHAR(60)  NULL,

    CONSTRAINT pk_detalle_uso        PRIMARY KEY (id_uso, id_equipo),
    CONSTRAINT fk_detalle_uso_uso    FOREIGN KEY (id_uso)
        REFERENCES uso_sala (id_uso)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_uso_equipo FOREIGN KEY (id_equipo)
        REFERENCES equipo (id_equipo)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB;
