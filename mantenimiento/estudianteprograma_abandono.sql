-- Migración 30: datos del abandono de una inscripción (se marca en Matriculados).
-- IdModuloAbandono = primer módulo que el estudiante ya NO cursa: sigue en las planillas
-- de los módulos anteriores (y en cualquiera donde ya tenga nota) y sale de las demás.
-- NULL en un abandono antiguo = abandono desde el inicio (no aparece en ninguna planilla
-- salvo donde ya tenga nota).
ALTER TABLE estudianteprograma
    ADD COLUMN IdModuloAbandono INT NULL DEFAULT NULL AFTER Estado,
    ADD COLUMN MotivoAbandono VARCHAR(255) NULL DEFAULT NULL AFTER IdModuloAbandono,
    ADD COLUMN FechaAbandono DATETIME NULL DEFAULT NULL AFTER MotivoAbandono,
    ADD COLUMN ResponsableAbandono VARCHAR(150) NULL DEFAULT NULL AFTER FechaAbandono;
