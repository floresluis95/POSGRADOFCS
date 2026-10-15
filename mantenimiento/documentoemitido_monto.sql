-- Monto pagado por cada certificado emitido (historial de pagos de certificados en
-- Registro de documentos). El precio de cada tipo se define en configuracion
-- (COSTO_CERT_<TIPO>, pantalla Autoridades) y llega prellenado al generar el
-- certificado; se puede cambiar si el estudiante pagó otro monto.

ALTER TABLE documentoemitido
    ADD COLUMN Monto DECIMAL(10,2) NULL COMMENT 'Monto pagado por el certificado (Bs.)' AFTER NumeroVaucher;
