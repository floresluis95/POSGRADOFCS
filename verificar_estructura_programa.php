<?php
/**
 * Script para verificar la estructura de la tabla programa
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "=== ESTRUCTURA DE LA TABLA PROGRAMA ===\n\n";

try {
    $conexion = Conexion::Conectar();

    // Mostrar estructura de la tabla
    echo "1. Estructura de la tabla 'programa':\n";
    $stmt = $conexion->query("DESCRIBE programa");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columnas as $columna) {
        echo "   - " . $columna['Field'] . " (" . $columna['Type'] . ") ";
        echo "NULL: " . $columna['Null'] . ", ";
        echo "Default: " . ($columna['Default'] ?? 'NULL') . "\n";
    }

    // Mostrar valores actuales del campo Estado
    echo "\n2. Valores actuales del campo 'Estado':\n";
    $stmt = $conexion->query("SELECT ProgramaID, NombrePrograma, Estado,
                              LENGTH(Estado) as longitud,
                              HEX(Estado) as hex_value
                              FROM programa");
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programas as $programa) {
        echo "   ID " . $programa['ProgramaID'] . ": ";
        echo "Estado = '" . $programa['Estado'] . "' ";
        echo "(Longitud: " . $programa['longitud'] . ", ";
        echo "HEX: " . $programa['hex_value'] . ")\n";
    }

    // Verificar qué tipo de comparación funciona
    echo "\n3. Probando consultas con diferentes condiciones:\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM programa WHERE Estado = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - WHERE Estado = 1: " . $result['total'] . " registros\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM programa WHERE Estado = '1'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - WHERE Estado = '1': " . $result['total'] . " registros\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM programa WHERE Estado = 'ACTIVO'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - WHERE Estado = 'ACTIVO': " . $result['total'] . " registros\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM programa");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   - WHERE sin condición: " . $result['total'] . " registros\n";

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN ===\n";
?>
