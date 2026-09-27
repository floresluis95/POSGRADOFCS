<?php
/**
 * Script para ejecutar la creación de la tabla pagomodulo
 * Ejecutar desde: http://localhost/POSGRADOFCS/ejecutar_crear_tabla_pagomodulo.php
 */

// Leer el archivo SQL
$sqlFile = __DIR__ . '/bd/crear_tabla_pagomodulo.sql';

if (!file_exists($sqlFile)) {
    die("<h2 style='color: red;'>ERROR: No se encontró el archivo SQL en: $sqlFile</h2>");
}

$sql = file_get_contents($sqlFile);

// Conectar a la base de datos
require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>🗄️ Creación de Tabla PAGOMODULO</h1>";
    echo "<hr>";

    // Dividir las consultas SQL (por punto y coma)
    $queries = array_filter(
        array_map('trim', explode(';', $sql)),
        function($query) {
            return !empty($query) && substr(trim($query), 0, 2) !== '--';
        }
    );

    $successCount = 0;
    $errorCount = 0;

    echo "<h2>📋 Ejecutando consultas SQL...</h2>";
    echo "<div style='background: #f4f4f4; padding: 15px; border-radius: 5px;'>";

    foreach ($queries as $index => $query) {
        try {
            $conexion->exec($query);
            $successCount++;
            echo "<p style='color: green;'>✅ Consulta " . ($index + 1) . " ejecutada correctamente</p>";
        } catch (PDOException $e) {
            $errorCount++;
            // Ignorar error si la tabla ya existe
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "<p style='color: orange;'>⚠️ Consulta " . ($index + 1) . " - La tabla ya existe (ignorado)</p>";
                $successCount++;
                $errorCount--;
            } else {
                echo "<p style='color: red;'>❌ Error en consulta " . ($index + 1) . ": " . $e->getMessage() . "</p>";
            }
        }
    }

    echo "</div>";

    echo "<hr>";
    echo "<h2>📊 Resumen de Ejecución</h2>";
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 4px solid #4caf50;'>";
    echo "<p><strong>✅ Consultas exitosas:</strong> $successCount</p>";
    echo "<p><strong>❌ Consultas con error:</strong> $errorCount</p>";
    echo "</div>";

    // Verificar si la tabla se creó correctamente
    echo "<hr>";
    echo "<h2>🔍 Verificación de la Tabla</h2>";

    $checkTable = $conexion->query("SHOW TABLES LIKE 'pagomodulo'");
    if ($checkTable->rowCount() > 0) {
        echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 4px solid #4caf50;'>";
        echo "<h3 style='color: #4caf50;'>✅ ¡Tabla 'pagomodulo' creada exitosamente!</h3>";

        // Mostrar estructura de la tabla
        echo "<h4>Estructura de la tabla:</h4>";
        $columns = $conexion->query("DESCRIBE pagomodulo");
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<thead style='background: #4caf50; color: white;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th><th>Extra</th></tr>";
        echo "</thead><tbody>";

        while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td><strong>" . $column['Field'] . "</strong></td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . ($column['Key'] ? '<span style="color: orange;">' . $column['Key'] . '</span>' : '') . "</td>";
            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>";

        echo "<hr>";
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin-top: 20px;'>";
        echo "<h3>📋 Próximos Pasos:</h3>";
        echo "<ol>";
        echo "<li>La tabla <strong>pagomodulo</strong> está lista para usar</li>";
        echo "<li>Ir a la página de <strong>Matriculados</strong> en el sistema</li>";
        echo "<li>Usar el botón <strong>'Acciones' > 'Inscribir a Módulo'</strong></li>";
        echo "<li>Seleccionar un módulo disponible del programa</li>";
        echo "<li>Completar los datos de pago y guardar</li>";
        echo "</ol>";
        echo "<p style='margin-top: 15px;'><a href='matriculados' style='background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>🎯 Ir a Matriculados</a></p>";
        echo "</div>";

    } else {
        echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; border-left: 4px solid #f44336;'>";
        echo "<h3 style='color: #f44336;'>❌ Error: No se pudo crear la tabla 'pagomodulo'</h3>";
        echo "<p>Por favor, revisa los errores anteriores y vuelve a intentar.</p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; border-left: 4px solid #f44336;'>";
    echo "<h2 style='color: #f44336;'>❌ Error de Conexión</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #666; margin-top: 30px;'>";
echo "Script ejecutado el: " . date('d/m/Y H:i:s');
echo "</p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: #f5f5f5;
    }

    h1 {
        color: #333;
        border-bottom: 3px solid #4caf50;
        padding-bottom: 10px;
    }

    h2 {
        color: #555;
        margin-top: 25px;
    }

    table {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    th {
        text-align: left;
    }

    tr:nth-child(even) {
        background: #f9f9f9;
    }
</style>
