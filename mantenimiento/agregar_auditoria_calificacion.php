<?php
/**
 * Script para agregar campos de auditoría a la tabla calificacion
 * Este script agrega el campo UsuarioRegistroID para rastrear quién registró las calificaciones
 */

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== AGREGANDO CAMPOS DE AUDITORÍA A TABLA CALIFICACION ===\n\n";

    // Verificar si el campo UsuarioRegistroID ya existe
    $stmt = $pdo->prepare("SHOW COLUMNS FROM calificacion LIKE 'UsuarioRegistroID'");
    $stmt->execute();
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "1. Agregando campo UsuarioRegistroID...\n";
        $pdo->exec("ALTER TABLE calificacion ADD COLUMN UsuarioRegistroID INT(11) NULL AFTER FechaRegistro");
        echo "   ✓ Campo UsuarioRegistroID agregado correctamente\n\n";
    } else {
        echo "1. El campo UsuarioRegistroID ya existe\n\n";
    }

    // Agregar índice para mejorar rendimiento
    echo "2. Verificando índice en UsuarioRegistroID...\n";
    $stmt = $pdo->prepare("SHOW INDEX FROM calificacion WHERE Column_name = 'UsuarioRegistroID'");
    $stmt->execute();
    $indexExists = $stmt->fetch();

    if (!$indexExists && !$exists) {
        $pdo->exec("ALTER TABLE calificacion ADD INDEX idx_usuario_registro (UsuarioRegistroID)");
        echo "   ✓ Índice agregado correctamente\n\n";
    } else {
        echo "   ✓ Índice ya existe\n\n";
    }

    // Nota: No se agrega clave foránea para evitar restricciones
    echo "3. Campo UsuarioRegistroID disponible (sin FK por flexibilidad)\n";

    echo "=== PROCESO COMPLETADO EXITOSAMENTE ===\n\n";
    echo "Estructura actualizada de la tabla calificacion:\n";

    $stmt = $pdo->query("DESCRIBE calificacion");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "\n";
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

    echo "\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
