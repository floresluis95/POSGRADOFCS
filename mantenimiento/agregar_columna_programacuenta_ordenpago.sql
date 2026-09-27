-- =====================================================
-- Agregar columna ProgramaCuentaID a la tabla ordenpago
-- PROPOSITO: en el Pre-Registro, el usuario elige a qué
-- cuenta del programa (tabla programacuenta) debe depositar
-- el estudiante, en vez de usar siempre la cuenta Principal
-- de tipo MATRICULA de forma automática.
-- =====================================================

ALTER TABLE ordenpago
ADD COLUMN ProgramaCuentaID int(11) NULL
COMMENT 'FK a programacuenta.ProgramaCuentaID: cuenta elegida para depositar esta orden de pago. NULL en órdenes antiguas (se usa la cuenta Principal del programa como respaldo).'
AFTER ProgramaID;

ALTER TABLE ordenpago
ADD CONSTRAINT ordenpago_ibfk_4 FOREIGN KEY (ProgramaCuentaID) REFERENCES programacuenta (ProgramaCuentaID) ON DELETE SET NULL;
