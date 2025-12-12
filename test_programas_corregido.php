<?php
/**
 * Test para verificar que todas las consultas devuelven los mismos programas
 */

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/programa.modelo.php';
require_once 'modelos/calificacion.modelo.php';
require_once 'modelos/reportemodulos.modelo.php';

echo "===== TEST DE CONSULTAS DE PROGRAMAS CORREGIDAS =====\n\n";

try {
    // 1. Vista de Programas y Módulos - ListaProgramaModelo()
    echo "1. VISTA DE PROGRAMAS Y MÓDULOS:\n";
    echo "   Usando: ProgramasModelos::ListaProgramaModelo()\n";
    $programas1 = ProgramasModelos::ListaProgramaModelo();
    echo "   Total de programas: " . count($programas1) . "\n";
    foreach ($programas1 as $prog) {
        echo "   - " . $prog['NombrePrograma'] . " (ID: " . $prog['ProgramaID'] . ")\n";
    }
    echo "\n";

    // 2. Vista de Reportes - ObtenerProgramasPorGradoModelo()
    echo "2. VISTA DE REPORTEMODULOS:\n";
    echo "   Usando: ReporteModulosModelo::ObtenerProgramasPorGradoModelo() por cada grado\n";

    // Obtener grados únicos
    $grados = ['MAESTRIA', 'DIPLOMADO', 'ESPECIALIDAD', 'DOCTORADO'];
    $totalProgramasReporte = 0;
    $programasReporte = [];

    foreach ($grados as $grado) {
        $progs = ReporteModulosModelo::ObtenerProgramasPorGradoModelo($grado);
        if (!empty($progs)) {
            echo "   Grado $grado: " . count($progs) . " programas\n";
            $totalProgramasReporte += count($progs);
            $programasReporte = array_merge($programasReporte, $progs);
        }
    }
    echo "   Total de programas: $totalProgramasReporte\n";
    foreach ($programasReporte as $prog) {
        echo "   - " . $prog['NombrePrograma'] . " (ID: " . $prog['ProgramaID'] . ")\n";
    }
    echo "\n";

    // 3. Vista de Calificaciones (bnotaestudiante) - ObtenerProgramasPorGradoModelo()
    echo "3. VISTA DE CALIFICACIONES (bnotaestudiante):\n";
    echo "   Usando: CalificacionModelo::ObtenerProgramasPorGradoModelo() por cada grado\n";

    $totalProgramasCalif = 0;
    $programasCalif = [];

    foreach ($grados as $grado) {
        $progs = CalificacionModelo::ObtenerProgramasPorGradoModelo($grado);
        if (!empty($progs)) {
            echo "   Grado $grado: " . count($progs) . " programas\n";
            $totalProgramasCalif += count($progs);
            $programasCalif = array_merge($programasCalif, $progs);
        }
    }
    echo "   Total de programas: $totalProgramasCalif\n";
    foreach ($programasCalif as $prog) {
        echo "   - " . $prog['NombrePrograma'] . " (ID: " . $prog['ProgramaID'] . ")\n";
    }
    echo "\n";

    // Verificación final
    echo str_repeat("=", 70) . "\n";
    echo "VERIFICACIÓN FINAL:\n";
    echo str_repeat("=", 70) . "\n";

    $consistente = (count($programas1) == $totalProgramasReporte && count($programas1) == $totalProgramasCalif);

    if ($consistente) {
        echo "✓ CORRECTO: Todas las vistas muestran la misma cantidad de programas\n";
        echo "  - Vista de Programas/Módulos: " . count($programas1) . " programas\n";
        echo "  - Vista de Reportes: $totalProgramasReporte programas\n";
        echo "  - Vista de Calificaciones: $totalProgramasCalif programas\n";
        echo "\n✓ SOLUCIÓN APLICADA EXITOSAMENTE\n";
    } else {
        echo "✗ ADVERTENCIA: Las vistas NO muestran la misma cantidad\n";
        echo "  - Vista de Programas/Módulos: " . count($programas1) . " programas\n";
        echo "  - Vista de Reportes: $totalProgramasReporte programas\n";
        echo "  - Vista de Calificaciones: $totalProgramasCalif programas\n";
        echo "\n⚠ Revisar las consultas\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
