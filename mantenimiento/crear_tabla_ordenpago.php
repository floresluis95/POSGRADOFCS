<?php
/**
 * Script para crear tabla de registro de órdenes de pago generadas
 */

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    // Crear tabla ordenpago si no existe
    $sql = "CREATE TABLE IF NOT EXISTS ordenpago (
        IdOrdenPago INT AUTO_INCREMENT PRIMARY KEY,
        EstudianteID INT NOT NULL,
        idinscripcion INT NOT NULL,
        ProgramaID INT NOT NULL,
        ListaPagosModulo TEXT NOT NULL COMMENT 'IDs de pagomodulo separados por comas',
        MontoTotal DECIMAL(10,2) NOT NULL,
        FechaGeneracion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ResponsableGeneracion VARCHAR(200),
        NombreFactura VARCHAR(200),
        NitCiFactura VARCHAR(50),
        NumeroOrden VARCHAR(100) UNIQUE,
        FOREIGN KEY (EstudianteID) REFERENCES estudiante(EstudianteID),
        FOREIGN KEY (idinscripcion) REFERENCES estudianteprograma(idInscripcion),
        FOREIGN KEY (ProgramaID) REFERENCES programa(ProgramaID),
        INDEX idx_estudiante (EstudianteID),
        INDEX idx_inscripcion (idinscripcion),
        INDEX idx_programa (ProgramaID),
        INDEX idx_fecha (FechaGeneracion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Registro de órdenes de pago generadas'";

    $pdo->exec($sql);
    echo "✓ Tabla 'ordenpago' creada exitosamente\n";

    // Verificar estructura
    $stmt = $pdo->query("DESCRIBE ordenpago");
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n=== ESTRUCTURA DE LA TABLA ordenpago ===\n";
    foreach ($campos as $campo) {
        echo "- {$campo['Field']}: {$campo['Type']}\n";
    }

    echo "\n✓ Script completado exitosamente\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
