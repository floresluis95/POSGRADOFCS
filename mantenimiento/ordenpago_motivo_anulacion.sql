-- Motivo de la anulación de una orden de pago (cuotas y clínicas desde Matriculados).
-- Se guarda quién la anuló, cuándo y por qué; la orden sigue en la tabla como ANULADO.
-- Las anuladas antes de esta migración quedan con estos campos en NULL.

ALTER TABLE ordenpago
    ADD COLUMN MotivoAnulacion VARCHAR(255) NULL COMMENT 'Motivo escrito al anular la orden',
    ADD COLUMN FechaAnulacion DATETIME NULL COMMENT 'Cuándo se anuló',
    ADD COLUMN ResponsableAnulacion VARCHAR(200) NULL COMMENT 'Quién la anuló';
