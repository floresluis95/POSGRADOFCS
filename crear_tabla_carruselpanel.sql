-- Diapositivas del carrusel del Panel principal: cada una vinculada a un
-- Programa real (para mostrar costo, modulos, fecha de inicio, etc. en el
-- modal de "Mas Informacion"), con imagen y textos editables por
-- Administracion/Secretaria.
CREATE TABLE IF NOT EXISTS `carruselpanel` (
  `IdSlide` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramaID` int(11) NOT NULL,
  `ImagenURL` varchar(255) DEFAULT NULL,
  `TituloPersonalizado` varchar(150) DEFAULT NULL,
  `DescripcionPersonalizada` varchar(255) DEFAULT NULL,
  `Orden` int(11) NOT NULL DEFAULT 0,
  `Estado` varchar(20) NOT NULL DEFAULT 'ACTIVO',
  `CreadoPorUsuarioID` int(11) DEFAULT NULL,
  `FechaRegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `FechaModificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`IdSlide`),
  KEY `idx_programa` (`ProgramaID`),
  CONSTRAINT `fk_carruselpanel_programa` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
