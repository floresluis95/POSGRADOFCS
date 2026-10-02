-- Requisitos automáticos y Ficha de inscripción (2026-10-02)
-- Ejecutar DESPUÉS de crear_requisitos.sql. Se puede correr más de una vez.
--
-- 1) `requisito.Automatico`: el requisito se marca como entregado solo, al
--    validar el voucher de matrícula (con la fecha de inscripción). Son los
--    documentos que el propio sistema genera en ese momento.
-- 2) Nuevo requisito "Ficha de inscripción" (automático) y el "Documento privado
--    de compromiso" pasa a ser automático.
-- 3) A los matriculados que ya existen (su voucher ya se validó) se les marcan
--    esos requisitos como entregados, sin tocar los que ya tenían registro.

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'requisito' AND COLUMN_NAME = 'Automatico');
SET @sql := IF(@existe = 0,
    'ALTER TABLE requisito ADD COLUMN Automatico TINYINT(1) NOT NULL DEFAULT 0 AFTER Obligatorio',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO requisito (Nombre, Obligatorio, Automatico, Orden)
SELECT 'Ficha de inscripción', 1, 1, 8
WHERE NOT EXISTS (SELECT 1 FROM requisito WHERE Nombre = 'Ficha de inscripción');

UPDATE requisito SET Automatico = 1
WHERE Nombre IN ('Ficha de inscripción', 'Documento privado de compromiso');

INSERT IGNORE INTO requisitoinscripcion (idInscripcion, IdRequisito, Entregado, FechaEntrega, ResponsableRegistro)
SELECT ep.idInscripcion, r.IdRequisito, 1, ep.FechaInscripcion, 'Sistema (validación de matrícula)'
FROM estudianteprograma ep
CROSS JOIN requisito r
WHERE r.Automatico = 1 AND r.Estado = 1;
