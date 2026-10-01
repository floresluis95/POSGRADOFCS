-- Recuperación de contraseña por correo (2026-10-01)
-- 1) El personal (ADM, SEC, AUX) no tenía correo; estudiantes y docentes ya lo tienen.
-- 2) Tabla de códigos de recuperación: el código se guarda como hash (nunca en claro),
--    vence a los 10 minutos, sirve una sola vez y admite un máximo de intentos.

ALTER TABLE personal ADD COLUMN Correo VARCHAR(150) NULL AFTER Telefono;

CREATE TABLE IF NOT EXISTS recuperacionpassword (
    IdRecuperacion INT(11) NOT NULL AUTO_INCREMENT,
    IdUsuario INT(11) NOT NULL,
    CodigoHash VARCHAR(255) NOT NULL,
    FechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FechaExpira DATETIME NOT NULL,
    Intentos TINYINT(4) NOT NULL DEFAULT 0,
    Usado TINYINT(1) NOT NULL DEFAULT 0,
    IpSolicitud VARCHAR(45) NULL,
    PRIMARY KEY (IdRecuperacion),
    KEY idx_recuperacion_usuario (IdUsuario, FechaCreacion),
    KEY idx_recuperacion_ip (IpSolicitud, FechaCreacion),
    CONSTRAINT fk_recuperacion_usuario FOREIGN KEY (IdUsuario) REFERENCES usuario (ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
