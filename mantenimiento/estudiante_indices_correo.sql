-- Índices repetidos en estudiante.Correo (2026-10-09)
-- La columna Correo tenía TRES índices únicos iguales (EmailPersonal, Correo y Correo_2):
-- hacen lo mismo y solo ocupan espacio y hacen más lento cada INSERT/UPDATE.
-- Se deja uno solo (`Correo`): el correo sigue siendo único entre estudiantes.
-- Ningún código usa estos índices por nombre.

-- 1) Ver los índices actuales (deben aparecer EmailPersonal, Correo y Correo_2 sobre Correo)
SHOW INDEX FROM estudiante WHERE Column_name = 'Correo';

-- 2) Quitar los dos repetidos
ALTER TABLE estudiante
    DROP INDEX EmailPersonal,
    DROP INDEX Correo_2;
