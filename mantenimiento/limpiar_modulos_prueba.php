<?php
/**
 * Limpiar módulos de prueba
 */

require_once __DIR__ . '/modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    // Eliminar los módulos de prueba (IDs 30 y 31)
    $stmt = $pdo->prepare("DELETE FROM modulos WHERE Idmodulo IN (30, 31)");
    $stmt->execute();

    echo "✓ Módulos de prueba eliminados correctamente (IDs 30, 31)<br>";
    echo "Ahora puedes probar el registro desde la interfaz.";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
