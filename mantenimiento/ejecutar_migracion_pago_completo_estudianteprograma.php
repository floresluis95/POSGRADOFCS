<?php
/**
 * Script para ejecutar la migración de campos de pago completo
 * Tabla: estudianteprograma
 * Ejecutar una sola vez
 */

require_once 'modelos/conexion.modelo.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Migración: Pago Completo - EstudiantePrograma</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h2 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
h3 { color: #764ba2; margin-top: 30px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
.warning { color: orange; font-weight: bold; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
table th { background: #667eea; color: white; padding: 12px; text-align: left; }
table td { padding: 10px; border-bottom: 1px solid #ddd; }
.highlight { background: #d4edda; }
code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
.btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
.btn:hover { background: #764ba2; }
ol li { margin: 10px 0; }
hr { border: none; border-top: 2px solid #e0e0e0; margin: 30px 0; }
</style>";
echo "</head><body>";
echo "<div class='container'>";

echo "<h2>🔧 Migración: Agregar Campos de Pago Completo</h2>";
echo "<p><strong>Tabla:</strong> estudianteprograma</p>";
echo "<hr>";

try {
    $pdo = Conexion::Conectar();

    // Leer el archivo SQL
    $sqlFile = __DIR__ . '/bd/agregar_campos_pago_completo_estudianteprograma.sql';

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

    echo "<h3>📋 Consultas a ejecutar:</h3>";
    echo "<ol>";

    $exitosas = 0;
    $errores = 0;

    foreach ($queries as $query) {
        $queryPreview = substr($query, 0, 150) . (strlen($query) > 150 ? '...' : '');
        echo "<li><code>" . htmlspecialchars($queryPreview) . "</code><br>";

        try {
            $pdo->exec($query);
            echo "<span class='success'>✓ Ejecutada exitosamente</span></li>";
            $exitosas++;
        } catch (PDOException $e) {
            // Verificar si el error es porque la columna ya existe
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<span class='warning'>⚠ La columna ya existe (omitiendo)</span></li>";
                $exitosas++;
            } else {
                echo "<span class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></li>";
                $errores++;
            }
        }
    }

    echo "</ol>";
    echo "<hr>";

    // Verificar estructura de la tabla
    echo "<h3>🔍 Verificando estructura de la tabla 'estudianteprograma':</h3>";
    $stmt = $pdo->query("DESCRIBE estudianteprograma");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr>";
    echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th>";
    echo "</tr>";

    $camposNuevos = ['montoPagado', 'pagoCompleto'];

    foreach ($estructura as $campo) {
        $highlight = in_array($campo['Field'], $camposNuevos) ? "class='highlight'" : "";
        echo "<tr $highlight>";
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
    echo "📊 Resumen: $exitosas exitosas, $errores errores";
    echo "</h3>";

    if ($errores == 0) {
        echo "<div style='background: #d4edda; border: 2px solid #28a745; border-radius: 8px; padding: 20px; margin: 20px 0;'>";
        echo "<p style='color: green; font-size: 18px; margin: 0;'>";
        echo "✓ <strong>¡Migración completada exitosamente!</strong><br>";
        echo "✓ Los campos <strong>montoPagado</strong> y <strong>pagoCompleto</strong> han sido agregados.<br>";
        echo "✓ La tabla <strong>estudianteprograma</strong> está lista para registrar inscripciones.";
        echo "</p>";
        echo "</div>";

        echo "<h3>📝 Próximos Pasos:</h3>";
        echo "<ol>";
        echo "<li>Ir al módulo de <strong>Inscripción</strong></li>";
        echo "<li>Registrar un nuevo estudiante</li>";
        echo "<li>Probar el <strong>Pago Completo del Programa</strong></li>";
        echo "<li>Verificar que se guarden correctamente los datos</li>";
        echo "</ol>";

        echo "<a href='inscripcion' class='btn'>📝 Ir a Inscripción</a>";
        echo " ";
        echo "<a href='matriculados' class='btn'>👥 Ver Matriculados</a>";
    } else {
        echo "<div style='background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin: 20px 0;'>";
        echo "<p style='color: #856404; font-size: 16px; margin: 0;'>";
        echo "⚠ <strong>Hubo algunos errores durante la migración.</strong><br>";
        echo "Por favor, revisa los mensajes de error arriba.";
        echo "</p>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; border-radius: 8px; padding: 20px; margin: 20px 0;'>";
    echo "<p style='color: red; font-size: 16px;'>✗ <strong>Error fatal:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #999;'><small>Ejecutado el: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</div>";
echo "</body></html>";
?>
