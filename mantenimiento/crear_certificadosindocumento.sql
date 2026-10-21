-- Certificados sin documento (2026-10-21). Paso 26 del LEEME.
-- Certificados que el sistema no genera (se pagan y se entregan por otro lado): en
-- Registro de documentos solo se registran con su nombre, N° de voucher, fecha de pago
-- del voucher y monto; la fecha de registro es FechaEmision. Se guardan en
-- documentoemitido (TipoDocumento = 'SIN_DOCUMENTO'), así salen en el recibo de pago de
-- certificados de cada programa, en el reporte y en la cuenta del estudiante, y su
-- voucher entra en la regla de voucher único.
-- Importar con --default-character-set=utf8mb4 si es por consola.

-- Catálogo de nombres (lo mantiene el ADM en Registro de documentos)
CREATE TABLE IF NOT EXISTS certificadosindocumento (
    IdCertificadoSD INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(150) NOT NULL,
    Monto DECIMAL(10,2) NOT NULL DEFAULT 0,   -- precio sugerido (se puede cambiar al registrar)
    Activo TINYINT(1) NOT NULL DEFAULT 1,     -- inactivo: ya no se ofrece; lo registrado se conserva
    UNIQUE KEY uq_certificadosindocumento_nombre (Nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO certificadosindocumento (Nombre, Monto) VALUES ('PRO BIBLIOTECA', 0);

-- Nombre del certificado (solo los sin documento) y fecha en que se pagó el voucher
ALTER TABLE documentoemitido
    ADD COLUMN IF NOT EXISTS NombreCertificado VARCHAR(150) NULL AFTER TipoDocumento,
    ADD COLUMN IF NOT EXISTS FechaPago DATE NULL AFTER Monto;
