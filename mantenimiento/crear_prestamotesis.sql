-- Préstamo de tesis (ejemplar físico) desde la Biblioteca de tesis.
-- El estudiante o docente lo solicita; el Administrador la reserva (APROBADO), registra la
-- entrega (PRESTADO, con fecha límite según PLAZO_PRESTAMO_TESIS en configuracion) y la
-- devolución (DEVUELTO). Puede rechazarla con motivo; el solicitante puede cancelarla
-- mientras no se la entregaron. Un solo ejemplar por tesis: no puede haber dos préstamos
-- APROBADO/PRESTADO de la misma tesis a la vez (lo controla el sistema).

CREATE TABLE IF NOT EXISTS prestamotesis (
    IdPrestamo INT AUTO_INCREMENT PRIMARY KEY,
    IdTrabajo INT NOT NULL COMMENT 'Tesis (trabajogrado.IdTrabajo)',
    TipoSolicitante CHAR(3) NOT NULL COMMENT 'EST o DOC',
    EstudianteID INT NULL,
    DocenteID INT NULL,
    Estado VARCHAR(12) NOT NULL DEFAULT 'SOLICITADO' COMMENT 'SOLICITADO, APROBADO, PRESTADO, DEVUELTO, RECHAZADO, CANCELADO',
    FechaSolicitud DATETIME NOT NULL,
    FechaAprobacion DATETIME NULL COMMENT 'Reservada: lista para recoger',
    FechaEntrega DATETIME NULL,
    FechaLimite DATE NULL COMMENT 'Hasta cuándo debe devolverla',
    FechaDevolucion DATETIME NULL,
    FechaCierre DATETIME NULL COMMENT 'Rechazo o cancelación',
    Motivo VARCHAR(300) NULL COMMENT 'Motivo del rechazo u observación de la devolución',
    ResponsableAprobacion VARCHAR(200) NULL,
    ResponsableEntrega VARCHAR(200) NULL,
    ResponsableDevolucion VARCHAR(200) NULL,
    ResponsableCierre VARCHAR(200) NULL,
    KEY idx_prestamo_trabajo (IdTrabajo, Estado),
    KEY idx_prestamo_estudiante (EstudianteID, Estado),
    KEY idx_prestamo_docente (DocenteID, Estado),
    KEY idx_prestamo_estado (Estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO configuracion (Clave, Valor, FechaActualizacion, ResponsableActualizacion)
VALUES ('PLAZO_PRESTAMO_TESIS', '7', NOW(), 'Migración');
