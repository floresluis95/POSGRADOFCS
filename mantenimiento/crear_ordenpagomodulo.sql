-- Módulos que cubre cada orden de pago de CUOTA (2026-10-02)
--
-- Solo para CONTROL de los módulos del estudiante: al generar la orden de una
-- cuota se marca a qué módulo(s) corresponde. NO cambia la orden de pago (monto,
-- número, PDF) ni vuelve a ligar las cuotas a módulos: las cuotas siguen siendo
-- libres (un programa de 12 módulos puede pagarse en 10 cuotas, y una cuota
-- puede cubrir más de un módulo).
-- Si la orden se anula, sus módulos quedan libres para otra cuota (las consultas
-- ignoran las órdenes ANULADAS).

CREATE TABLE IF NOT EXISTS ordenpagomodulo (
    IdOrdenPago INT(11) NOT NULL,
    IdModulo INT(11) NOT NULL,
    PRIMARY KEY (IdOrdenPago, IdModulo),
    KEY idx_opm_modulo (IdModulo),
    CONSTRAINT fk_opm_orden FOREIGN KEY (IdOrdenPago) REFERENCES ordenpago (IdOrdenPago) ON DELETE CASCADE,
    CONSTRAINT fk_opm_modulo FOREIGN KEY (IdModulo) REFERENCES modulos (Idmodulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
