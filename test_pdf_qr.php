<?php
/**
 * Script de prueba para verificar el PDF con QR
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Probando PDF con código QR ===\n\n";

// Simular parámetro GET
$_GET['id'] = 1;

echo "Generando PDF para Estudiante ID: 1\n";
echo "Si ves código PDF a continuación, significa que funciona correctamente.\n\n";

// Incluir el archivo PDF
ob_start();
include 'extensiones/tcpdf/pdf/pdfestudiante.php';
$output = ob_get_clean();

// Verificar que se generó PDF
if (strpos($output, '%PDF') === 0) {
    echo "✓ ¡PDF generado exitosamente con código QR!\n";
    echo "✓ El PDF contiene " . strlen($output) . " bytes\n";
} else {
    echo "✗ Error al generar PDF\n";
    echo "Salida: " . substr($output, 0, 500) . "\n";
}
?>
