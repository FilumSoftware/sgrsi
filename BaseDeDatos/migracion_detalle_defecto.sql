-- Ejecutar una sola vez sobre una base SGRSI existente.
-- Preserva los tickets y separa el texto libre de la categoría "Otros".
USE sgrsi;

ALTER TABLE ticket ADD COLUMN detalle_defecto VARCHAR(120) NULL AFTER tipo_de_defecto;

UPDATE ticket
   SET detalle_defecto = LEFT(COALESCE(NULLIF(TRIM(descripcion), ''), 'Falla no catalogada'), 120)
 WHERE tipo_de_defecto = 'Otros';
