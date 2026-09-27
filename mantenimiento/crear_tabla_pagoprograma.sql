-- =====================================================
-- TABLAS: pagoprograma / cuotaprograma
-- PROPÓSITO: Controlar el pago del PROGRAMA DE POSGRADO
-- (independiente del pago de matrícula, que ya se maneja
-- en `ordenpago` / `estudianteprograma`).
--
-- Cada inscripción (estudianteprograma) elige UN plan de pago
-- para el programa:
--   - REGULAR : se paga en cuotas. La secretaría define cuántas
--               cuotas, su monto y su fecha de vencimiento
--               (tabla `cuotaprograma`). Puede llevar un descuento.
--   - DESCUENTO: se establece un porcentaje de descuento sobre
--               el costo total del programa.
--   - GRUPAL   : cancelación total del programa cuando se
--               inscribe más de un estudiante junto (se agrupan
--               con `CodigoGrupo`).
-- =====================================================

-- --------------------------------------------------------
-- Tabla: pagoprograma (plan de pago del programa, por inscripción)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `pagoprograma` (
  `IdPagoPrograma` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `idInscripcion` INT(11) NOT NULL COMMENT 'FK a estudianteprograma (matrícula ya registrada)',
  `EstudianteID` INT(11) NOT NULL,
  `ProgramaID` INT(11) NOT NULL,

  -- Costo del programa (sin matrícula) y plan elegido
  `CostoTotalPrograma` DECIMAL(10,2) NOT NULL COMMENT 'Costo total del programa, tomado de programa.Costo',
  `TipoPlan` ENUM('REGULAR','DESCUENTO','GRUPAL') NOT NULL DEFAULT 'REGULAR',
  `PorcentajeDescuento` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Descuento aplicado (plan Regular o Descuento)',
  `MontoDescuento` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `MontoTotalPagar` DECIMAL(10,2) NOT NULL COMMENT 'CostoTotalPrograma - MontoDescuento',

  -- Plan Regular: cantidad de cuotas definidas por secretaría
  `NumeroCuotas` INT(11) NOT NULL DEFAULT 1,

  -- Plan Grupal: vincula a los estudiantes que se inscriben juntos
  `CodigoGrupo` VARCHAR(30) DEFAULT NULL COMMENT 'Mismo código para todos los inscritos del grupo',
  `CantidadInscritosGrupo` INT(11) NOT NULL DEFAULT 1,

  `ResponsableGeneracion` VARCHAR(100) DEFAULT NULL COMMENT 'Secretaria/usuario que definió el plan',
  `Observaciones` TEXT DEFAULT NULL,
  `FechaRegistro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Estado` ENUM('ACTIVO','COMPLETADO','ANULADO') NOT NULL DEFAULT 'ACTIVO',

  UNIQUE KEY `uq_pagoprograma_inscripcion` (`idInscripcion`),
  INDEX `idx_pagoprograma_estudiante` (`EstudianteID`),
  INDEX `idx_pagoprograma_programa` (`ProgramaID`),
  INDEX `idx_pagoprograma_tipoplan` (`TipoPlan`),
  INDEX `idx_pagoprograma_grupo` (`CodigoGrupo`),

  FOREIGN KEY (`idInscripcion`) REFERENCES `estudianteprograma`(`idInscripcion`) ON DELETE CASCADE,
  FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante`(`EstudianteID`) ON DELETE CASCADE,
  FOREIGN KEY (`ProgramaID`) REFERENCES `programa`(`ProgramaID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: cuotaprograma (cuotas del plan, definidas por secretaría)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `cuotaprograma` (
  `IdCuota` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `IdPagoPrograma` INT(11) NOT NULL,
  `NumeroCuota` INT(11) NOT NULL COMMENT 'Orden de la cuota (1, 2, 3...)',
  `MontoCuota` DECIMAL(10,2) NOT NULL COMMENT 'Monto establecido por secretaría',
  `FechaVencimiento` DATE NULL COMMENT 'Fecha límite establecida por secretaría; NULL si se acepta cancelar antes del inicio del módulo, sin fecha fija',
  `FechaPago` DATE DEFAULT NULL,
  `NumeroVoucher` VARCHAR(25) DEFAULT NULL,
  `FotoVoucher` LONGBLOB DEFAULT NULL,
  `Estado` ENUM('PENDIENTE','PAGADO','VENCIDO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  `FechaRegistro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  UNIQUE KEY `uq_cuotaprograma_numero` (`IdPagoPrograma`, `NumeroCuota`),
  INDEX `idx_cuotaprograma_estado` (`Estado`),
  INDEX `idx_cuotaprograma_vencimiento` (`FechaVencimiento`),

  FOREIGN KEY (`IdPagoPrograma`) REFERENCES `pagoprograma`(`IdPagoPrograma`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Vista: vista_estudiantes_matriculados
-- Estudiantes con matrícula ACTIVA, junto a su plan de pago
-- del programa (si ya lo tienen definido) y el resumen de cuotas.
-- --------------------------------------------------------

CREATE OR REPLACE VIEW `vista_estudiantes_matriculados` AS
SELECT
  ep.idInscripcion,
  e.EstudianteID,
  CONCAT(e.Apaterno, ' ', e.Amaterno, ' ', e.Nombre) AS NombreCompleto,
  CONCAT(e.Ci, IF(e.Complemento <> '', CONCAT('-', e.Complemento), '')) AS CiCompleto,
  e.Correo,
  e.Celular,

  p.ProgramaID,
  p.NombrePrograma,
  p.GradoAcademico,
  p.Sede,
  p.Codigo AS CodigoPrograma,

  ep.FechaInscripcion,
  ep.Estado AS EstadoInscripcion,
  ep.costomatricula AS CostoMatricula,
  ep.montoPagado AS MontoMatriculaPagado,
  ep.pagoCompleto AS MatriculaPagoCompleto,

  p.Costo AS CostoPrograma,

  pp.IdPagoPrograma,
  pp.TipoPlan,
  pp.PorcentajeDescuento,
  pp.MontoDescuento,
  pp.CostoTotalPrograma,
  pp.MontoTotalPagar,
  pp.NumeroCuotas,
  pp.CodigoGrupo,
  pp.CantidadInscritosGrupo,
  pp.Estado AS EstadoPlanPrograma,

  COALESCE(cc.CuotasPagadas, 0) AS CuotasPagadas,
  COALESCE(cc.MontoPagadoCuotas, 0) AS MontoPagadoPrograma,
  (COALESCE(pp.MontoTotalPagar, 0) - COALESCE(cc.MontoPagadoCuotas, 0)) AS SaldoPendientePrograma,
  cc.ProximoVencimiento

FROM estudianteprograma ep
INNER JOIN estudiante e ON e.EstudianteID = ep.EstudianteID
INNER JOIN programa p ON p.ProgramaID = ep.ProgramaID
LEFT JOIN pagoprograma pp ON pp.idInscripcion = ep.idInscripcion AND pp.Estado != 'ANULADO'
LEFT JOIN (
    SELECT
        IdPagoPrograma,
        SUM(CASE WHEN Estado = 'PAGADO' THEN 1 ELSE 0 END) AS CuotasPagadas,
        SUM(CASE WHEN Estado = 'PAGADO' THEN MontoCuota ELSE 0 END) AS MontoPagadoCuotas,
        MIN(CASE WHEN Estado = 'PENDIENTE' THEN FechaVencimiento END) AS ProximoVencimiento
    FROM cuotaprograma
    WHERE Estado != 'ANULADO'
    GROUP BY IdPagoPrograma
) cc ON cc.IdPagoPrograma = pp.IdPagoPrograma

WHERE ep.Estado = 'ACTIVO';

-- =====================================================
-- NOTAS
-- =====================================================
--
-- - `pagoprograma` es 1:1 con `estudianteprograma.idInscripcion`
--   (un estudiante matriculado tiene un único plan de pago activo
--   para el programa; si se anula, puede registrarse uno nuevo).
-- - Plan REGULAR: se generan N filas en `cuotaprograma` con el
--   monto y la fecha de vencimiento que defina la secretaría.
-- - Plan DESCUENTO: normalmente 1 sola cuota (pago único) con
--   `PorcentajeDescuento` aplicado sobre `CostoTotalPrograma`.
-- - Plan GRUPAL: `CodigoGrupo` agrupa las inscripciones que pagan
--   juntas; `CantidadInscritosGrupo` registra cuántas son.
-- - `vista_estudiantes_matriculados` sirve para listar matriculados
--   con el estado de su plan de pago y saldo pendiente del programa.
--
-- =====================================================
