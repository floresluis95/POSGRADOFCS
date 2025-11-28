<?php
/**
 * Test directo del modelo
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/calificacion.modelo.php';

echo "=== TEST DIRECTO DEL MODELO ===\n\n";

// Probar con cada docente
$docentes = [1, 2];

foreach ($docentes as $docenteID) {
    echo "Docente ID: $docenteID\n";

    $asignaciones = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docenteID);

    echo "Resultado:\n";
    echo "  - Total: " . count($asignaciones) . "\n";

    if (count($asignaciones) > 0) {
        foreach ($asignaciones as $i => $asig) {
            echo "  Asignación " . ($i + 1) . ":\n";
            echo "    - Módulo: {$asig['nombremodulo']}\n";
            echo "    - Código: {$asig['codigomodulo']}\n";
            echo "    - Programa: {$asig['NombrePrograma']}\n";
            echo "    - Grado: {$asig['GradoAcademico']}\n";
        }
    }

    // Crear respuesta JSON como lo haría el controlador
    $response = json_encode([
        'status' => 'success',
        'data' => $asignaciones
    ]);

    echo "\nJSON generado:\n";
    echo $response . "\n";

    // Simular lo que recibe JavaScript
    $decoded = json_decode($response, true);
    echo "\nLo que recibiría JavaScript:\n";
    echo "  - status: {$decoded['status']}\n";
    echo "  - data.length: " . count($decoded['data']) . "\n";

    echo "\n" . str_repeat("-", 60) . "\n\n";
}

echo "=== FIN DEL TEST ===\n";
?>
