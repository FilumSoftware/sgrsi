-- Ejecutar una sola vez sobre una base SGRSI existente.
-- Aplicar después de migracion_detalle_defecto.sql.
USE sgrsi;
SET NAMES utf8mb4;

CREATE TABLE tipo_incidencia (
    nombre VARCHAR(80) NOT NULL,
    estado ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    CONSTRAINT pk_tipo_incidencia PRIMARY KEY (nombre)
) ENGINE = InnoDB;

INSERT INTO tipo_incidencia (nombre, estado) VALUES
('No enciende', 'Activo'),
('Sin imagen', 'Activo'),
('Sin red', 'Activo'),
('Sin audio', 'Activo'),
('Periférico dañado', 'Activo'),
('Software', 'Activo'),
('Otros', 'Activo');

ALTER TABLE ticket MODIFY tipo_de_defecto VARCHAR(80) NOT NULL;
ALTER TABLE ticket ADD CONSTRAINT fk_ticket_tipo
    FOREIGN KEY (tipo_de_defecto) REFERENCES tipo_incidencia (nombre)
    ON UPDATE CASCADE ON DELETE RESTRICT;
