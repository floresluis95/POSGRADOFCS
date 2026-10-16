-- Órdenes de pago que genera el propio estudiante (cuota) llevan impresa la firma
-- y el sello de Posgrado. Paso 22 del LEEME.
-- 1) Marca de la orden generada por el estudiante (las del personal siguen en 0).
ALTER TABLE ordenpago
    ADD COLUMN GeneradaPorEstudiante TINYINT(1) NOT NULL DEFAULT 0 AFTER ResponsableGeneracion;

-- 2) Imágenes de la firma del coordinador y del sello (se cambian desde Configuración
--    > Autoridades cuando cambia el coordinador). Clave: FIRMA o SELLO.
CREATE TABLE IF NOT EXISTS imagenfirma (
    Clave VARCHAR(20) NOT NULL PRIMARY KEY,
    Imagen LONGBLOB NOT NULL,
    FechaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ResponsableActualizacion VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
