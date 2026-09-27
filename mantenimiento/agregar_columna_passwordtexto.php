<?php
/**
 * Script para agregar columna PasswordTexto a la tabla usuario
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    // Verificar si la columna ya existe
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuario LIKE 'PasswordTexto'");
    $stmt->execute();
    $existe = $stmt->fetch();

    if ($existe) {
        echo "✓ La columna 'PasswordTexto' ya existe en la tabla 'usuario'.\n";
    } else {
        // Agregar la columna
        $sql = "ALTER TABLE usuario ADD COLUMN PasswordTexto VARCHAR(50) DEFAULT NULL AFTER Password";
        $conexion->exec($sql);
        echo "✓ Columna 'PasswordTexto' agregada exitosamente a la tabla 'usuario'.\n";
    }

    echo "\n=== Estructura actual de la tabla usuario ===\n";
    $stmt = $conexion->prepare("DESCRIBE usuario");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columnas as $columna) {
        echo sprintf("%-20s %-20s %-10s %-10s\n",
            $columna['Field'],
            $columna['Type'],
            $columna['Null'],
            $columna['Default']
        );
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
