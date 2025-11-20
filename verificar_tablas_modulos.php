<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "<h1>Verificando Tablas de la Base de Datos</h1>";

try {
    $pdo = Conexion::Conectar();

    // Listar todas las tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "<h2>Todas las tablas:</h2>";
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li><strong>$tabla</strong>";

        // Si contiene 'modul' mostrar estructura
        if (stripos($tabla, 'modul') !== false) {
            echo " <span style='color: green;'>(TABLA DE MÓDULOS)</span>";
            $stmtDesc = $pdo->query("DESCRIBE $tabla");
            echo "<table border='1' cellpadding='5' style='margin: 10px 0; border-collapse: collapse;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = $stmtDesc->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "</li>";
    }
    echo "</ul>";

    // Verificar tabla estudianteprograma
    echo "<h2>Estructura de estudianteprograma:</h2>";
    $stmt = $pdo->query("DESCRIBE estudianteprograma");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}
?>
