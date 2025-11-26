<?php
/**
 * Script FINAL para modificar la tabla usuario
 * Desactiva foreign keys temporalmente
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Modificando estructura de tabla usuario...</h2>";

    // Desactivar verificación de foreign keys temporalmente
    echo "<p>Desactivando verificación de claves foráneas...</p>";
    $conexion->exec("SET FOREIGN_KEY_CHECKS=0");
    echo "<p style='color: green;'>✓ Foreign keys desactivadas</p>";

    // Eliminar PRIMARY KEY
    echo "<p>Eliminando PRIMARY KEY de IdPersonal...</p>";
    try {
        $conexion->exec("ALTER TABLE `usuario` DROP PRIMARY KEY");
        echo "<p style='color: green;'>✓ PRIMARY KEY eliminada</p>";
    } catch (PDOException $e) {
        echo "<p style='color: orange;'>⚠ " . $e->getMessage() . "</p>";
    }

    // Agregar campo ID como PRIMARY KEY
    echo "<p>Agregando campo ID autoincrementable...</p>";
    try {
        $conexion->exec("ALTER TABLE `usuario` ADD COLUMN `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        echo "<p style='color: green;'>✓ Campo ID agregado</p>";
    } catch (PDOException $e) {
        // Si el campo ya existe, lo modificamos para que sea PRIMARY KEY
        echo "<p style='color: orange;'>Campo ID ya existe, modificando...</p>";
        try {
            $conexion->exec("ALTER TABLE `usuario` MODIFY COLUMN `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
            echo "<p style='color: green;'>✓ Campo ID modificado</p>";
        } catch (PDOException $e2) {
            echo "<p style='color: orange;'>⚠ " . $e2->getMessage() . "</p>";
        }
    }

    // Modificar IdPersonal para permitir NULL
    echo "<p>Modificando IdPersonal para permitir NULL...</p>";
    $conexion->exec("ALTER TABLE `usuario` MODIFY COLUMN `IdPersonal` INT(11) NULL DEFAULT NULL");
    echo "<p style='color: green;'>✓ IdPersonal ahora permite NULL</p>";

    // Reactivar verificación de foreign keys
    echo "<p>Reactivando verificación de claves foráneas...</p>";
    $conexion->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "<p style='color: green;'>✓ Foreign keys reactivadas</p>";

    echo "<div style='color: green; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✓ Modificación completada</h3>";
    echo "</div>";

    // Verificar estructura
    echo "<h3>Estructura final de la tabla usuario:</h3>";
    $stmt = $conexion->query("DESCRIBE usuario");
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 20px 0; width: 100%;'>";
    echo "<tr style='background: #667eea; color: white;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";

    $idPersonalIsNull = false;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Field'] == 'IdPersonal' && $row['Null'] == 'YES') {
            $idPersonalIsNull = true;
        }

        $bgColor = '';
        if ($row['Field'] == 'ID') $bgColor = 'background: #e3f2fd;';
        if ($row['Field'] == 'IdPersonal' && $row['Null'] == 'YES') $bgColor = 'background: #d4edda;';
        if ($row['Field'] == 'EstudianteID') $bgColor = 'background: #d4edda;';

        echo "<tr style='$bgColor'>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td style='color: " . ($row['Null'] == 'YES' ? 'green' : 'red') . "; font-weight: bold;'>" . $row['Null'] . "</td>";
        echo "<td>" . ($row['Key'] ?: '-') . "</td>";
        echo "<td>" . ($row['Default'] !== null ? $row['Default'] : 'NULL') . "</td>";
        echo "<td>" . ($row['Extra'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    if ($idPersonalIsNull) {
        echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #28a745; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #28a745;'>✅ ¡ÉXITO! La tabla está lista</h3>";
        echo "<p><strong>IdPersonal ahora permite NULL</strong></p>";
        echo "<ul>";
        echo "<li>Usuarios de personal: IdPersonal tiene valor, EstudianteID es NULL</li>";
        echo "<li>Usuarios de estudiantes: EstudianteID tiene valor, IdPersonal es NULL</li>";
        echo "</ul>";
        echo "<a href='estudiantes' style='display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; font-weight: bold;'>Ir a Gestión de Estudiantes</a>";
        echo "</div>";
    } else {
        echo "<div style='padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='color: #dc3545;'>⚠ IdPersonal aún no permite NULL</h3>";
        echo "<p>Puede que necesites ejecutar el comando SQL manualmente en phpMyAdmin:</p>";
        echo "<code style='display: block; background: #f5f5f5; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "SET FOREIGN_KEY_CHECKS=0;<br>";
        echo "ALTER TABLE `usuario` DROP PRIMARY KEY;<br>";
        echo "ALTER TABLE `usuario` ADD COLUMN `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;<br>";
        echo "ALTER TABLE `usuario` MODIFY COLUMN `IdPersonal` INT(11) NULL DEFAULT NULL;<br>";
        echo "SET FOREIGN_KEY_CHECKS=1;";
        echo "</code>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div style='color: red; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✗ Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
