<?php
require_once 'modelos/conexion.modelo.php';
require_once 'modelos/reportemodulos.modelo.php';
require_once 'controladores/reportemodulos.controlador.php';

echo "=== PRUEBA DE ESTADÍSTICAS POR SEDE ===\n\n";

// Test 1: Obtener datos del modelo
echo "1. Datos del Modelo:\n";
$sedes = ReporteModulosModelo::ObtenerProgramasPorSedeModelo();
foreach ($sedes as $sede) {
    echo "   Sede: {$sede['Sede']}\n";
    echo "   - Total Programas: {$sede['TotalProgramas']}\n";
    echo "   - Tipos de Grado: {$sede['TiposGrado']}\n\n";
}

// Test 2: Generar HTML del controlador
echo "2. HTML Generado:\n";
echo str_repeat("-", 50) . "\n";
ReporteModulosControlador::MostrarEstadisticaSedesControlador();
echo "\n" . str_repeat("-", 50) . "\n";

echo "\n✅ PRUEBA COMPLETADA EXITOSAMENTE\n";
?>
