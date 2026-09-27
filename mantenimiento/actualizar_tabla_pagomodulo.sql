-- Actualizar tabla pagomodulo existente
-- Agregar columna IdModulo para relacionar con la tabla modulos

-- 1. Agregar columna IdModulo (nullable inicialmente)
ALTER TABLE `pagomodulo`
ADD COLUMN `IdModulo` int(11) NULL AFTER `idinscripcion`,
ADD INDEX `idx_modulo` (`IdModulo`);

-- 2. Agregar Foreign Key a modulos
ALTER TABLE `pagomodulo`
ADD CONSTRAINT `fk_pagomodulo_modulo`
FOREIGN KEY (`IdModulo`) REFERENCES `modulos`(`Idmodulo`)
ON DELETE SET NULL
ON UPDATE CASCADE;

-- 3. Opcional: Si quieres actualizar los registros existentes
-- para relacionarlos con la tabla modulos basándote en el nombre:
/*
UPDATE pagomodulo pm
INNER JOIN modulos m ON pm.nmodulo = m.nombremodulo
SET pm.IdModulo = m.Idmodulo
WHERE pm.IdModulo IS NULL;
*/

-- 4. Agregar índice compuesto para evitar duplicados
ALTER TABLE `pagomodulo`
ADD UNIQUE KEY `unique_inscripcion_modulo` (`idinscripcion`, `IdModulo`);

-- Verificar estructura
DESCRIBE pagomodulo;
