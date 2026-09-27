-- Vincula cada cuota pagada (pagomodulo) con la orden de pago (ordenpago) que
-- originó su solicitud, para poder mostrar al estudiante la fecha en que se
-- generó la solicitud de pago, además de la fecha en que se canceló.
-- Las cuotas ya registradas antes de esta columna quedan con IdOrdenPago NULL
-- (no se puede reconstruir el vínculo de forma confiable de forma retroactiva).
ALTER TABLE `pagomodulo`
    ADD COLUMN `IdOrdenPago` int(11) DEFAULT NULL AFTER `idinscripcion`,
    ADD KEY `idx_pagomodulo_ordenpago` (`IdOrdenPago`),
    ADD CONSTRAINT `fk_pagomodulo_ordenpago` FOREIGN KEY (`IdOrdenPago`) REFERENCES `ordenpago` (`IdOrdenPago`) ON DELETE SET NULL ON UPDATE CASCADE;
