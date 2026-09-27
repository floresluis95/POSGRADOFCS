-- ============================================
-- Script para crear tablas de MÓDULOS
-- ============================================

-- 1. Tabla MODULO (si no existe)
CREATE TABLE IF NOT EXISTS `modulo` (
  `ModuloID` int(11) NOT NULL AUTO_INCREMENT,
  `ProgramaID` int(11) NOT NULL,
  `NombreModulo` varchar(255) NOT NULL,
  `Codigo` varchar(50) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Creditos` int(11) DEFAULT 0,
  `HorasTeoricas` int(11) DEFAULT 0,
  `HorasPracticas` int(11) DEFAULT 0,
  `Costo` decimal(10,2) DEFAULT 0.00,
  `Estado` tinyint(1) DEFAULT 1,
  `FechaCreacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ModuloID`),
  KEY `ProgramaID` (`ProgramaID`),
  CONSTRAINT `fk_modulo_programa` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Tabla ESTUDIANTEMODULO (inscripciones a módulos)
CREATE TABLE IF NOT EXISTS `estudiantemodulo` (
  `idEstudianteModulo` int(11) NOT NULL AUTO_INCREMENT,
  `EstudianteID` int(11) NOT NULL,
  `ModuloID` int(11) NOT NULL,
  `costomodulo` decimal(10,2) NOT NULL,
  `nvauchermodulo` varchar(100) NOT NULL,
  `FechaInscripcion` date NOT NULL,
  `Estado` enum('ACTIVO','INACTIVO','RETIRADO') DEFAULT 'ACTIVO',
  `FechaRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idEstudianteModulo`),
  KEY `EstudianteID` (`EstudianteID`),
  KEY `ModuloID` (`ModuloID`),
  CONSTRAINT `fk_estudiantemodulo_estudiante` FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante` (`EstudianteID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_estudiantemodulo_modulo` FOREIGN KEY (`ModuloID`) REFERENCES `modulo` (`ModuloID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Índices adicionales para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_estudiantemodulo_estado ON estudiantemodulo(Estado);
CREATE INDEX IF NOT EXISTS idx_estudiantemodulo_fecha ON estudiantemodulo(FechaInscripcion);

-- ============================================
-- Datos de ejemplo para MODULO (opcional)
-- ============================================

-- Descomentar las siguientes líneas si deseas insertar módulos de ejemplo
/*
INSERT INTO `modulo` (`ProgramaID`, `NombreModulo`, `Codigo`, `Descripcion`, `Creditos`, `HorasTeoricas`, `HorasPracticas`, `Costo`) VALUES
(1, 'Metodología de la Investigación', 'MOD-001', 'Fundamentos de investigación científica', 4, 40, 20, 500.00),
(1, 'Estadística Aplicada', 'MOD-002', 'Análisis estadístico de datos', 4, 40, 20, 500.00),
(1, 'Epistemología', 'MOD-003', 'Teoría del conocimiento', 3, 30, 15, 450.00),
(2, 'Gestión de Proyectos', 'MOD-004', 'Administración y dirección de proyectos', 5, 50, 25, 600.00),
(2, 'Finanzas Corporativas', 'MOD-005', 'Gestión financiera empresarial', 5, 50, 25, 600.00);
*/
