-- Script para agregar campos de pago completo a la tabla estudianteprograma
-- Ejecutar este script en la base de datos antes de usar la nueva funcionalidad

USE sistemadeposgrado;

-- Verificar si las columnas ya existen antes de agregarlas
-- Agregar columna montoPagado (monto total pagado por el estudiante)
ALTER TABLE estudianteprograma
ADD COLUMN IF NOT EXISTS montoPagado DECIMAL(10,2) DEFAULT 0 AFTER costomatricula;

-- Agregar columna pagoCompleto (1 = pagó todo el programa, 0 = solo matrícula)
ALTER TABLE estudianteprograma
ADD COLUMN IF NOT EXISTS pagoCompleto TINYINT(1) DEFAULT 0 AFTER montoPagado;

-- Actualizar registros existentes
-- Los registros antiguos tendrán pagoCompleto = 0 (solo matrícula)
-- y montoPagado = costomatricula
UPDATE estudianteprograma
SET montoPagado = costomatricula,
    pagoCompleto = 0
WHERE montoPagado IS NULL OR montoPagado = 0;

-- Verificar los cambios
SELECT 'Columnas agregadas correctamente' AS Mensaje;
DESCRIBE estudianteprograma;
