-- Parámetros del sistema editables por el ADM (2026-10-02)
-- Tabla clave/valor. Primer parámetro: NOTA_APROBACION (nota mínima para aprobar
-- un módulo, 76). Antes estaba escrita en el código, y no igual en todas partes
-- (76 en registro de notas y reportes, 51 en la vista del estudiante y en
-- Matriculados). Ahora todo el sistema la lee de aquí.

CREATE TABLE IF NOT EXISTS configuracion (
    Clave VARCHAR(50) NOT NULL,
    Valor VARCHAR(255) NOT NULL,
    Descripcion VARCHAR(255) NULL,
    FechaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ResponsableActualizacion VARCHAR(100) NULL,
    PRIMARY KEY (Clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO configuracion (Clave, Valor, Descripcion) VALUES
    ('NOTA_APROBACION', '76', 'Nota mínima para aprobar un módulo (sobre 100)');
