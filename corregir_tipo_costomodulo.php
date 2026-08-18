<?php
/**
 * Script para corregir el tipo de dato de costomodulo
 * Cambia de int(11) a decimal(10,2) para soportar valores decimales
 */

require_once 'modelos/conexion.modelo.php';

echo "=== CORRECCIÓN DE TIPO DE DATO costomodulo ===\n\n";

try {
    $pdo = Conexion::Conectar();

    echo "1. Tipo de dato actual:\n";
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        if ($col['Field'] == 'costomodulo') {
            echo "   costomodulo: {$col['Type']}\n\n";
        }
    }

    echo "2. Modificando tipo de dato a decimal(10,2)...\n";
    $pdo->exec("ALTER TABLE modulos MODIFY COLUMN costomodulo DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    echo "   ✓ Tipo de dato modificado exitosamente\n\n";

    echo "3. Verificando el cambio:\n";
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        if ($col['Field'] == 'costomodulo') {
            echo "   costomodulo: {$col['Type']}\n\n";
        }
    }

    echo "4. Redistribuyendo costos para todos los programas activos:\n";
    $stmtProgramas = $pdo->query("SELECT ProgramaID, NombrePrograma FROM programa WHERE Estado = 'ACTIVO'");
    $programas = $stmtProgramas->fetchAll(PDO::FETCH_ASSOC);

    require_once 'modelos/modulopagos_core.php';

    foreach ($programas as $prog) {
        echo "   - Procesando: {$prog['NombrePrograma']} (ID: {$prog['ProgramaID']})\n";
        $resultado = PagoModuloModelo::DistribuirCostoProgramaEnModulosModelo($prog['ProgramaID']);
        if ($resultado) {
            echo "     ✓ Costos distribuidos correctamente\n";
        } else {
            echo "     ⚠ No se pudo distribuir (posiblemente sin módulos)\n";
        }
    }

    echo "\n5. Verificando datos actualizados:\n";
    $stmt = $pdo->query("
        SELECT
            p.NombrePrograma,
            p.CostoMatricula,
            COUNT(m.Idmodulo) as TotalModulos,
            m.costomodulo as CostoIndividual
        FROM programa p
        INNER JOIN modulos m ON p.ProgramaID = m.ProgramaId
        WHERE m.estadomodulo = 'ACTIVO'
        GROUP BY p.ProgramaID, m.costomodulo
        LIMIT 10
    ");
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $r) {
        echo "   Programa: {$r['NombrePrograma']}\n";
        echo "     - Costo Matrícula: Bs. " . number_format($r['CostoMatricula'], 2) . "\n";
        echo "     - Costo por Módulo: Bs. " . number_format($r['CostoIndividual'], 2) . "\n\n";
    }

    echo "=== PROCESO COMPLETADO EXITOSAMENTE ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
