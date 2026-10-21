-- Certificados exigidos para la titulación (2026-10-21). Paso 27 del LEEME.
-- El ADM marca en Costos de certificados qué certificados exige cada grado académico.
-- Con lo emitido/registrado de cada estudiante en su programa, el sistema calcula cuáles
-- le faltan (solo avisa: no bloquea nada). Se ve en Registro de documentos y en
-- "Mis requisitos" del estudiante.
-- Importar con --default-character-set=utf8mb4 si es por consola.

-- Clave: el tipo de certificado generado (CALIFICACIONES, HISTORIAL_ACADEMICO, ...) o
-- 'SD:<IdCertificadoSD>' para un certificado sin documento del catálogo.
CREATE TABLE IF NOT EXISTS certificadotitulacion (
    Clave VARCHAR(40) NOT NULL,
    GradoAcademico VARCHAR(30) NOT NULL,
    PRIMARY KEY (Clave, GradoAcademico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A qué certificado del catálogo corresponde cada registro sin documento (así no depende
-- del nombre: si el ADM lo renombra, lo ya pagado sigue contando)
ALTER TABLE documentoemitido
    ADD COLUMN IF NOT EXISTS IdCertificadoSD INT NULL AFTER NombreCertificado;

UPDATE documentoemitido d
INNER JOIN certificadosindocumento c ON c.Nombre = d.NombreCertificado
SET d.IdCertificadoSD = c.IdCertificadoSD
WHERE d.TipoDocumento = 'SIN_DOCUMENTO' AND d.IdCertificadoSD IS NULL;
