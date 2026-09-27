<?php
/**
 * Script para ejecutar la migración de campos de docentes extranjeros
 * Ejecutar una sola vez
 */

require_once 'modelos/conexion.modelo.php';

echo "<h2>Ejecutando migración: Agregar campos de docentes extranjeros</h2>";
echo "<hr>";

try {
    $pdo = Conexion::Conectar();

    // Leer el archivo SQL
    $sqlFile = __DIR__ . '/bd/agregar_campos_extranjero_docente.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("No se encontró el archivo SQL: " . $sqlFile);
    }

    $sql = file_get_contents($sqlFile);

    // Dividir las consultas
    $queries = array_filter(
        array_map('trim', explode(';', $sql)),
        function($query) {
            // Filtrar comentarios y líneas vacías
            return !empty($query) &&
                   !preg_match('/^--/', $query) &&
                   !preg_match('/^USE/', $query) &&
                   !preg_match('/^DESCRIBE/', $query);
        }
    );

    echo "<h3>Consultas a ejecutar:</h3>";
    echo "<ol>";

    $exitosas = 0;
    $errores = 0;

    foreach ($queries as $query) {
        $queryPreview = substr($query, 0, 100) . (strlen($query) > 100 ? '...' : '');
        echo "<li><code>" . htmlspecialchars($queryPreview) . "</code><br>";

        try {
            $pdo->exec($query);
            echo "<span style='color: green;'>✓ Ejecutada exitosamente</span></li>";
            $exitosas++;
        } catch (PDOException $e) {
            // Verificar si el error es porque la columna ya existe
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<span style='color: orange;'>⚠ La columna ya existe (omitiendo)</span></li>";
                $exitosas++;
            } else {
                echo "<span style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></li>";
                $errores++;
            }
        }
    }

    echo "</ol>";
    echo "<hr>";

    // Verificar estructura de la tabla
    echo "<h3>Verificando estructura de la tabla 'docente':</h3>";
    $stmt = $pdo->query("DESCRIBE docente");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #667eea; color: white;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th>";
    echo "</tr>";

    $camposExtranjero = ['EsExtranjero', 'Pais', 'Region'];

    foreach ($estructura as $campo) {
        $highlight = in_array($campo['Field'], $camposExtranjero) ? "background: #d4edda;" : "";
        echo "<tr style='$highlight'>";
        echo "<td><strong>" . htmlspecialchars($campo['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<hr>";
    echo "<h3 style='color: " . ($errores > 0 ? "orange" : "green") . ";'>";
    echo "Resumen: $exitosas exitosas, $errores errores";
    echo "</h3>";

    if ($errores == 0) {
        echo "<p style='color: green; font-size: 18px;'>";
        echo "✓ Migración completada exitosamente!<br>";
        echo "✓ Los campos <strong>EsExtranjero</strong>, <strong>Pais</strong> y <strong>Region</strong> han sido agregados.";
        echo "</p>";
        echo "<p><a href='docentes' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Ir a Docentes</a></p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error fatal: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><small>Ejecutado el: " . date('Y-m-d H:i:s') . "</small></p>";
?>
