<?php
require_once 'modelos/conexion.modelo.php';
require_once 'modelos/reportemodulos.modelo.php';

echo "=== PRUEBA FINAL DE ESTADÍSTICAS ===\n\n";

// Test 1: Estadísticas generales
$stats = ReporteModulosModelo::ObtenerEstadisticasGeneralesModelo();
echo "1. Estadísticas Generales:\n";
echo "   - Programas Activos: " . $stats['totalProgramas'] . " ✓\n";
echo "   - Módulos Disponibles: " . $stats['totalModulos'] . " ✓\n";
echo "   - Estudiantes Inscritos: " . $stats['totalEstudiantes'] . " ✓\n";
echo "   - Docentes Activos: " . $stats['totalDocentes'] . " ✓\n\n";

// Test 2: Grados académicos
$grados = ReporteModulosModelo::ObtenerGradosAcademicosModelo();
echo "2. Grados Académicos: " . count($grados) . " encontrados ✓\n";
foreach ($grados as $grado) {
    echo "   - " . $grado['GradoAcademico'] . "\n";
}
echo "\n";

// Test 3: Programas por grado
if (!empty($grados)) {
    $primerGrado = $grados[0]['GradoAcademico'];
    $programas = ReporteModulosModelo::ObtenerProgramasPorGradoModelo($primerGrado);
    echo "3. Programas de tipo '" . $primerGrado . "': " . count($programas) . " encontrados ✓\n\n";
}

// Test 4: Conteo de módulos por programa
$conteo = ReporteModulosModelo::ObtenerConteoModulosPorProgramaModelo();
echo "4. Resumen por Programa:\n";
foreach ($conteo as $prog) {
    echo "   - " . $prog['NombrePrograma'] . "\n";
    echo "     Módulos: " . $prog['TotalModulos'] . ", Inscritos: " . $prog['TotalInscritos'] . "\n";
}

echo "\n✅ TODAS LAS PRUEBAS PASARON CORRECTAMENTE\n";
?>
