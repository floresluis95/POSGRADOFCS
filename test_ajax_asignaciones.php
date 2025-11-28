<?php
/**
 * Test de la petición AJAX de asignaciones
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular sesión
session_start();
$_SESSION['IdUsuario'] = 1; // Simular usuario logueado

// Simular POST
$_POST['accion'] = 'obtenerAsignaciones';
$_POST['docenteID'] = 1; // Probar con DocenteID = 1

echo "=== TEST DE AJAX - OBTENER ASIGNACIONES ===\n\n";
echo "DocenteID enviado: {$_POST['docenteID']}\n\n";

// Incluir el archivo AJAX
ob_start();
include 'ajax/calificacion.ajax.php';
$response = ob_get_clean();

echo "Respuesta JSON:\n";
echo $response . "\n\n";

// Decodificar y mostrar de forma legible
$data = json_decode($response, true);

if ($data) {
    echo "Decodificado:\n";
    echo "  - Status: {$data['status']}\n";

    if ($data['status'] === 'success') {
        echo "  - Total asignaciones: " . count($data['data']) . "\n\n";

        if (count($data['data']) > 0) {
            foreach ($data['data'] as $i => $asig) {
                echo "  Asignación " . ($i + 1) . ":\n";
                foreach ($asig as $key => $value) {
                    echo "    - $key: $value\n";
                }
                echo "\n";
            }
        } else {
            echo "  ✗ Array de asignaciones vacío\n";
        }
    } else {
        echo "  - Message: {$data['message']}\n";
    }
} else {
    echo "Error al decodificar JSON\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>
