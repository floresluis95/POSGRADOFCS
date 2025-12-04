<?php
require_once 'modelos/conexion.modelo.php';
require_once 'modelos/programa.modelo.php';

echo "=== PRUEBA DE FILTROS DE PROGRAMAS ===\n\n";

// Test 1: Sin filtros
echo "1. Sin filtros (todos los programas):\n";
$todos = ProgramasModelos::ListaProgramaModelo();
echo "   Total: " . count($todos) . " programas\n";
foreach ($todos as $p) {
    echo "   - {$p['NombrePrograma']} ({$p['GradoAcademico']}) - {$p['FechaInicio']}\n";
}
echo "\n";

// Test 2: Filtro por grado
echo "2. Filtro por MAESTRIA:\n";
$porGrado = ProgramasModelos::BuscarProgramasConFiltros('MAESTRIA', null, null);
echo "   Total: " . count($porGrado) . " programas\n";
foreach ($porGrado as $p) {
    echo "   - {$p['NombrePrograma']} ({$p['GradoAcademico']}) - {$p['FechaInicio']}\n";
}
echo "\n";

// Test 3: Filtro por fecha desde
echo "3. Filtro por fecha desde 2025-01-01:\n";
$porFecha = ProgramasModelos::BuscarProgramasConFiltros(null, '2025-01-01', null);
echo "   Total: " . count($porFecha) . " programas\n";
foreach ($porFecha as $p) {
    echo "   - {$p['NombrePrograma']} ({$p['GradoAcademico']}) - {$p['FechaInicio']}\n";
}
echo "\n";

// Test 4: Filtro combinado
echo "4. Filtro combinado (MAESTRIA + desde 2025-01-01):\n";
$combinado = ProgramasModelos::BuscarProgramasConFiltros('MAESTRIA', '2025-01-01', null);
echo "   Total: " . count($combinado) . " programas\n";
foreach ($combinado as $p) {
    echo "   - {$p['NombrePrograma']} ({$p['GradoAcademico']}) - {$p['FechaInicio']}\n";
}
echo "\n";

// Test 5: Simular parámetros GET
echo "5. Simulando parámetros GET:\n";
$_GET['grado'] = 'MAESTRIA';
$_GET['fechaInicio'] = '2025-01-01';
$_GET['fechaFin'] = '';

$grado = isset($_GET['grado']) && !empty($_GET['grado']) ? $_GET['grado'] : null;
$fechaInicio = isset($_GET['fechaInicio']) && !empty($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
$fechaFin = isset($_GET['fechaFin']) && !empty($_GET['fechaFin']) ? $_GET['fechaFin'] : null;

echo "   Grado capturado: '$grado'\n";
echo "   FechaInicio capturado: '$fechaInicio'\n";
echo "   FechaFin capturado: '$fechaFin'\n";

$resultado = ProgramasModelos::BuscarProgramasConFiltros($grado, $fechaInicio, $fechaFin);
echo "   Total encontrado: " . count($resultado) . " programas\n";

echo "\n✅ PRUEBA COMPLETADA\n";
?>
