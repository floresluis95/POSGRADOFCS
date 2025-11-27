<?php
/**
 * Script de prueba para el PDF
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Probando PDF con EstudianteID = 1\n\n";

// Simular parámetro GET
$_GET['id'] = 1;

// Incluir el archivo PDF
include 'extensiones/tcpdf/pdf/pdfestudiante.php';
?>
