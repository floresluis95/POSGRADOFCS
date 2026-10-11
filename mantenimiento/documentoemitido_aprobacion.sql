-- Registro de documentos: reimpresión con los datos guardados y aprobación del decano.
--  - Datos: los datos con que se generó el documento (JSON), para volver a imprimirlo
--    (y corregirlo) desde Registro de documentos.
--  - Estado: PENDIENTE (generado, falta que lo apruebe el decano) o APROBADO (volvió
--    firmado/aprobado y se validó). Es solo control: no bloquea otros trámites.
-- Los documentos ya emitidos quedan como PENDIENTE y sin datos guardados (para
-- reimprimirlos hay que volver a generarlos desde su pantalla).

ALTER TABLE documentoemitido
    ADD COLUMN Datos TEXT NULL AFTER NumeroVaucher,
    ADD COLUMN Estado ENUM('PENDIENTE', 'APROBADO') NOT NULL DEFAULT 'PENDIENTE' AFTER FechaEmision,
    ADD COLUMN FechaAprobacion DATETIME NULL AFTER Estado,
    ADD COLUMN UsuarioAprobadorID INT NULL AFTER FechaAprobacion,
    ADD KEY idx_estado (Estado);
