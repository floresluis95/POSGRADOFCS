-- =============================================================================
-- Código del programa con numeración propia por sede y grado (2026-11-14)
--
-- Antes el número del código era el total de programas del sistema, así que
-- especialidades, maestrías y diplomados compartían la numeración
-- (ORU-ESP-1-2024, ORU-MAE-2-…, ORU-MAE-3-…). Ahora cada sede y grado lleva la suya:
-- ORU-ESP-1, ORU-ESP-2…, ORU-MAE-1, ORU-MAE-2…, ORU-DIP-1…, LPZ-ESP-1…
--
-- Este script vuelve a numerar los programas que ya existen: dentro de cada sede y
-- grado, por fecha de inicio (el más antiguo es el 1). El año es el de la fecha de
-- inicio. Solo cambia programa.Codigo (ninguna otra tabla guarda el código).
-- Los programas nuevos ya salen bien desde el sistema (ProgramasModelos::GenerarCodigo).
--
-- OJO: los documentos ya impresos conservan el código anterior.
-- Ejecutar con respaldo previo. Se puede volver a ejecutar sin problema (da lo mismo).
-- =============================================================================

UPDATE programa p
JOIN (
    SELECT ProgramaID,
           CONCAT(Prefijo, '-',
                  ROW_NUMBER() OVER (PARTITION BY Prefijo ORDER BY FechaInicio, ProgramaID),
                  '-', YEAR(FechaInicio)) AS CodigoNuevo
    FROM (
        SELECT ProgramaID, FechaInicio,
               CONCAT(
                   CASE UPPER(TRIM(Sede))
                       WHEN 'ORURO' THEN 'ORU'
                       WHEN 'LA PAZ' THEN 'LPZ'
                       WHEN 'COCHABAMBA' THEN 'CBB'
                       WHEN 'SANTA CRUZ' THEN 'SCZ'
                       ELSE LEFT(REPLACE(UPPER(TRIM(Sede)), ' ', ''), 3)
                   END,
                   '-',
                   CASE UPPER(TRIM(GradoAcademico))
                       WHEN 'DIPLOMADO' THEN 'DIP'
                       WHEN 'ESPECIALIDAD' THEN 'ESP'
                       WHEN 'MAESTRIA' THEN 'MAE'
                       WHEN 'MAESTRÍA' THEN 'MAE'
                       WHEN 'DOCTORADO' THEN 'DOC'
                       ELSE 'OTR'
                   END
               ) AS Prefijo
        FROM programa
    ) base
) x ON x.ProgramaID = p.ProgramaID
SET p.Codigo = x.CodigoNuevo;

-- Revisar el resultado
SELECT ProgramaID, Sede, GradoAcademico, FechaInicio, Codigo FROM programa ORDER BY Codigo;
