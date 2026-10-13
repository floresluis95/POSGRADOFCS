-- Reseteo de calificaciones de un módulo desde Reporte de Notas (2026-10-13)
-- Al resetear se BORRAN las notas del módulo (tabla calificacion) para que se
-- carguen de nuevo. La auditoría de Reporte de Notas sale de esas mismas filas,
-- así que antes de borrar se guarda aquí una copia de cada nota (JSON en Detalle)
-- con quién lo hizo, cuándo y por qué. Sirve de rastro y para recuperar a mano.

CREATE TABLE IF NOT EXISTS calificacionreseteo (
    ReseteoID INT NOT NULL AUTO_INCREMENT,
    Idmodulo INT NOT NULL,
    ProgramaId INT NOT NULL,
    TotalNotas INT NOT NULL DEFAULT 0,
    Motivo VARCHAR(255) NOT NULL,
    Usuario VARCHAR(100) NULL,
    Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Detalle LONGTEXT NOT NULL,
    PRIMARY KEY (ReseteoID),
    KEY idx_reseteo_modulo (Idmodulo, ProgramaId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
