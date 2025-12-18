<?php
/**
 * Recrear tabla ordenpago con nueva estructura
 */

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== RECREANDO TABLA ordenpago ===\n\n";

    // 1. Eliminar tabla vieja si existe
    echo "Eliminando tabla vieja...\n";
    $pdo->exec("DROP TABLE IF EXISTS `ordenpago`");
    echo "✅ Tabla vieja eliminada.\n\n";

    // 2. Crear nueva tabla
    echo "Creando nueva tabla con estructura actualizada...\n";
    $sql = "
    CREATE TABLE `ordenpago` (
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
      INDEX `idx_fecha_generacion` (`FechaGeneracion`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);
    echo "✅ Tabla 'ordenpago' creada exitosamente con nueva estructura.\n\n";

    // 3. Verificar estructura
    echo "=== ESTRUCTURA DE LA TABLA ===\n\n";
    $stmt = $pdo->query("DESCRIBE ordenpago");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    printf("%-30s %-25s %-10s %-15s\n", "Campo", "Tipo", "Null", "Extra");
    echo str_repeat("-", 85) . "\n";

    foreach ($columnas as $columna) {
        printf("%-30s %-25s %-10s %-15s\n",
            $columna['Field'],
            $columna['Type'],
            $columna['Null'],
            $columna['Extra']
        );
    }

    echo "\n";
    echo "=== CAMPOS PRINCIPALES ===\n";
    echo "✅ IdOrdenPago: Identificador único de la orden\n";
    echo "✅ NumeroOrden: Número de orden único (ORD-XXXXXX-YYYYMMDD)\n";
    echo "✅ EstudianteID: Estudiante que solicita la orden\n";
    echo "✅ ProgramaID: Programa al que se inscribe\n";
    echo "✅ MontoTotal: Monto antes de descuento\n";
    echo "✅ MontoDescuento: Descuento aplicado\n";
    echo "✅ MontoFinal: Monto a pagar\n";
    echo "✅ PagoCompleto: Indica si es pago completo o solo matrícula\n";
    echo "✅ Estado: PENDIENTE/CONFIRMADO/ANULADO/VENCIDO\n";
    echo "✅ FechaGeneracion: Fecha y hora de creación\n";

    echo "\n✅ RECREACIÓN COMPLETADA\n";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
