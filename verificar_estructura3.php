<?php
require_once 'modelos/conexion.modelo.php';

$pdo = Conexion::Conectar();

echo "=== Columnas de 'pagomodulo' ===\n";
$stmt = $pdo->query("DESCRIBE pagomodulo");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n=== Columnas de 'modulos' ===\n";
$stmt = $pdo->query("DESCRIBE modulos");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n=== Columnas de 'profesion' ===\n";
try {
    $stmt = $pdo->query("DESCRIBE profesion");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
