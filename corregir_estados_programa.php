<?php
/**
 * Corregir estados de programas para usar formato numérico consistente
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    echo "===== CORRECCIÓN DE ESTADOS EN PROGRAMAS =====\n\n";

    // 1. Actualizar registros con 'ACTIVO' a '1'
    echo "1. Actualizando programas con Estado = 'ACTIVO' a '1'...\n";
    $stmt = $conexion->prepare("UPDATE programa SET Estado = '1' WHERE Estado = 'ACTIVO'");
    $stmt->execute();
    $actualizados = $stmt->rowCount();
    echo "   ✓ $actualizados programas actualizados\n\n";

    // 2. Actualizar registros con 'INACTIVO' a '0' (si existen)
    echo "2. Actualizando programas con Estado = 'INACTIVO' a '0'...\n";
    $stmt = $conexion->prepare("UPDATE programa SET Estado = '0' WHERE Estado = 'INACTIVO'");
    $stmt->execute();
    $actualizados = $stmt->rowCount();
    echo "   ✓ $actualizados programas actualizados\n\n";

    // 3. Cambiar el valor por defecto de la columna
    echo "3. Cambiando valor por defecto de la columna Estado a '1'...\n";
    $stmt = $conexion->prepare("ALTER TABLE programa ALTER COLUMN Estado SET DEFAULT '1'");
    $stmt->execute();
    echo "   ✓ Valor por defecto actualizado\n\n";

    // 4. Verificar resultado
    echo "4. Verificando resultado final...\n";
    $stmt = $conexion->prepare("SELECT Estado, COUNT(*) as Total FROM programa GROUP BY Estado ORDER BY Estado");
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultado as $res) {
        $etiqueta = $res['Estado'] == '1' ? 'ACTIVO' : ($res['Estado'] == '0' ? 'INACTIVO' : 'OTRO');
        echo "   Estado '{$res['Estado']}' ($etiqueta): {$res['Total']} programas\n";
    }

    echo "\n===== CORRECCIÓN COMPLETADA =====\n";
    echo "Todos los programas ahora usan:\n";
    echo "  - Estado = '1' para ACTIVO\n";
    echo "  - Estado = '0' para INACTIVO\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
