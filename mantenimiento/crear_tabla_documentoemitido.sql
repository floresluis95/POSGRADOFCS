-- Registro (bitácora) de cada documento/certificado generado por el sistema,
-- para tener trazabilidad de quién, cuándo y con qué N° de vaucher se emitió
-- cada Certificado de Calificaciones, Historial Académico, Aprobación de
-- Monografía o Medio Magnético.

CREATE TABLE IF NOT EXISTS `documentoemitido` (
  `IdDocumento` int(11) NOT NULL AUTO_INCREMENT,
  `TipoDocumento` varchar(40) NOT NULL,
  `EstudianteID` int(11) DEFAULT NULL,
  `ProgramaID` int(11) DEFAULT NULL,
  `NombreEstudiante` varchar(150) DEFAULT NULL,
  `NumeroVaucher` varchar(60) DEFAULT NULL,
  `UsuarioEmisorID` int(11) DEFAULT NULL,
  `FechaEmision` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`IdDocumento`),
  KEY `idx_estudiante` (`EstudianteID`),
  KEY `idx_tipo` (`TipoDocumento`),
  KEY `idx_fecha` (`FechaEmision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
