-- Avisos a los docentes para que registren las calificaciones de un módulo ya terminado.
-- El Administrador los envía desde Registrar Notas; el docente los ve en su panel y en
-- Reporte de calificaciones, y al pulsar "Entendido" se guarda FechaLeido.

CREATE TABLE IF NOT EXISTS avisocalificacion (
    IdAviso INT AUTO_INCREMENT PRIMARY KEY,
    Idmodulo INT NOT NULL COMMENT 'Módulo que falta calificar',
    DocenteID INT NOT NULL COMMENT 'Docente avisado (el asignado al módulo al enviar)',
    Mensaje VARCHAR(600) NOT NULL COMMENT 'Texto que ve el docente',
    FechaEnvio DATETIME NOT NULL,
    EnviadoPor VARCHAR(200) NULL COMMENT 'Quién lo envió',
    FechaLeido DATETIME NULL COMMENT 'Cuándo el docente pulsó Entendido',
    KEY idx_aviso_docente (DocenteID, FechaLeido),
    KEY idx_aviso_modulo (Idmodulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
