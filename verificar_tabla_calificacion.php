<?php
/**
 * Script de diagnóstico para verificar la tabla calificacion
 */

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    echo "=== VERIFICACIÓN DE TABLA CALIFICACION ===\n\n";

    // 1. Verificar estructura de la tabla
    echo "1. ESTRUCTURA DE LA TABLA:\n";
    $stmt = $pdo->prepare("DESCRIBE calificacion");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columns as $col) {
        echo "  - {$col['Field']}: {$col['Type']} | Null: {$col['Null']} | Key: {$col['Key']} | Default: {$col['Default']}\n";
    }

    // 2. Verificar índices y claves
    echo "\n2. ÍNDICES Y CLAVES:\n";
    $stmt = $pdo->prepare("SHOW KEYS FROM calificacion");
    $stmt->execute();
    $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($keys as $key) {
        echo "  - {$key['Key_name']}: Columna={$key['Column_name']}, Tipo={$key['Index_type']}, Unique=" . ($key['Non_unique'] == 0 ? 'Sí' : 'No') . "\n";
    }

    // 3. Verificar si existe la clave única necesaria
    echo "\n3. VERIFICACIÓN DE CLAVE ÚNICA:\n";
    $hasUniqueKey = false;
    foreach ($keys as $key) {
        if ($key['Non_unique'] == 0 && in_array($key['Column_name'], ['EstudianteID', 'ProgramaId', 'Idmodulo'])) {
            $hasUniqueKey = true;
            echo "  ✓ Encontrada clave única en columna: {$key['Column_name']}\n";
        }
    }

    if (!$hasUniqueKey) {
        echo "  ✗ NO SE ENCONTRÓ CLAVE ÚNICA COMPUESTA (EstudianteID, ProgramaId, Idmodulo)\n";
        echo "  ⚠ ESTO ES NECESARIO PARA QUE ON DUPLICATE KEY UPDATE FUNCIONE\n\n";

        echo "4. SOLUCIÓN SUGERIDA:\n";
        echo "   Ejecutar el siguiente SQL para crear la clave única:\n\n";
        echo "   ALTER TABLE calificacion\n";
        echo "   ADD UNIQUE KEY unique_calificacion (EstudianteID, ProgramaId, Idmodulo);\n\n";
    } else {
        echo "  ✓ La tabla tiene la estructura correcta para ON DUPLICATE KEY UPDATE\n";
    }

    // 4. Contar registros
    echo "\n5. REGISTROS EXISTENTES:\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM calificacion");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Total de calificaciones: {$count['total']}\n";

    // 5. Mostrar últimas 5 calificaciones
    if ($count['total'] > 0) {
        echo "\n6. ÚLTIMAS 5 CALIFICACIONES:\n";
        $stmt = $pdo->prepare("
            SELECT c.*, e.Nombre, e.Apaterno
            FROM calificacion c
            LEFT JOIN estudiante e ON c.EstudianteID = e.EstudianteID
            ORDER BY c.FechaRegistro DESC
            LIMIT 5
        ");
        $stmt->execute();
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($recent as $cal) {
            echo "  - Estudiante: {$cal['Nombre']} {$cal['Apaterno']} | Nota: {$cal['Nota']} | Fecha: {$cal['FechaRegistro']}\n";
        }
    }

    echo "\n=== FIN DE VERIFICACIÓN ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
