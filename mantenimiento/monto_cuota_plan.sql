-- Monto de cada cuota del programa, pactado en Pre-Registro.
-- Por defecto el total del programa (con descuento) entre el N° de cuotas,
-- redondeado hacia arriba a decenas cuando no sale exacto (30000 / 11 = 2727,27
-- -> 2730); la última cuota es la que ajusta (30000 - 10 x 2730 = 2700).
-- Se puede cambiar a mano en Pre-Registro. Al validar el voucher pasa a
-- pagoprograma.MontoCuota (igual que el N° de cuotas). NULL = sin monto pactado
-- (planes anteriores): la cuota se sigue sugiriendo como saldo / cuotas restantes.

ALTER TABLE ordenpago
    ADD COLUMN PlanPropuestoMontoCuota DECIMAL(10,2) NULL AFTER PlanPropuestoNumeroCuotas;

ALTER TABLE pagoprograma
    ADD COLUMN MontoCuota DECIMAL(10,2) NULL AFTER NumeroCuotas;

-- Total del programa escrito a mano en Pre-Registro (en Bs., en vez del % de
-- descuento). El % equivalente se guarda redondeado en PlanPropuestoPorcentaje;
-- este es el monto exacto que pasa a pagoprograma.MontoTotalPagar. NULL = se
-- calcula con el % (como antes).
ALTER TABLE ordenpago
    ADD COLUMN PlanPropuestoMontoPrograma DECIMAL(10,2) NULL AFTER PlanPropuestoPorcentaje;
