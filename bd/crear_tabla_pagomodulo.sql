-- ============================================
-- Script para crear tabla PAGOMODULO
-- Sistema de registro de pagos de módulos
-- ============================================

CREATE TABLE IF NOT EXISTS `pagomodulo` (
  `Idmodulo` int(11) NOT NULL AUTO_INCREMENT,
  `idinscripcion` int(11) NOT NULL COMMENT 'Relación con la tabla estudianteprograma',
  `nmodulo` varchar(150) NOT NULL COMMENT 'Nombre del módulo',
  `costomodulo` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo del módulo',
  `fechapago` date NOT NULL COMMENT 'Fecha de pago del módulo',
  `nvaucher` varchar(100) DEFAULT NULL COMMENT 'Número de voucher de pago',
  `fmodulo` longblob DEFAULT NULL COMMENT 'Foto/archivo del voucher de pago',
  `Estado` enum('PAGADO','PENDIENTE','ANULADO') DEFAULT 'PAGADO',
  `FechaRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Idmodulo`),
  KEY `idx_idinscripcion` (`idinscripcion`),
  KEY `idx_fechapago` (`fechapago`),
  KEY `idx_estado` (`Estado`),
  CONSTRAINT `fk_pagomodulo_inscripcion` FOREIGN KEY (`idinscripcion`)
    REFERENCES `estudianteprograma` (`idInscripcion`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Registro de pagos de módulos por estudiante';

-- ============================================
-- Índices para mejorar rendimiento
-- ============================================
CREATE INDEX IF NOT EXISTS idx_pagomodulo_nmodulo ON pagomodulo(nmodulo);
CREATE INDEX IF NOT EXISTS idx_pagomodulo_costo ON pagomodulo(costomodulo);
