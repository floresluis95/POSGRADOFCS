-- Script para agregar el campo CostoMatricula a la tabla programa
-- Ejecutar en phpMyAdmin o MySQL

ALTER TABLE `programa`
ADD COLUMN `CostoMatricula` DECIMAL(10,2) NULL DEFAULT NULL
AFTER `Costo`;

-- Verificar que se agregó correctamente
-- DESCRIBE programa;
