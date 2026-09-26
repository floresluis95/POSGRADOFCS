-- Pagos de clínica: voucher con el que se confirma una orden de pago de clínica
-- (ordenpago con Observaciones = 'CLINICA'). Va aparte de pagomodulo para que
-- estos pagos no se cuenten como cuotas del programa ni afecten su saldo.
CREATE TABLE IF NOT EXISTS pagoclinica (
  IdPagoClinica int(11) NOT NULL AUTO_INCREMENT,
  IdOrdenPago int(11) NOT NULL,
  idinscripcion int(11) NOT NULL,
  Monto decimal(10,2) NOT NULL,
  FechaPago date NOT NULL,
  NumeroVoucher varchar(100) NOT NULL,
  FotoVoucher longblob DEFAULT NULL,
  ResponsableRegistro varchar(200) DEFAULT NULL,
  Estado enum('PAGADO','ANULADO') NOT NULL DEFAULT 'PAGADO',
  FechaRegistro timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (IdPagoClinica),
  KEY idx_pagoclinica_orden (IdOrdenPago),
  KEY idx_pagoclinica_inscripcion (idinscripcion),
  CONSTRAINT fk_pagoclinica_ordenpago FOREIGN KEY (IdOrdenPago) REFERENCES ordenpago (IdOrdenPago),
  CONSTRAINT fk_pagoclinica_inscripcion FOREIGN KEY (idinscripcion) REFERENCES estudianteprograma (idInscripcion) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos (vouchers) de órdenes de pago de clínica';
