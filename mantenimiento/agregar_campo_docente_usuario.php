<?php
/**
 * Script para agregar el campo DocenteID a la tabla usuario
 * Ejecutar una sola vez
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    echo "<h2>Modificando tabla usuario...</h2>";

    // Verificar si el campo DocenteID ya existe
    $stmt = $conexion->prepare("SHOW COLUMNS FROM usuario LIKE 'DocenteID'");
    $stmt->execute();
    $existe = $stmt->fetch();

    if ($existe) {
        echo "<p style='color: orange;'>✓ El campo DocenteID ya existe en la tabla usuario.</p>";
    } else {
        // Agregar campo DocenteID después de EstudianteID
        $sql = "ALTER TABLE `usuario`
                ADD COLUMN `DocenteID` INT(11) NULL DEFAULT NULL AFTER `EstudianteID`,
                ADD INDEX `idx_docente` (`DocenteID`)";

        $conexion->exec($sql);
        echo "<p style='color: green;'>✓ Campo DocenteID agregado exitosamente.</p>";
    }

    // Verificar si existe la clave foránea
    $stmt = $conexion->prepare("SELECT CONSTRAINT_NAME
                                FROM information_schema.KEY_COLUMN_USAGE
                                WHERE TABLE_NAME = 'usuario'
                                AND COLUMN_NAME = 'DocenteID'
                                AND CONSTRAINT_NAME LIKE 'fk_%'");
    $stmt->execute();
    $existeFK = $stmt->fetch();

    if (!$existeFK) {
        // Agregar clave foránea si no existe
        $sql = "ALTER TABLE `usuario`
                ADD CONSTRAINT `fk_usuario_docente`
                FOREIGN KEY (`DocenteID`) REFERENCES `docente`(`DocenteID`)
                ON DELETE CASCADE ON UPDATE CASCADE";

        try {
            $conexion->exec($sql);
            echo "<p style='color: green;'>✓ Clave foránea agregada exitosamente.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>⚠ Advertencia FK: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>✓ La clave foránea ya existe.</p>";
    }

    // Mostrar estructura actualizada de la tabla
    echo "<h3>Estructura actual de la tabla usuario:</h3>";
    $stmt = $conexion->prepare("DESCRIBE usuario");
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
