<?php
/**
 * Script para agregar campos Version y NumeroTramite a la tabla programa
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "<h2>AGREGAR CAMPOS A TABLA PROGRAMA</h2>";

try {
    $conexion = Conexion::Conectar();

    echo "<h3>1. Verificando estructura actual de la tabla programa...</h3>";
    $stmt = $conexion->query("DESCRIBE programa");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";

    // Verificar si los campos ya existen
    $camposExistentes = array_column($columnas, 'Field');

    $versionExiste = in_array('Version', $camposExistentes);
    $tramiteExiste = in_array('NumeroTramite', $camposExistentes);

    echo "<h3>2. Agregando campos nuevos...</h3>";

    if (!$versionExiste) {
        echo "<p>→ Agregando campo <strong>Version</strong>...</p>";
        $sql = "ALTER TABLE programa ADD COLUMN Version VARCHAR(10) NULL DEFAULT 'V-1' AFTER Estado";
        $conexion->exec($sql);
        echo "<p style='color: green;'>✓ Campo Version agregado correctamente</p>";
    } else {
        echo "<p style='color: orange;'>⚠ El campo Version ya existe</p>";
    }

    if (!$tramiteExiste) {
        echo "<p>→ Agregando campo <strong>NumeroTramite</strong>...</p>";
        $sql = "ALTER TABLE programa ADD COLUMN NumeroTramite VARCHAR(50) NULL AFTER Version";
        $conexion->exec($sql);
        echo "<p style='color: green;'>✓ Campo NumeroTramite agregado correctamente</p>";
    } else {
        echo "<p style='color: orange;'>⚠ El campo NumeroTramite ya existe</p>";
    }

    echo "<h3>3. Verificando estructura actualizada...</h3>";
    $stmt = $conexion->query("DESCRIBE programa");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    foreach ($columnas as $col) {
        $resaltado = ($col['Field'] == 'Version' || $col['Field'] == 'NumeroTramite') ? "background: #90EE90;" : "";
        echo "<tr style='$resaltado'>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";

    echo "<h3 style='color: green;'>✓ PROCESO COMPLETADO EXITOSAMENTE</h3>";
    echo "<p><a href='index.php?action=programas'>Ir al módulo de Programas</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
