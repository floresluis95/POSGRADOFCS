-- Requisitos de matriculación (2026-10-01)
-- 1) `requisito`: catálogo de documentos que se piden al matricularse. Se pueden
--    agregar o desactivar requisitos sin tocar código (Estado = 0 lo oculta).
-- 2) `requisitoinscripcion`: qué requisitos entregó cada inscripción
--    (estudiante + programa), cuándo y quién lo registró. No bloquea nada:
--    la inscripción se hace igual y Matriculados avisa lo que falta.

CREATE TABLE IF NOT EXISTS requisito (
    IdRequisito INT(11) NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(150) NOT NULL,
    Obligatorio TINYINT(1) NOT NULL DEFAULT 1,
    Orden INT(11) NOT NULL DEFAULT 0,
    Estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (IdRequisito)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS requisitoinscripcion (
    IdRequisitoInscripcion INT(11) NOT NULL AUTO_INCREMENT,
    idInscripcion INT(11) NOT NULL,
    IdRequisito INT(11) NOT NULL,
    Entregado TINYINT(1) NOT NULL DEFAULT 0,
    FechaEntrega DATE NULL,
    Observacion VARCHAR(255) NULL,
    ResponsableRegistro VARCHAR(200) NULL,
    FechaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (IdRequisitoInscripcion),
    UNIQUE KEY uk_requisito_inscripcion (idInscripcion, IdRequisito),
    CONSTRAINT fk_reqins_inscripcion FOREIGN KEY (idInscripcion) REFERENCES estudianteprograma (idInscripcion) ON DELETE CASCADE,
    CONSTRAINT fk_reqins_requisito FOREIGN KEY (IdRequisito) REFERENCES requisito (IdRequisito)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO requisito (Nombre, Obligatorio, Orden)
SELECT * FROM (
    SELECT 'Trámite B' AS Nombre, 1 AS Obligatorio, 1 AS Orden UNION ALL
    SELECT 'Fotocopia de carnet', 1, 2 UNION ALL
    SELECT 'Fotocopia de acta de nacimiento', 1, 3 UNION ALL
    SELECT 'Fotografía', 1, 4 UNION ALL
    SELECT 'Hoja de vida', 1, 5 UNION ALL
    SELECT 'Diploma académico', 1, 6
) AS inicial
WHERE NOT EXISTS (SELECT 1 FROM requisito);

-- Requisito agregado después: Documento privado de compromiso (2026-10-01)
INSERT INTO requisito (Nombre, Obligatorio, Orden)
SELECT 'Documento privado de compromiso', 1, 7
WHERE NOT EXISTS (SELECT 1 FROM requisito WHERE Nombre = 'Documento privado de compromiso');
