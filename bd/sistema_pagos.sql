-- =========================================
-- SISTEMA DE PLAN DE PAGOS Y VOUCHERS
-- Migración PHP 8 - Compatible con PDO
-- =========================================

USE `proyecto`;

-- --------------------------------------------------------
-- Tabla: plan_pagos
-- Almacena el plan de pagos de cada inscripción
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `plan_pagos` (
  `PlanPagoID` int(11) NOT NULL AUTO_INCREMENT,
  `idInscripcion` int(11) NOT NULL COMMENT 'FK a estudianteprograma',
  `CostoTotal` decimal(10,2) NOT NULL COMMENT 'Costo total del programa',
  `MontoPagoInicial` decimal(10,2) DEFAULT 0.00 COMMENT 'Pago de matrícula/inicial',
  `MontoModulos` decimal(10,2) NOT NULL COMMENT 'Monto total de módulos',
  `CantidadModulos` int(11) NOT NULL COMMENT 'Número de módulos',
  `CostoPorModulo` decimal(10,2) NOT NULL COMMENT 'Costo calculado por módulo',
  `FechaCreacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `Estado` enum('ACTIVO','COMPLETADO','SUSPENDIDO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`PlanPagoID`),
  KEY `idx_inscripcion` (`idInscripcion`),
  CONSTRAINT `fk_planpago_inscripcion` FOREIGN KEY (`idInscripcion`)
    REFERENCES `estudianteprograma` (`idInscripcion`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Plan de pagos de cada inscripción';

-- --------------------------------------------------------
-- Tabla: cuota
-- Detalle de cada cuota del plan de pagos (una por módulo)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cuota` (
  `CuotaID` int(11) NOT NULL AUTO_INCREMENT,
  `PlanPagoID` int(11) NOT NULL COMMENT 'FK a plan_pagos',
  `NumeroModulo` int(11) NOT NULL COMMENT 'Número del módulo (1, 2, 3...)',
  `NombreModulo` varchar(100) DEFAULT NULL COMMENT 'Nombre descriptivo del módulo',
  `MontoCuota` decimal(10,2) NOT NULL COMMENT 'Monto a pagar por este módulo',
  `FechaVencimiento` date DEFAULT NULL COMMENT 'Fecha límite de pago',
  `EstadoPago` enum('PENDIENTE','PAGADO','VENCIDO','PARCIAL') DEFAULT 'PENDIENTE',
  `MontoPagado` decimal(10,2) DEFAULT 0.00 COMMENT 'Monto pagado acumulado',
  `FechaPago` datetime DEFAULT NULL COMMENT 'Fecha del último pago',
  `Observaciones` text COMMENT 'Notas adicionales',
  `FechaCreacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`CuotaID`),
  KEY `idx_planpago` (`PlanPagoID`),
  KEY `idx_estado` (`EstadoPago`),
  CONSTRAINT `fk_cuota_planpago` FOREIGN KEY (`PlanPagoID`)
    REFERENCES `plan_pagos` (`PlanPagoID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cuotas individuales del plan de pagos';

-- --------------------------------------------------------
-- Tabla: voucher
-- Registro de vouchers de pago de cada cuota
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `voucher` (
  `VoucherID` int(11) NOT NULL AUTO_INCREMENT,
  `CuotaID` int(11) NOT NULL COMMENT 'FK a cuota',
  `CodigoVoucher` varchar(50) NOT NULL COMMENT 'Código único del voucher',
  `MontoPago` decimal(10,2) NOT NULL COMMENT 'Monto pagado en este voucher',
  `FechaPago` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha del pago',
  `MetodoPago` enum('EFECTIVO','TRANSFERENCIA','DEPOSITO','QR','TARJETA','OTRO') DEFAULT 'EFECTIVO',
  `NumeroTransaccion` varchar(100) DEFAULT NULL COMMENT 'Número de transacción bancaria',
  `ArchivoVoucher` varchar(255) DEFAULT NULL COMMENT 'Ruta del archivo/imagen del voucher',
  `RegistradoPor` int(11) DEFAULT NULL COMMENT 'FK a personal - Usuario que registró',
  `Validado` tinyint(1) DEFAULT 0 COMMENT '0=Pendiente validación, 1=Validado',
  `FechaValidacion` datetime DEFAULT NULL,
  `ValidadoPor` int(11) DEFAULT NULL COMMENT 'FK a personal - Usuario que validó',
  `Observaciones` text COMMENT 'Notas del pago',
  `FechaCreacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`VoucherID`),
  UNIQUE KEY `idx_codigo_voucher` (`CodigoVoucher`),
  KEY `idx_cuota` (`CuotaID`),
  KEY `idx_fecha_pago` (`FechaPago`),
  CONSTRAINT `fk_voucher_cuota` FOREIGN KEY (`CuotaID`)
    REFERENCES `cuota` (`CuotaID`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Vouchers de pago de cuotas';

-- --------------------------------------------------------
-- Vista: vista_estado_pagos
-- Vista consolidada del estado de pagos por estudiante
-- --------------------------------------------------------

CREATE OR REPLACE VIEW `vista_estado_pagos` AS
SELECT
    ep.idInscripcion,
    ep.EstudianteID,
    CONCAT(e.Nombre, ' ', e.Apaterno, ' ', e.Amaterno) AS NombreCompleto,
    e.Ci,
    ep.ProgramaID,
    p.NombrePrograma,
    p.GradoAcademico,
    pp.PlanPagoID,
    pp.CostoTotal,
    pp.MontoPagoInicial,
    pp.MontoModulos,
    pp.CantidadModulos,
    pp.Estado AS EstadoPlan,
    COUNT(c.CuotaID) AS TotalCuotas,
    SUM(CASE WHEN c.EstadoPago = 'PAGADO' THEN 1 ELSE 0 END) AS CuotasPagadas,
    SUM(CASE WHEN c.EstadoPago = 'PENDIENTE' THEN 1 ELSE 0 END) AS CuotasPendientes,
    SUM(CASE WHEN c.EstadoPago = 'VENCIDO' THEN 1 ELSE 0 END) AS CuotasVencidas,
    SUM(c.MontoPagado) AS TotalPagado,
    (pp.MontoModulos - IFNULL(SUM(c.MontoPagado), 0)) AS SaldoPendiente,
    ROUND((IFNULL(SUM(c.MontoPagado), 0) / pp.MontoModulos * 100), 2) AS PorcentajePagado
FROM
    estudianteprograma ep
    INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
    INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
    LEFT JOIN plan_pagos pp ON ep.idInscripcion = pp.idInscripcion
    LEFT JOIN cuota c ON pp.PlanPagoID = c.PlanPagoID
GROUP BY
    ep.idInscripcion,
    ep.EstudianteID,
    e.Nombre,
    e.Apaterno,
    e.Amaterno,
    e.Ci,
    ep.ProgramaID,
    p.NombrePrograma,
    p.GradoAcademico,
    pp.PlanPagoID,
    pp.CostoTotal,
    pp.MontoPagoInicial,
    pp.MontoModulos,
    pp.CantidadModulos,
    pp.Estado;

-- --------------------------------------------------------
-- Índices adicionales para optimización
-- --------------------------------------------------------

ALTER TABLE `estudianteprograma`
  ADD INDEX IF NOT EXISTS `idx_estudiante` (`EstudianteID`),
  ADD INDEX IF NOT EXISTS `idx_programa` (`ProgramaID`);

-- --------------------------------------------------------
-- Datos de ejemplo (OPCIONAL - Comentar si no se desea)
-- --------------------------------------------------------

-- EJEMPLO: Inscripción con plan de pagos para ProgramaID 3 (DIPLOMADO EN ENDODONCIA)
/*
-- Insertar inscripción (requiere EstudianteID existente)
INSERT INTO estudianteprograma (EstudianteID, ProgramaID, FechaInscripcion)
VALUES (1, 3, '2025-11-12');

SET @idInscripcion = LAST_INSERT_ID();

-- Crear plan de pagos (6 módulos, 15000 Bs total, 1000 Bs matrícula)
INSERT INTO plan_pagos (idInscripcion, CostoTotal, MontoPagoInicial, MontoModulos, CantidadModulos, CostoPorModulo)
VALUES (@idInscripcion, 15000, 1000, 14000, 6, 2333.33);

SET @PlanPagoID = LAST_INSERT_ID();

-- Crear 6 cuotas (una por módulo)
INSERT INTO cuota (PlanPagoID, NumeroModulo, NombreModulo, MontoCuota, FechaVencimiento) VALUES
(@PlanPagoID, 1, 'MÓDULO I - INTRODUCCIÓN', 2333.33, '2025-12-15'),
(@PlanPagoID, 2, 'MÓDULO II - DIAGNÓSTICO', 2333.33, '2026-01-15'),
(@PlanPagoID, 3, 'MÓDULO III - TRATAMIENTO', 2333.33, '2026-02-15'),
(@PlanPagoID, 4, 'MÓDULO IV - TÉCNICAS AVANZADAS', 2333.33, '2026-03-15'),
(@PlanPagoID, 5, 'MÓDULO V - CASOS CLÍNICOS', 2333.33, '2026-04-15'),
(@PlanPagoID, 6, 'MÓDULO VI - EVALUACIÓN FINAL', 2333.34, '2026-05-15');
*/

-- =========================================
-- FIN DEL SCRIPT
-- =========================================
