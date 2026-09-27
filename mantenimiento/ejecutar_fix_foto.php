<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "<h1>Modificando tabla estudianteprograma</h1>";

try {
    $pdo = Conexion::Conectar();

    // Modificar columna foto para permitir NULL
    $sql = "ALTER TABLE estudianteprograma MODIFY COLUMN foto LONGBLOB NULL";

    echo "<p>Ejecutando: <code>$sql</code></p>";

    $pdo->exec($sql);

    echo "<div style='background: #e8f5e9; padding: 15px; margin: 10px 0; border-left: 5px solid #4CAF50;'>";
    echo "<h3>✓ ÉXITO</h3>";
    echo "<p>La columna 'foto' ahora permite valores NULL.</p>";
    echo "<p>Esto significa que el campo de imagen es opcional en el formulario.</p>";
    echo "</div>";

    // Verificar el cambio
    $stmt = $pdo->query("DESCRIBE estudianteprograma");
    echo "<h3>Estructura actual de la tabla:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $color = ($row['Field'] == 'foto') ? 'background: #ffffcc;' : '';
        echo "<tr style='$color'>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; margin: 10px 0; border-left: 5px solid #f44336;'>";
    echo "<h3>✗ ERROR</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php?ruta=inscripcion'>← Volver a Inscripción</a></p>";
?>
