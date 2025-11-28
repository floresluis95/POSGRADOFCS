<?php
/**
 * Script para verificar y agregar el campo Estado a la tabla docente si no existe
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    echo "<h2>Verificando tabla docente...</h2>";

    // Verificar si el campo Estado ya existe
    $stmt = $conexion->prepare("SHOW COLUMNS FROM docente LIKE 'Estado'");
    $stmt->execute();
    $existe = $stmt->fetch();

    if ($existe) {
        echo "<p style='color: orange;'>✓ El campo Estado ya existe en la tabla docente.</p>";
    } else {
        // Agregar campo Estado
        $sql = "ALTER TABLE `docente`
                ADD COLUMN `Estado` CHAR(1) NOT NULL DEFAULT '1' AFTER `Cel`";

        $conexion->exec($sql);
        echo "<p style='color: green;'>✓ Campo Estado agregado exitosamente a la tabla docente.</p>";
    }

    // Mostrar estructura actualizada de la tabla
    echo "<h3>Estructura actual de la tabla docente:</h3>";
    $stmt = $conexion->prepare("DESCRIBE docente");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3 style='color: green;'>✓ Proceso completado exitosamente</h3>";
    echo "<p><a href='docentes'>Ir a gestión de docentes</a></p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
