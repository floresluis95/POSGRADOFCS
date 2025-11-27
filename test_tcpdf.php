<?php
/**
 * Script de prueba para verificar que TCPDF funciona
 */

echo "Probando TCPDF...\n\n";

// Intentar cargar el autoloader
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "✓ Autoloader cargado correctamente\n";
} else {
    die("✗ Error: No se encontró vendor/autoload.php\n");
}

// Verificar que TCPDF existe
if (class_exists('TCPDF')) {
    echo "✓ Clase TCPDF disponible\n";

    // Crear una instancia de prueba
    try {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        echo "✓ Instancia de TCPDF creada exitosamente\n";
        echo "\n=== TCPDF está listo para usar ===\n";
        echo "Ahora puedes generar reportes PDF sin problemas.\n";
    } catch (Exception $e) {
        echo "✗ Error al crear instancia: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Error: La clase TCPDF no está disponible\n";
}
?>
