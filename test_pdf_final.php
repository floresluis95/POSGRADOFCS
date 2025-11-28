<?php
/**
 * Script de prueba para el PDF restaurado
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Probando PDF restaurado con imágenes\n\n";

// Simular parámetro GET
$_GET['id'] = 1;

// Incluir el archivo PDF
include 'extensiones/tcpdf/pdf/pdfestudiante.php';
?>
