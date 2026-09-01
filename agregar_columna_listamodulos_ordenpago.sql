-- =====================================================
-- Agregar columna ListaModulos a la tabla ordenpago
-- PROPOSITO: soportar ordenes de pago de modulos generadas
-- ANTES de que el modulo este pagado (a diferencia de
-- ListaPagosModulo, que referencia pagomodulo.Idpagomodulo
-- de modulos YA pagados).
-- =====================================================

ALTER TABLE ordenpago
ADD COLUMN ListaModulos TEXT NULL
COMMENT 'Lista de Idmodulo (modulos.Idmodulo) separados por coma, para ordenes de pago generadas ANTES de pagar (pendientes). Distinto de ListaPagosModulo que referencia pagomodulo.Idpagomodulo (modulos ya pagados).'
AFTER ListaPagosModulo;
