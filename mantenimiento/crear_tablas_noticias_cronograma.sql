-- Noticias e informaciones del Panel principal, editables por Administracion/Secretaria.
CREATE TABLE IF NOT EXISTS `noticiapanel` (
  `IdNoticia` int(11) NOT NULL AUTO_INCREMENT,
  `Titulo` varchar(200) NOT NULL,
  `Descripcion` text NOT NULL,
  `Autor` varchar(100) NOT NULL DEFAULT 'Administración',
  `Etiqueta` varchar(30) NOT NULL DEFAULT 'NUEVO',
  `ImagenURL` varchar(255) DEFAULT NULL,
  `Enlace` varchar(255) DEFAULT NULL,
  `Orden` int(11) NOT NULL DEFAULT 0,
  `Estado` varchar(20) NOT NULL DEFAULT 'ACTIVO',
  `FechaPublicacion` date DEFAULT NULL,
  `CreadoPorUsuarioID` int(11) DEFAULT NULL,
  `FechaRegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaModificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`IdNoticia`),
  KEY `idx_estado_orden` (`Estado`, `Orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cronograma de Defensa (Tesis/Monografia): nombre, fecha, hora y lugar,
-- editable por Administracion/Secretaria, visible para todos en el Panel.
CREATE TABLE IF NOT EXISTS `cronogramadefensa` (
  `IdDefensa` int(11) NOT NULL AUTO_INCREMENT,
  `NombreEstudiante` varchar(150) NOT NULL,
  `ProgramaID` int(11) DEFAULT NULL,
  `FechaDefensa` date NOT NULL,
  `HoraDefensa` time DEFAULT NULL,
  `Lugar` varchar(150) NOT NULL,
  `Detalle` varchar(255) DEFAULT NULL,
  `Estado` varchar(20) NOT NULL DEFAULT 'ACTIVO',
  `CreadoPorUsuarioID` int(11) DEFAULT NULL,
  `FechaRegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaModificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`IdDefensa`),
  KEY `idx_programa` (`ProgramaID`),
  KEY `idx_fecha` (`FechaDefensa`),
  CONSTRAINT `fk_cronogramadefensa_programa` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
