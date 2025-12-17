-- =====================================================
-- AGREGAR CAMPOS DE DESCUENTO A ESTUDIANTEPROGRAMA
-- =====================================================
-- Fecha: 17 de diciembre de 2025
-- Descripción: Agrega campos para registrar descuentos
--              aplicados al pago completo del programa
-- =====================================================

USE postgradofcs;

-- Verificar si los campos ya existen antes de agregarlos
SET @exist_porcentajeDescuento = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'postgradofcs'
    AND TABLE_NAME = 'estudianteprograma'
    AND COLUMN_NAME = 'porcentajeDescuento'
);

SET @exist_montoDescuento = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'postgradofcs'
    AND TABLE_NAME = 'estudianteprograma'
    AND COLUMN_NAME = 'montoDescuento'
);

-- Agregar campo porcentajeDescuento si no existe
SET @sql_porcentajeDescuento = IF(@exist_porcentajeDescuento = 0,
    'ALTER TABLE `estudianteprograma`
     ADD COLUMN `porcentajeDescuento` DECIMAL(5,2) NOT NULL DEFAULT 0.00
     COMMENT "Porcentaje de descuento aplicado (0-100)"
     AFTER `pagoCompleto`',
    'SELECT "El campo porcentajeDescuento ya existe" AS Mensaje'
);

PREPARE stmt FROM @sql_porcentajeDescuento;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar campo montoDescuento si no existe
SET @sql_montoDescuento = IF(@exist_montoDescuento = 0,
    'ALTER TABLE `estudianteprograma`
     ADD COLUMN `montoDescuento` DECIMAL(10,2) NOT NULL DEFAULT 0.00
     COMMENT "Monto total del descuento en bolivianos"
     AFTER `porcentajeDescuento`',
    'SELECT "El campo montoDescuento ya existe" AS Mensaje'
);

PREPARE stmt FROM @sql_montoDescuento;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mostrar estructura actualizada
DESCRIBE estudianteprograma;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
