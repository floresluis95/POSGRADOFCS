<?php
/**
 * Script para modificar la tabla usuario (Versión 2)
 * Elimina PRIMARY KEY de IdPersonal y permite NULL
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Modificando estructura de tabla usuario...</h2>";

    // Paso 1: Eliminar la PRIMARY KEY actual
    echo "<p>Paso 1: Eliminando PRIMARY KEY...</p>";
    try {
        $conexion->exec("ALTER TABLE `usuario` DROP PRIMARY KEY");
        echo "<p style='color: green;'>✓ PRIMARY KEY eliminada</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠ No se pudo eliminar PRIMARY KEY (puede que no exista): " . $e->getMessage() . "</p>";
    }

    // Paso 2: Agregar un campo ID autoincrementable como nueva PRIMARY KEY
    echo "<p>Paso 2: Agregando campo ID autoincrementable...</p>";
    try {
        $conexion->exec("ALTER TABLE `usuario` ADD COLUMN `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        echo "<p style='color: green;'>✓ Campo ID agregado como PRIMARY KEY</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠ Campo ID ya existe o error: " . $e->getMessage() . "</p>";
    }

    // Paso 3: Modificar IdPersonal para permitir NULL
    echo "<p>Paso 3: Modificando columna IdPersonal para permitir NULL...</p>";
    $conexion->exec("ALTER TABLE `usuario` MODIFY COLUMN `IdPersonal` INT(11) NULL DEFAULT NULL");
    echo "<p style='color: green;'>✓ IdPersonal ahora permite NULL</p>";

    echo "<div style='color: green; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✓ Tabla 'usuario' modificada exitosamente</h3>";
    echo "<p><strong>Cambios realizados:</strong></p>";
    echo "<ul>";
    echo "<li>Se agregó un campo 'ID' autoincrementable como PRIMARY KEY</li>";
    echo "<li>La columna 'IdPersonal' ahora permite valores NULL</li>";
    echo "<li>La columna 'EstudianteID' permite valores NULL</li>";
    echo "</ul>";
    echo "</div>";

    // Verificar la estructura actualizada
    echo "<h3>Estructura actualizada de la tabla usuario:</h3>";
    $stmt = $conexion->query("DESCRIBE usuario");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #667eea; color: white;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $color = 'black';
        if ($row['Field'] == 'ID' && $row['Key'] == 'PRI') {
            $color = 'blue';
        } elseif ($row['Field'] == 'IdPersonal' && $row['Null'] == 'YES') {
            $color = 'green';
        } elseif ($row['Field'] == 'EstudianteID' && $row['Null'] == 'YES') {
            $color = 'green';
        }

        echo "<tr style='color: $color;'>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td style='color: " . ($row['Null'] == 'YES' ? 'green' : 'red') . ";'><strong>" . $row['Null'] . "</strong></td>";
        echo "<td>" . ($row['Key'] ?: '-') . "</td>";
        echo "<td>" . ($row['Default'] !== null ? $row['Default'] : 'NULL') . "</td>";
        echo "<td>" . ($row['Extra'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div style='padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>📋 Nueva estructura:</h4>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> Clave primaria autoincrementable</li>";
    echo "<li><strong>Usuarios de personal:</strong> IdPersonal tiene valor, EstudianteID es NULL</li>";
    echo "<li><strong>Usuarios de estudiantes:</strong> EstudianteID tiene valor, IdPersonal es NULL</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div style='padding: 15px; background: #d1ecf1; border: 1px solid #17a2b8; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>✅ Listo para usar:</h4>";
    echo "<p>Ahora puedes asignar usuarios a los estudiantes sin problemas. El sistema diferenciará automáticamente:</p>";
    echo "<ul>";
    echo "<li>Personal: tendrá IdPersonal poblado</li>";
    echo "<li>Estudiantes: tendrá EstudianteID poblado</li>";
    echo "</ul>";
    echo "</div>";

    echo "<a href='estudiantes' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;'>Ir a Gestión de Estudiantes</a>";

} catch (PDOException $e) {
    echo "<div style='color: red; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✗ Error al modificar la tabla</h3>";
    echo "<p><strong>Mensaje de error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Sugerencia:</strong> Verifica que no haya índices únicos o restricciones adicionales en la tabla.</p>";
    echo "</div>";
}
?>
