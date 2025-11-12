-- Script para mejorar el campo Estado en la tabla programa
-- El estado ahora será un VARCHAR para permitir: 'ACTIVO', 'EN CURSO', 'CONCLUIDO', 'INACTIVO'

-- Primero respaldamos los valores actuales
-- Estado 1 = ACTIVO, Estado 0 = INACTIVO

-- Agregar nueva columna temporal
ALTER TABLE `programa`
ADD COLUMN `EstadoNuevo` VARCHAR(20) NULL AFTER `Estado`;

-- Migrar datos: 1 -> 'ACTIVO', 0 -> 'INACTIVO'
UPDATE `programa` SET `EstadoNuevo` =
    CASE
        WHEN `Estado` = 1 THEN 'ACTIVO'
        WHEN `Estado` = 0 THEN 'INACTIVO'
        ELSE 'ACTIVO'
    END;

-- Eliminar columna antigua
ALTER TABLE `programa` DROP COLUMN `Estado`;

-- Renombrar columna nueva
ALTER TABLE `programa` CHANGE `EstadoNuevo` `Estado` VARCHAR(20) NOT NULL DEFAULT 'ACTIVO';

-- Verificar
-- SELECT ProgramaID, NombrePrograma, Estado FROM programa;
