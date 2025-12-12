<?php
/**
 * Verificar programas detalladamente
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    echo "===== VERIFICACIÓN DETALLADA DE PROGRAMAS =====\n\n";

    // Consulta completa
    $stmt = $conexion->prepare("SELECT * FROM programa ORDER BY ProgramaID");
    $stmt->execute();
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "TOTAL DE REGISTROS EN LA TABLA PROGRAMA: " . count($programas) . "\n\n";
    echo str_repeat("=", 100) . "\n";

    foreach ($programas as $idx => $prog) {
        echo "PROGRAMA #" . ($idx + 1) . ":\n";
        echo "  ID: " . $prog['ProgramaID'] . "\n";
        echo "  Nombre: " . $prog['NombrePrograma'] . "\n";
        echo "  Código: " . $prog['Codigo'] . "\n";
        echo "  Grado: " . $prog['GradoAcademico'] . "\n";
        echo "  Estado: " . ($prog['Estado'] == 1 ? 'ACTIVO' : 'INACTIVO') . "\n";
        echo "  Módulos: " . $prog['Modulos'] . "\n";
        echo "  Costo: Bs. " . $prog['Costo'] . "\n";
        echo "  Sede: " . $prog['Sede'] . "\n";
        echo "  Fecha Inicio: " . $prog['FechaInicio'] . "\n";
        echo "  Fecha Fin: " . $prog['FechaFin'] . "\n";
        echo str_repeat("-", 100) . "\n";
    }

    // Resumen por estado
    $activos = array_filter($programas, function($p) { return $p['Estado'] == 1; });
    $inactivos = array_filter($programas, function($p) { return $p['Estado'] == 0; });

    echo "\n===== RESUMEN =====\n";
    echo "Total: " . count($programas) . " programas\n";
    echo "Activos (Estado=1): " . count($activos) . " programas\n";
    echo "Inactivos (Estado=0): " . count($inactivos) . " programas\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
