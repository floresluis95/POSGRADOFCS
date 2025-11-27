<?php
require_once 'modelos/conexion.modelo.php';

echo "=== Verificación de Estructura ===\n\n";

$pdo = Conexion::Conectar();

// Columnas de estudiante
echo "1. Columnas de 'estudiante':\n";
$stmt = $pdo->query("DESCRIBE estudiante");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "  - " . $col['Field'] . "\n";
}

echo "\n2. Tablas con 'modulo':\n";
$stmt = $pdo->query("SHOW TABLES LIKE '%modulo%'");
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $tabla) {
    echo "  - " . $tabla . "\n";
}

echo "\n3. Tablas con 'pago':\n";
$stmt = $pdo->query("SHOW TABLES LIKE '%pago%'");
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $tabla) {
    echo "  - " . $tabla . "\n";
}
?>
