<?php
/**
 * Script para modificar la tabla usuario
 * Permite NULL en IdPersonal para usuarios de estudiantes
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Modificando estructura de tabla usuario...</h2>";

    // Modificar la columna IdPersonal para permitir NULL
    $sql = "ALTER TABLE `usuario` MODIFY COLUMN `IdPersonal` INT(11) NULL DEFAULT NULL";

    $conexion->exec($sql);

    echo "<div style='color: green; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✓ Tabla 'usuario' modificada exitosamente</h3>";
    echo "<p><strong>Cambio realizado:</strong></p>";
    echo "<ul>";
    echo "<li>La columna 'IdPersonal' ahora permite valores NULL</li>";
    echo "<li>Esto permite tener usuarios para personal (IdPersonal con valor) y usuarios para estudiantes (EstudianteID con valor)</li>";
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
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td style='color: " . ($row['Null'] == 'YES' ? 'green' : 'red') . ";'><strong>" . $row['Null'] . "</strong></td>";
        echo "<td>" . ($row['Key'] ?: '-') . "</td>";
        echo "<td>" . ($row['Default'] ?: 'NULL') . "</td>";
        echo "<td>" . ($row['Extra'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div style='padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>⚠️ Importante:</h4>";
    echo "<ul>";
    echo "<li>Usuarios de personal: IdPersonal tiene valor, EstudianteID es NULL</li>";
    echo "<li>Usuarios de estudiantes: EstudianteID tiene valor, IdPersonal es NULL</li>";
    echo "<li>Ahora puedes asignar usuarios a los estudiantes sin problemas</li>";
    echo "</ul>";
    echo "</div>";

    echo "<a href='estudiantes' style='display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;'>Ir a Gestión de Estudiantes</a>";

} catch (PDOException $e) {
    echo "<div style='color: red; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✗ Error al modificar la tabla</h3>";
    echo "<p><strong>Mensaje de error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
