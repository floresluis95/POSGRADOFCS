-- Orden de pago con voucher extraviado (cuotas y clínicas).
-- El estudiante pagó pero perdió el voucher y aún no se tiene su código: la orden pasa a
-- EXTRAVIADO (deja de contar como pendiente, así se puede generar la orden siguiente) y
-- cuando Tesoro da el código se registra el voucher de esa misma orden (queda CONFIRMADO).
-- Mientras tanto ese monto NO cuenta como cobrado.

ALTER TABLE ordenpago
    MODIFY Estado ENUM('PENDIENTE', 'CONFIRMADO', 'ANULADO', 'VENCIDO', 'EXTRAVIADO') NOT NULL DEFAULT 'PENDIENTE',
    ADD COLUMN MotivoExtraviado VARCHAR(255) NULL COMMENT 'Motivo al marcar el voucher como extraviado',
    ADD COLUMN FechaExtraviado DATETIME NULL COMMENT 'Cuándo se marcó como extraviado',
    ADD COLUMN ResponsableExtraviado VARCHAR(200) NULL COMMENT 'Quién lo marcó';
