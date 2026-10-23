USE sgrsi;

CREATE TABLE prestamo (
    id_prestamo               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_equipo                 INT UNSIGNED NOT NULL,
    ci_solicitante            CHAR(8) NOT NULL,
    ci_entrega                CHAR(8) NOT NULL,
    fecha_hora_prestamo       DATETIME NOT NULL,
    fecha_devolucion_prevista DATE NULL,
    fecha_hora_devolucion     DATETIME NULL,
    ci_recepcion              CHAR(8) NULL,
    fecha_hora_anulacion      DATETIME NULL,
    ci_anulacion              CHAR(8) NULL,
    observaciones             VARCHAR(500) NULL,

    CONSTRAINT pk_prestamo PRIMARY KEY (id_prestamo),
    CONSTRAINT fk_prestamo_equipo FOREIGN KEY (id_equipo)
        REFERENCES equipo (id_equipo) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_solicitante FOREIGN KEY (ci_solicitante)
        REFERENCES usuario (ci) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_entrega FOREIGN KEY (ci_entrega)
        REFERENCES usuario (ci) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_recepcion FOREIGN KEY (ci_recepcion)
        REFERENCES usuario (ci) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_prestamo_anulacion FOREIGN KEY (ci_anulacion)
        REFERENCES usuario (ci) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT ck_prestamo_fechas CHECK (
        (fecha_hora_devolucion IS NULL OR fecha_hora_devolucion >= fecha_hora_prestamo)
        AND (fecha_hora_anulacion IS NULL OR fecha_hora_anulacion >= fecha_hora_prestamo)
    ),
    CONSTRAINT ck_prestamo_cierre CHECK (
        (fecha_hora_devolucion IS NULL) = (ci_recepcion IS NULL)
        AND (fecha_hora_anulacion IS NULL) = (ci_anulacion IS NULL)
        AND NOT (fecha_hora_devolucion IS NOT NULL AND fecha_hora_anulacion IS NOT NULL)
    )
) ENGINE = InnoDB;

CREATE INDEX idx_prestamo_equipo_activo
    ON prestamo (id_equipo, fecha_hora_devolucion, fecha_hora_anulacion);
CREATE INDEX idx_prestamo_solicitante ON prestamo (ci_solicitante);
