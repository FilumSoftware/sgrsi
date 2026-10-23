-- Ejecutar una sola vez sobre una base SGRSI existente.
-- Conserva los tickets y agrega el estado de cancelación lógica.
USE sgrsi;

ALTER TABLE ticket
    MODIFY estado_ticket ENUM('Pendiente', 'En proceso', 'Resuelto', 'Cancelado')
    NOT NULL DEFAULT 'Pendiente';
