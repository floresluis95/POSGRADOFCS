-- Permitir que la columna 'foto' acepte valores NULL
-- Ya que la imagen es opcional en el formulario

ALTER TABLE estudianteprograma
MODIFY COLUMN foto LONGBLOB NULL;
