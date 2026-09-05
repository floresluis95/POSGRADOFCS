-- =====================================================
-- MIGRACIÓN: Unificar vocabulario de "tipo de pago" del programa
-- =====================================================
-- Antes existían dos valores distintos para la misma idea de plan "al
-- contado con descuento": `CONTADO` (usado en Pre-Registro, tabla
-- `ordenpago.PlanPropuestoTipo`) y `DESCUENTO` (usado en `pagoprograma.TipoPlan`).
-- Esa diferencia de vocabulario causaba traducciones manuales frágiles y,
-- en el caso de "Plan Grupal", una conversión silenciosa a "Plan Regular"
-- que el personal no podía corregir después (no existía forma de editar
-- un plan ya creado). Ver commit de rediseño del flujo de tipo de pago.
--
-- Esta migración:
--   1. Asegura que `ordenpago` tenga las columnas de la propuesta de plan
--      (PlanPropuestoTipo, PlanPropuestoPorcentaje) con ancho suficiente
--      para 'DESCUENTO' (9 caracteres).
--   2. Normaliza datos históricos: cualquier fila con 'CONTADO' pasa a 'DESCUENTO'.
--
-- Es segura de ejecutar más de una vez (columnas con IF NOT EXISTS / MODIFY).
-- =====================================================

ALTER TABLE `ordenpago`
  ADD COLUMN IF NOT EXISTS `PlanPropuestoTipo` VARCHAR(20) NULL
    COMMENT 'Plan de pago del programa propuesto en Pre-Registro: REGULAR, DESCUENTO o GRUPAL',
  ADD COLUMN IF NOT EXISTS `PlanPropuestoPorcentaje` DECIMAL(5,2) NOT NULL DEFAULT 0.00
    COMMENT '% de descuento propuesto junto con PlanPropuestoTipo';

-- Por si la columna ya existía con un ancho menor (ej. VARCHAR(10), suficiente
-- para 'REGULAR'/'CONTADO'/'GRUPAL' pero no para 'DESCUENTO').
ALTER TABLE `ordenpago`
  MODIFY COLUMN `PlanPropuestoTipo` VARCHAR(20) NULL
    COMMENT 'Plan de pago del programa propuesto en Pre-Registro: REGULAR, DESCUENTO o GRUPAL';

-- Normalizar datos históricos al nuevo vocabulario único.
UPDATE `ordenpago` SET `PlanPropuestoTipo` = 'DESCUENTO' WHERE `PlanPropuestoTipo` = 'CONTADO';

-- =====================================================
-- FIN DE LA MIGRACIÓN
-- =====================================================
