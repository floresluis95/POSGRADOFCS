<?php
/**
 * Test de la consulta corregida
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/calificacion.modelo.php';

echo "=== TEST DE ASIGNACIONES CORREGIDAS ===\n\n";

$docenteID = isset($_GET['id']) ? intval($_GET['id']) : 1;

echo "Docente ID: $docenteID\n\n";

// Probar la función del modelo
$asignaciones = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docenteID);

if (count($asignaciones) > 0) {
    echo "✓ Se encontraron " . count($asignaciones) . " asignaciones:\n\n";

    foreach ($asignaciones as $i => $asig) {
        echo "Asignación #" . ($i + 1) . ":\n";
        echo "  - Módulo ID: {$asig['Idmodulo']}\n";
        echo "  - Nombre Módulo: {$asig['nombremodulo']}\n";
        echo "  - Código Módulo: {$asig['codigomodulo']}\n";
        echo "  - Programa: {$asig['NombrePrograma']}\n";
        echo "  - Grado Académico: {$asig['GradoAcademico']}\n";
        echo "  - Código Programa: {$asig['CodigoPrograma']}\n";
        echo "  - Total Estudiantes: {$asig['TotalEstudiantes']}\n";
        echo "\n";
    }
} else {
    echo "✗ No se encontraron asignaciones para este docente\n";
}

echo "=== FIN DEL TEST ===\n";
?>
