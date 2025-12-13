<?php
session_start();
require_once 'modelos/conexion.modelo.php';
require_once 'modelos/calificacion.modelo.php';

// Simular sesión de docente
echo "=== TEST DE ASIGNACIONES DOCENTE ===\n\n";

// Probar con DocenteID = 1
$docenteID = 1;

echo "Probando con DocenteID: $docenteID\n\n";

$asignaciones = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docenteID);

echo "Total asignaciones encontradas: " . count($asignaciones) . "\n\n";

if (count($asignaciones) > 0) {
    echo "PRIMERA ASIGNACIÓN:\n";
    print_r($asignaciones[0]);

    echo "\n\nCAMPOS IMPORTANTES:\n";
    echo "- EstadoModulo: " . ($asignaciones[0]['EstadoModulo'] ?? 'NO EXISTE') . "\n";
    echo "- ValidadoPor: " . ($asignaciones[0]['ValidadoPor'] ?? 'NULL') . "\n";
    echo "- FechaValidacion: " . ($asignaciones[0]['FechaValidacion'] ?? 'NULL') . "\n";
    echo "- NombreValidador: " . ($asignaciones[0]['NombreValidador'] ?? 'NULL') . "\n";
}

// Probar con DocenteID = 2
echo "\n\n=== Probando con DocenteID: 2 ===\n";
$asignaciones2 = CalificacionModelo::ObtenerAsignacionesDocenteModelo(2);
echo "Total asignaciones: " . count($asignaciones2) . "\n";

if (count($asignaciones2) > 0) {
    echo "EstadoModulo: " . ($asignaciones2[0]['EstadoModulo'] ?? 'NO EXISTE') . "\n";
}
?>
