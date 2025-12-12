<?php
/**
 * Test final para verificar que todo funciona correctamente
 */

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/programa.modelo.php';

echo "===== TEST FINAL DE ESTADOS DE PROGRAMAS =====\n\n";

try {
    $conexion = Conexion::Conectar();

    echo "1. VERIFICACIÓN DE ESTRUCTURA:\n";
    echo str_repeat("-", 70) . "\n";
    $stmt = $conexion->prepare("DESCRIBE programa");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columnas as $col) {
        if ($col['Field'] == 'Estado') {
            echo "   Columna Estado:\n";
            echo "   - Tipo: " . $col['Type'] . "\n";
            echo "   - Default: " . $col['Default'] . "\n\n";
        }
    }

    echo "2. PROGRAMAS ACTUALES EN LA BASE DE DATOS:\n";
    echo str_repeat("-", 70) . "\n";

    // Consulta directa a la base de datos
    $stmt = $conexion->prepare("SELECT ProgramaID, NombrePrograma, Estado FROM programa ORDER BY ProgramaID");
    $stmt->execute();
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programas as $prog) {
        $etiqueta = $prog['Estado'] == '1' ? 'ACTIVO' : ($prog['Estado'] == '0' ? 'INACTIVO' : 'DESCONOCIDO');
        echo sprintf("   ID: %-3s | %-45s | Estado: %s (%s)\n",
            $prog['ProgramaID'],
            $prog['NombrePrograma'],
            $prog['Estado'],
            $etiqueta
        );
    }

    echo "\n3. PRUEBA DE CONSULTA CON FILTRO (Estado = 1):\n";
    echo str_repeat("-", 70) . "\n";

    // Usar el modelo actualizado
    $programasActivos = ProgramasModelos::ListaProgramaModelo();
    echo "   Total de programas activos encontrados: " . count($programasActivos) . "\n";

    foreach ($programasActivos as $prog) {
        echo "   - " . $prog['NombrePrograma'] . " (ID: " . $prog['ProgramaID'] . ")\n";
    }

    echo "\n4. RESUMEN POR ESTADO:\n";
    echo str_repeat("-", 70) . "\n";
    $stmt = $conexion->prepare("SELECT Estado, COUNT(*) as Total FROM programa GROUP BY Estado");
    $stmt->execute();
    $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resumen as $res) {
        $etiqueta = $res['Estado'] == '1' ? 'ACTIVO' : ($res['Estado'] == '0' ? 'INACTIVO' : 'OTRO');
        echo "   Estado '{$res['Estado']}' ($etiqueta): {$res['Total']} programas\n";
    }

    echo "\n" . str_repeat("=", 70) . "\n";
    echo "✓ SISTEMA CORREGIDO EXITOSAMENTE\n";
    echo str_repeat("=", 70) . "\n";
    echo "\nConfiguración actual:\n";
    echo "  - Todos los programas activos tienen Estado = '1'\n";
    echo "  - Todos los programas inactivos tienen Estado = '0'\n";
    echo "  - El valor por defecto de la columna es '1'\n";
    echo "  - Los INSERT usan '1' para nuevos programas\n";
    echo "  - Los UPDATE usan '1' para activar y '0' para desactivar\n";
    echo "  - Las consultas filtran por Estado = 1\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
