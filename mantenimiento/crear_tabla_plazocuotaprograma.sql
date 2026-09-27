-- Tabla para registrar la fecha plazo (fecha límite) de pago de cuota por
-- Programa, definida por Administración/Secretaría desde el Calendario
-- Académico. Se usa para avisar al Estudiante al ingresar al sistema y para
-- bloquear la consulta de sus calificaciones si no ha cancelado su cuota
-- vencida.
CREATE TABLE IF NOT EXISTS `plazocuotaprograma` (
  `IdPlazo` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramaID` int(11) NOT NULL,
  `FechaPlazo` date NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Estado` varchar(20) NOT NULL DEFAULT 'ACTIVO',
  `CreadoPorUsuarioID` int(11) DEFAULT NULL,
  `FechaRegistro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdPlazo`),
  KEY `idx_programa` (`ProgramaID`),
  KEY `idx_fecha` (`FechaPlazo`),
  CONSTRAINT `fk_plazocuota_programa` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
