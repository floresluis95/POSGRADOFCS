<?php
/**
 * Verificar estructura de la tabla programa y valores de Estado
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    echo "===== ESTRUCTURA DE LA TABLA PROGRAMA =====\n\n";

    // Mostrar estructura de la tabla
    $stmt = $conexion->prepare("DESCRIBE programa");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columnas as $col) {
        if ($col['Field'] == 'Estado') {
            echo "COLUMNA ESTADO:\n";
            echo "  Tipo: " . $col['Type'] . "\n";
            echo "  Nulo: " . $col['Null'] . "\n";
            echo "  Default: " . $col['Default'] . "\n";
            echo "  Extra: " . $col['Extra'] . "\n\n";
        }
    }

    echo "===== VALORES ACTUALES DE ESTADO EN PROGRAMAS =====\n\n";

    // Verificar valores actuales
    $stmt = $conexion->prepare("SELECT ProgramaID, NombrePrograma, Estado FROM programa");
    $stmt->execute();
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programas as $prog) {
        $estadoValue = var_export($prog['Estado'], true);
        $estadoType = gettype($prog['Estado']);
        echo sprintf("ID: %-3s | %-45s | Estado: %-15s | Tipo: %s\n",
            $prog['ProgramaID'],
            $prog['NombrePrograma'],
            $estadoValue,
            $estadoType
        );
    }

    echo "\n===== RESUMEN DE ESTADOS =====\n";
    $stmt = $conexion->prepare("SELECT Estado, COUNT(*) as Total FROM programa GROUP BY Estado");
    $stmt->execute();
    $resumen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resumen as $res) {
        echo "Estado '" . var_export($res['Estado'], true) . "': " . $res['Total'] . " programas\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
