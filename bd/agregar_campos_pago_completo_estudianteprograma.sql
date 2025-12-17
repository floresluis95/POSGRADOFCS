-- ============================================
-- Script para agregar campos de Pago Completo
-- Tabla: estudianteprograma
-- Fecha: 17 de diciembre de 2025
-- ============================================

USE `posgrado`;

-- Agregar campo montoPagado después de costomatricula
ALTER TABLE `estudianteprograma`
ADD COLUMN `montoPagado` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Monto total pagado por el estudiante' AFTER `costomatricula`;

-- Agregar campo pagoCompleto después de montoPagado
ALTER TABLE `estudianteprograma`
ADD COLUMN `pagoCompleto` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Pago parcial (solo matrícula), 1=Pago completo del programa' AFTER `montoPagado`;

-- Ver estructura actualizada
DESCRIBE `estudianteprograma`;

-- ============================================
-- NOTAS:
-- - montoPagado: Almacena el monto total pagado (matrícula o programa completo)
-- - pagoCompleto: 0 = Solo pagó matrícula, 1 = Pagó programa completo
-- ============================================
