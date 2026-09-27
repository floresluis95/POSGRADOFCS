<?php
/**
 * Script para agregar campos de auditoría de modificaciones
 * a la tabla calificacion
 */

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Agregando Campos de Auditoría de Modificaciones ===\n\n";

    // 1. Verificar y agregar UsuarioModificacionID
    $stmt = $pdo->query("SHOW COLUMNS FROM calificacion LIKE 'UsuarioModificacionID'");
    if ($stmt->rowCount() == 0) {
        echo "Agregando campo UsuarioModificacionID...\n";
        $pdo->exec("ALTER TABLE calificacion ADD COLUMN UsuarioModificacionID INT(11) NULL AFTER UsuarioRegistroID");
        echo "✓ Campo UsuarioModificacionID agregado exitosamente\n\n";
    } else {
        echo "✓ Campo UsuarioModificacionID ya existe\n\n";
    }

    // 2. Verificar y agregar FechaModificacion
    $stmt = $pdo->query("SHOW COLUMNS FROM calificacion LIKE 'FechaModificacion'");
    if ($stmt->rowCount() == 0) {
        echo "Agregando campo FechaModificacion...\n";
        $pdo->exec("ALTER TABLE calificacion ADD COLUMN FechaModificacion DATETIME NULL AFTER UsuarioModificacionID");
        echo "✓ Campo FechaModificacion agregado exitosamente\n\n";
    } else {
        echo "✓ Campo FechaModificacion ya existe\n\n";
    }

    // 3. Crear índice para UsuarioModificacionID si no existe
    $stmt = $pdo->query("SHOW INDEX FROM calificacion WHERE Key_name = 'idx_usuario_modificacion'");
    if ($stmt->rowCount() == 0) {
        echo "Creando índice idx_usuario_modificacion...\n";
        $pdo->exec("ALTER TABLE calificacion ADD INDEX idx_usuario_modificacion (UsuarioModificacionID)");
        echo "✓ Índice idx_usuario_modificacion creado exitosamente\n\n";
    } else {
        echo "✓ Índice idx_usuario_modificacion ya existe\n\n";
    }

    // 4. Verificar estructura final
    echo "=== Estructura actual de campos de auditoría en tabla calificacion ===\n\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM calificacion WHERE Field IN ('UsuarioRegistroID', 'UsuarioModificacionID', 'FechaRegistro', 'FechaModificacion')");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fields as $field) {
        echo "Campo: {$field['Field']}\n";
        echo "  Tipo: {$field['Type']}\n";
        echo "  Null: {$field['Null']}\n";
        echo "  Default: {$field['Default']}\n\n";
    }

    echo "=== Proceso completado exitosamente ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
