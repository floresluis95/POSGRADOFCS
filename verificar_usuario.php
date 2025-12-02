<?php
require_once 'modelos/conexion.modelo.php';

echo "=== ESTRUCTURA DE LA TABLA USUARIO ===\n\n";

try {
    $pdo = Conexion::Conectar();
    $stmt = $pdo->query("DESCRIBE usuario");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    printf("%-25s %-20s %-10s %-10s\n", "Campo", "Tipo", "Nulo", "Clave");
    echo str_repeat("-", 70) . "\n";

    foreach ($columns as $column) {
        printf("%-25s %-20s %-10s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'],
            $column['Key']
        );
    }

    echo "\n\n=== EJEMPLO DE REGISTRO ===\n\n";
    $stmt = $pdo->query("SELECT * FROM usuario LIMIT 1");
    $ejemplo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ejemplo) {
        foreach ($ejemplo as $campo => $valor) {
            echo "$campo: $valor\n";
        }
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
