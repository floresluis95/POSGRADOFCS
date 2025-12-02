<?php
require_once 'modelos/conexion.modelo.php';

echo "=== VERIFICAR ESTRUCTURA DE TABLAS ===\n\n";

try {
    $pdo = Conexion::Conectar();

    // 1. Estructura tabla PROGRAMA
    echo "1. TABLA PROGRAMA:\n";
    $stmt = $pdo->query("DESCRIBE programa");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo "   - {$col['Field']}: {$col['Type']}\n";
    }

    echo "\n2. EJEMPLO DE DATOS DE PROGRAMA:\n";
    $stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, CostoMatricula FROM programa LIMIT 3");
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programas as $p) {
        echo "   ID: {$p['ProgramaID']} | {$p['NombrePrograma']} | Costo Matrícula: Bs. " . number_format($p['CostoMatricula'], 2) . "\n";
    }

    echo "\n3. TABLA MODULOS:\n";
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo "   - {$col['Field']}: {$col['Type']}\n";
    }

    echo "\n4. EJEMPLO DE DATOS DE MÓDULOS:\n";
    $stmt = $pdo->query("SELECT Idmodulo, nombremodulo, codigomodulo, costomodulo, ProgramaId FROM modulos WHERE estadomodulo = 'ACTIVO' LIMIT 5");
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($modulos as $m) {
        echo "   ID: {$m['Idmodulo']} | {$m['codigomodulo']} - {$m['nombremodulo']} | Costo: Bs. " . number_format($m['costomodulo'], 2) . " | ProgramaID: {$m['ProgramaId']}\n";
    }

    echo "\n5. CONTEO DE MÓDULOS POR PROGRAMA:\n";
    $stmt = $pdo->query("
        SELECT
            p.ProgramaID,
            p.NombrePrograma,
            p.CostoMatricula,
            COUNT(m.Idmodulo) as TotalModulos
        FROM programa p
        LEFT JOIN modulos m ON p.ProgramaID = m.ProgramaId AND m.estadomodulo = 'ACTIVO'
        GROUP BY p.ProgramaID
        HAVING TotalModulos > 0
        LIMIT 5
    ");
    $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resumen as $r) {
        $costoPorModulo = $r['TotalModulos'] > 0 ? $r['CostoMatricula'] / $r['TotalModulos'] : 0;
        echo "   Programa: {$r['NombrePrograma']}\n";
        echo "     - Costo Matrícula: Bs. " . number_format($r['CostoMatricula'], 2) . "\n";
        echo "     - Módulos Activos: {$r['TotalModulos']}\n";
        echo "     - Costo por Módulo (calculado): Bs. " . number_format($costoPorModulo, 2) . "\n\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
