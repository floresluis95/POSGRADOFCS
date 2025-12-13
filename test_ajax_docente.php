<?php
// Simular la llamada AJAX
session_start();

// Simular sesión de docente con CI
$_SESSION['Usuario'] = '123456'; // Cambia esto por un CI real de docente en tu BD
$_SESSION['Validar'] = true;

// Simular petición POST
$_POST['accion'] = 'obtenerDocente';

// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SIMULANDO LLAMADA AJAX obtenerDocente ===\n\n";
echo "Usuario en sesión: " . $_SESSION['Usuario'] . "\n\n";

// Ejecutar el AJAX
ob_start();
include 'ajax/calificacion.ajax.php';
$output = ob_get_clean();

echo "=== RESPUESTA DEL AJAX ===\n";
echo $output;
echo "\n\n";

// Intentar decodificar JSON
echo "=== VALIDAR JSON ===\n";
$json = json_decode($output, true);
if ($json === null) {
    echo "ERROR: No es JSON válido\n";
    echo "Error JSON: " . json_last_error_msg() . "\n";
} else {
    echo "JSON válido:\n";
    print_r($json);
}
?>
