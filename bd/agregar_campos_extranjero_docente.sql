-- ============================================
-- Script para agregar campos de Docente Extranjero
-- Fecha: 17 de diciembre de 2025
-- ============================================

USE `posgrado`;

-- Agregar campos para docentes extranjeros
ALTER TABLE `docente`
ADD COLUMN `EsExtranjero` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Nacional, 1=Extranjero' AFTER `Estado`,
ADD COLUMN `Pais` VARCHAR(100) NULL DEFAULT NULL COMMENT 'País de origen si es extranjero' AFTER `EsExtranjero`,
ADD COLUMN `Region` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Región/Departamento del país' AFTER `Pais`;

-- Ver estructura actualizada
DESCRIBE `docente`;

-- ============================================
-- NOTAS:
-- - EsExtranjero: 0 = Boliviano (nacional), 1 = Extranjero
-- - Pais: Se llena solo si EsExtranjero = 1
-- - Region: Se llena solo si EsExtranjero = 1, depende del país
-- ============================================
