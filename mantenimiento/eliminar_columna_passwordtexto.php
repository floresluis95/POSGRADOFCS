<?php
/**
 * Script para ELIMINAR la columna PasswordTexto de la tabla usuario.
 *
 * Por seguridad ya no se almacena la contraseña en texto plano: solo se
 * conserva el hash (columna Password) y la contraseña en claro se muestra
 * una única vez al crearla o restablecerla. Ejecutar una sola vez.
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    // Verificar si la columna existe
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuario LIKE 'PasswordTexto'");
    $stmt->execute();
    $existe = $stmt->fetch();

    if (!$existe) {
        echo "✓ La columna 'PasswordTexto' ya no existe en la tabla 'usuario'.\n";
    } else {
        $conexion->exec("ALTER TABLE usuario DROP COLUMN PasswordTexto");
        echo "✓ Columna 'PasswordTexto' eliminada exitosamente de la tabla 'usuario'.\n";
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
