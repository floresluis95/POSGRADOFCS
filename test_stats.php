<?php
require_once 'modelos/conexion.modelo.php';
require_once 'modelos/reportemodulos.modelo.php';

$stats = ReporteModulosModelo::ObtenerEstadisticasGeneralesModelo();
echo "=== ESTADÍSTICAS GENERALES ===\n";
echo "Total Programas: " . $stats['totalProgramas'] . "\n";
echo "Total Módulos: " . $stats['totalModulos'] . "\n";
echo "Total Estudiantes: " . $stats['totalEstudiantes'] . "\n";
echo "Total Docentes: " . $stats['totalDocentes'] . "\n";
echo "Total Pagos: " . $stats['totalPagos'] . "\n";
?>
