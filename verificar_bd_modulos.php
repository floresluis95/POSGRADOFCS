<?php
require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== ESTRUCTURA TABLA modulos ===\n\n";
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo sprintf("%-25s %-20s\n", $col['Field'], $col['Type']);
    }

    echo "\n=== EJEMPLO DE DATOS ===\n";
    $stmt = $pdo->query("SELECT * FROM modulos LIMIT 2");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($registros) > 0) {
        echo "\nCampos disponibles:\n";
        foreach (array_keys($registros[0]) as $campo) {
            echo "- $campo\n";
        }
    }

    echo "\n=== VERIFICAR SI EXISTE DocenteID = 1 ===\n";
    $stmt = $pdo->query("SELECT DocenteID, COUNT(*) as Total FROM modulos GROUP BY DocenteID");
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Módulos por docente:\n";
    foreach ($docentes as $d) {
        echo "DocenteID: " . ($d['DocenteID'] ?? 'NULL') . " - Total módulos: " . $d['Total'] . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
