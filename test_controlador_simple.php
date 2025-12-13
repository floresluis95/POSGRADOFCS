<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TEST CARGA DE ARCHIVOS ===\n\n";

// Test 1: Cargar modelo
echo "1. Cargando modelo calificacion...\n";
require_once 'modelos/calificacion.modelo.php';
echo "   ✓ Modelo cargado\n\n";

// Test 2: Cargar controlador
echo "2. Cargando controlador calificacion...\n";
require_once 'controladores/calificacion.controlador.php';
echo "   ✓ Controlador cargado\n\n";

// Test 3: Instanciar controlador
echo "3. Instanciando controlador...\n";
$ctrl = new CalificacionControlador();
echo "   ✓ Controlador instanciado\n\n";

// Test 4: Simular sesión y llamar método
echo "4. Probando obtener docente logueado...\n";
session_start();
$_SESSION['Usuario'] = '1234567'; // Cambia por CI real
$_SESSION['Validar'] = true;

ob_start();
$ctrl->ObtenerDocenteLogueadoControlador();
$output = ob_get_clean();

echo "   Respuesta:\n";
echo "   " . $output . "\n\n";

// Test 5: Validar JSON
echo "5. Validando JSON...\n";
$json = json_decode($output, true);
if ($json === null) {
    echo "   ✗ ERROR: No es JSON válido\n";
    echo "   Error: " . json_last_error_msg() . "\n";
} else {
    echo "   ✓ JSON válido\n";
    echo "   Status: " . $json['status'] . "\n";
}
?>
