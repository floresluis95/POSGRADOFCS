-- Responsables de la firma en MAYÚSCULAS, como el resto de los nombres del sistema.
-- Desde ahora FirmanteModelos::RegistrarFirmanteModelo los guarda así; esto convierte los
-- ya registrados y la firma guardada en las órdenes de pago.
-- Importar con --default-character-set=utf8mb4 si es por consola (para las tildes y la Ñ).
-- Antes revisa que no haya dos nombres que solo difieran en mayúsculas/minúsculas
-- (el índice único uq_firmante_nombre ya no distingue mayúsculas, así que no debería haber).

UPDATE firmante SET Nombre = UPPER(TRIM(Nombre));
UPDATE ordenpago SET Firma = UPPER(TRIM(Firma)) WHERE Firma IS NOT NULL AND Firma <> '';
