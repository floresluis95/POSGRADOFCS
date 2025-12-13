<?php
require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== VERIFICANDO CAMPO EstadoModulo ===\n\n";

    // Verificar si existe EstadoModulo (case insensitive)
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Campos actuales:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
        if (strtolower($col['Field']) == 'estadomodulo') {
            echo "  -> Este es el campo estadomodulo existente\n";
        }
    }

    // Intentar agregar EstadoModulo con E mayúscula
    echo "\n=== INTENTANDO AGREGAR EstadoModulo (con E mayúscula) ===\n";

    try {
        $sql = "ALTER TABLE modulos
                ADD COLUMN EstadoModulo VARCHAR(20) NOT NULL DEFAULT 'ABIERTO' AFTER DocenteID";
        $pdo->exec($sql);
        echo "✓ Campo EstadoModulo agregado exitosamente.\n";
    } catch (PDOException $e) {
        echo "Error al agregar EstadoModulo: " . $e->getMessage() . "\n";
    }

    echo "\n=== CAMPOS FINALES ===\n";
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo sprintf("%-25s %-20s %s\n",
            $col['Field'],
            $col['Type'],
            $col['Default'] ?? 'NULL'
        );
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
