<?php
require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== ESTRUCTURA TABLA calificacion ===\n";
    $stmt = $pdo->query("DESCRIBE calificacion");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo sprintf("%-25s %-15s %-5s %-5s %-10s %s\n",
            $col['Field'],
            $col['Type'],
            $col['Null'],
            $col['Key'],
            $col['Default'],
            $col['Extra']
        );
    }

    echo "\n=== ESTRUCTURA TABLA modulos ===\n";
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo sprintf("%-25s %-15s %-5s %-5s %-10s %s\n",
            $col['Field'],
            $col['Type'],
            $col['Null'],
            $col['Key'],
            $col['Default'],
            $col['Extra']
        );
    }

    echo "\n=== EJEMPLO DE REGISTROS EN calificacion ===\n";
    $stmt = $pdo->query("SELECT * FROM calificacion LIMIT 3");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($registros);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
