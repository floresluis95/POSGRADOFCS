-- Agregar campos faltantes a la tabla ordenpago
-- Ejecutar en phpMyAdmin

USE posgradofcs;

-- Agregar campo CostoMatricula
ALTER TABLE ordenpago
ADD COLUMN CostoMatricula DECIMAL(10,2) NULL AFTER PagoCompleto;

-- Agregar campo Firma
ALTER TABLE ordenpago
ADD COLUMN Firma VARCHAR(200) NULL AFTER NitCiFactura;

-- Verificar que se agregaron correctamente
DESCRIBE ordenpago;

-- Ver los campos nuevos
SELECT
    'CostoMatricula' as Campo,
    COUNT(*) as Existe
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'posgradofcs'
  AND TABLE_NAME = 'ordenpago'
  AND COLUMN_NAME = 'CostoMatricula'

UNION

SELECT
    'Firma' as Campo,
    COUNT(*) as Existe
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'posgradofcs'
  AND TABLE_NAME = 'ordenpago'
  AND COLUMN_NAME = 'Firma';
