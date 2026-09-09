-- Tabla para registrar el trabajo de grado (Tesis / Monografía) aprobado
-- por un estudiante en un programa, para reutilizar el título automáticamente
-- en los certificados de Aprobación de Monografía y Medio Magnético.

CREATE TABLE IF NOT EXISTS `trabajogrado` (
  `IdTrabajo` int(11) NOT NULL AUTO_INCREMENT,
  `EstudianteID` int(11) NOT NULL,
  `ProgramaID` int(11) NOT NULL,
  `TituloTrabajo` text NOT NULL,
  `TipoTrabajo` varchar(60) NOT NULL DEFAULT 'MEDIO FISICO Y DIGITAL DEL TRABAJO DE TESIS',
  `Tribunal` text DEFAULT NULL,
  `CalificacionNumeral` decimal(5,2) DEFAULT NULL,
  `FechaEntrega` date DEFAULT NULL,
  `FechaAprobacion` date DEFAULT NULL,
  `UsuarioRegistroID` int(11) DEFAULT NULL,
  `FechaRegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaModificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`IdTrabajo`),
  UNIQUE KEY `uq_estudiante_programa` (`EstudianteID`,`ProgramaID`),
  KEY `idx_programa` (`ProgramaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
