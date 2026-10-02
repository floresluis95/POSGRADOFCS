-- Autoridades que firman documentos (2026-10-02)
-- Una fila por cargo (DIRECTOR, COORDINADOR). El ADM cambia el título y el
-- nombre desde la pantalla "Autoridades" cuando hay cambio de autoridad, y el
-- Documento Privado de Compromiso toma los datos de aquí.
-- No confundir con `firmante`: ese es el catálogo de nombres que se elige al
-- imprimir cada orden de pago.

CREATE TABLE IF NOT EXISTS autoridad (
    Cargo VARCHAR(20) NOT NULL,
    Titulo VARCHAR(50) NOT NULL DEFAULT '',
    Nombre VARCHAR(150) NOT NULL,
    FechaActualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ResponsableActualizacion VARCHAR(100) NULL,
    PRIMARY KEY (Cargo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO autoridad (Cargo, Titulo, Nombre) VALUES
    ('DIRECTOR', 'MSc. Ing.', 'Carlos Antezana García'),
    ('COORDINADOR', 'MSc. Dr.', 'Milton Mamani Morales');
