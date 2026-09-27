<?php
/**
 * Script para crear la tabla del catálogo de firmantes (nombres que pueden
 * aparecer como "Responsable de la Firma" en las órdenes de pago impresas).
 */

require_once file_exists(__DIR__ . '/modelos/conexion.modelo.php') ? __DIR__ . '/modelos/conexion.modelo.php' : __DIR__ . '/core/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    $sql = "CREATE TABLE IF NOT EXISTS firmante (
        IdFirmante INT AUTO_INCREMENT PRIMARY KEY,
        Nombre VARCHAR(150) NOT NULL,
        Estado TINYINT(1) NOT NULL DEFAULT 1,
        FechaRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_firmante_nombre (Nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    COMMENT='Catálogo de nombres para la línea de firma de las órdenes de pago'";

    $pdo->exec($sql);
    echo "✓ Tabla 'firmante' creada exitosamente\n";

    $stmt = $pdo->query("DESCRIBE firmante");
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n=== ESTRUCTURA DE LA TABLA firmante ===\n";
    foreach ($campos as $campo) {
        echo "- {$campo['Field']}: {$campo['Type']}\n";
    }

    echo "\n✓ Script completado exitosamente\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
