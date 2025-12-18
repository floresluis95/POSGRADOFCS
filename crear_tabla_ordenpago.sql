-- =====================================================
-- TABLA: ordenpago
-- PROPÓSITO: Almacenar órdenes de pago (preregistros)
-- con información adicional independiente de estudianteprograma
-- =====================================================

CREATE TABLE IF NOT EXISTS `ordenpago` (
  `IdOrdenPago` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `NumeroOrden` VARCHAR(50) NOT NULL UNIQUE,
  `idInscripcion` INT(11) NULL COMMENT 'FK a estudianteprograma (NULL si aún no confirmado)',
  `EstudianteID` INT(11) NOT NULL,
  `ProgramaID` INT(11) NOT NULL,

  -- Montos
  `MontoTotal` DECIMAL(10,2) NOT NULL COMMENT 'Monto total antes de descuento',
  `MontoDescuento` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Monto del descuento aplicado',
  `PorcentajeDescuento` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de descuento',
  `MontoFinal` DECIMAL(10,2) NOT NULL COMMENT 'Monto final a pagar (después descuento)',

  -- Tipo de pago
  `PagoCompleto` TINYINT(1) DEFAULT 0 COMMENT '1=Pago completo, 0=Solo matrícula',

  -- Fechas
  `FechaGeneracion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FechaVencimiento` DATE NULL COMMENT 'Fecha límite para pago',
  `FechaConfirmacion` DATETIME NULL COMMENT 'Fecha cuando se confirmó el pago',

  -- Responsable y observaciones
  `ResponsableGeneracion` VARCHAR(100) NULL COMMENT 'Usuario que generó la orden',
  `Observaciones` TEXT NULL,

  -- Datos para facturación (opcional)
  `NombreFactura` VARCHAR(200) NULL,
  `NitCiFactura` VARCHAR(50) NULL,

  -- Estado de la orden
  `Estado` ENUM('PENDIENTE', 'CONFIRMADO', 'ANULADO', 'VENCIDO') DEFAULT 'PENDIENTE',

  -- Índices
  INDEX `idx_estudiante` (`EstudianteID`),
  INDEX `idx_programa` (`ProgramaID`),
  INDEX `idx_estado` (`Estado`),
  INDEX `idx_fecha_generacion` (`FechaGeneracion`),

  -- Claves foráneas
  FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante`(`EstudianteID`) ON DELETE CASCADE,
  FOREIGN KEY (`ProgramaID`) REFERENCES `programa`(`ProgramaID`) ON DELETE CASCADE,
  FOREIGN KEY (`idInscripcion`) REFERENCES `estudianteprograma`(`idInscripcion`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- COMENTARIOS SOBRE LA ESTRUCTURA
-- =====================================================
--
-- Esta tabla permite:
-- 1. Guardar órdenes de pago independientes
-- 2. Agregar campos adicionales sin afectar estudianteprograma
-- 3. Tener un historial completo de órdenes (incluso anuladas)
-- 4. Datos de facturación opcionales
-- 5. Fechas de vencimiento y confirmación
-- 6. Relación opcional con estudianteprograma (cuando se confirma)
--
-- Estados:
-- - PENDIENTE: Orden generada, esperando pago
-- - CONFIRMADO: Pago verificado y registrado en estudianteprograma
-- - ANULADO: Orden cancelada
-- - VENCIDO: Orden expirada sin pago
--
-- =====================================================
