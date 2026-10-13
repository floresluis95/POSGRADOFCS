-- Plan Grupal: el grupo se arma en Pre-Registro.
-- Cada integrante tiene su propia orden de matrícula (con sus propios descuentos) y
-- todas las del grupo comparten el mismo código. Al validar el voucher, el código pasa
-- a pagoprograma.CodigoGrupo (igual que el tipo de plan y el % del programa).

ALTER TABLE ordenpago
    ADD COLUMN PlanPropuestoCodigoGrupo VARCHAR(30) NULL AFTER PlanPropuestoPorcentaje,
    ADD KEY idx_plan_codigo_grupo (PlanPropuestoCodigoGrupo);
