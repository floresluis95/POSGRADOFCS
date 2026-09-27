<?php
/**
 * Script para agregar el campo EstadoModulo a la tabla modulos
 * Estados:
 * - ABIERTO: Módulo abierto para registro y edición de calificaciones
 * - VALIDADO: Módulo validado por el docente, cerrado para edición (solo admin puede editar)
 * - CERRADO: Módulo completamente cerrado (solo admin puede editar)
 */

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== AGREGANDO CAMPO EstadoModulo A TABLA modulos ===\n\n";

    // Verificar si el campo ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM modulos LIKE 'EstadoModulo'");
    $existe = $stmt->fetch();

    if ($existe) {
        echo "El campo EstadoModulo ya existe en la tabla modulos.\n";
    } else {
        // Agregar el campo EstadoModulo
        $sql = "ALTER TABLE modulos
                ADD COLUMN EstadoModulo VARCHAR(20) NOT NULL DEFAULT 'ABIERTO' AFTER estadomodulo";

        $pdo->exec($sql);
        echo "✓ Campo EstadoModulo agregado exitosamente a la tabla modulos.\n";
    }

    // Verificar si existe el campo ValidadoPor
    $stmt = $pdo->query("SHOW COLUMNS FROM modulos LIKE 'ValidadoPor'");
    $existeValidadoPor = $stmt->fetch();

    if ($existeValidadoPor) {
        echo "El campo ValidadoPor ya existe en la tabla modulos.\n";
    } else {
        // Agregar campo para registrar quién validó/cerró el módulo
        $sql = "ALTER TABLE modulos
                ADD COLUMN ValidadoPor INT(11) NULL AFTER EstadoModulo,
                ADD COLUMN FechaValidacion DATETIME NULL AFTER ValidadoPor";

        $pdo->exec($sql);
        echo "✓ Campos ValidadoPor y FechaValidacion agregados exitosamente.\n";
    }

    echo "\n=== ESTRUCTURA ACTUALIZADA ===\n";
    $stmt = $pdo->query("DESCRIBE modulos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo sprintf("%-25s %-20s %-5s %-5s %-15s %s\n",
            $col['Field'],
            $col['Type'],
            $col['Null'],
            $col['Key'],
            $col['Default'] ?? 'NULL',
            $col['Extra']
        );
    }

    echo "\n✓ Migración completada exitosamente.\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
