<?php
require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== TODOS LOS CAMPOS DE LA TABLA modulos ===\n\n";

    $stmt = $pdo->query("SHOW FULL COLUMNS FROM modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        print_r($col);
        echo "\n";
    }

    echo "\n=== EJEMPLO DE REGISTRO ===\n";
    $stmt = $pdo->query("SELECT * FROM modulos LIMIT 1");
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        foreach ($registro as $campo => $valor) {
            echo "$campo => $valor\n";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
