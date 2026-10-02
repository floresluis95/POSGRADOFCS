-- Título del trabajo de grado en un solo lugar (2026-10-02)
--
-- 1) `cronogramadefensa.EstudianteID`: la defensa queda ligada al estudiante,
--    así la lista de defensas muestra el título guardado en `trabajogrado`
--    (EstudianteID + ProgramaID) y se actualiza solo si el título cambia.
--    Las defensas antiguas (sin estudiante ligado) siguen mostrando su Detalle.
-- 2) Los títulos ya registrados pasan a MAYÚSCULAS, como se guardan desde ahora.
-- Se puede correr más de una vez.

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cronogramadefensa' AND COLUMN_NAME = 'EstudianteID');
SET @sql := IF(@existe = 0,
    'ALTER TABLE cronogramadefensa ADD COLUMN EstudianteID INT(11) NULL AFTER IdDefensa, ADD INDEX idx_defensa_estudiante (EstudianteID)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE trabajogrado SET TituloTrabajo = UPPER(TRIM(TituloTrabajo)) WHERE TituloTrabajo <> UPPER(TRIM(TituloTrabajo));

-- 3) `trabajogrado.HoraEntrega`: hora de entrega del informe final (la fecha
--    sigue en FechaEntrega). Se imprime en el certificado de aprobación.
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'trabajogrado' AND COLUMN_NAME = 'HoraEntrega');
SET @sql := IF(@existe = 0,
    'ALTER TABLE trabajogrado ADD COLUMN HoraEntrega TIME NULL AFTER FechaEntrega',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
