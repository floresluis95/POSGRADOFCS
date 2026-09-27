<?php
/**
 * Script SIMPLE para agregar campos manualmente
 * Muestra errores detallados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'>";
echo "<title>Agregar Campos - MANUAL</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
.container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
h2 { color: #667eea; }
.success { background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 10px 0; border-radius: 5px; }
.error { background: #f8d7da; border: 2px solid #dc3545; padding: 15px; margin: 10px 0; border-radius: 5px; color: #721c24; }
.info { background: #d1ecf1; border: 2px solid #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }
.warning { background: #fff3cd; border: 2px solid #856404; padding: 15px; margin: 10px 0; border-radius: 5px; }
pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
.step { background: #e7f3ff; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
table th { background: #667eea; color: white; padding: 10px; text-align: left; }
table td { padding: 8px; border-bottom: 1px solid #ddd; }
.highlight { background: #ffffcc; font-weight: bold; }
</style>";
echo "</head><body>";
echo "<div class='container'>";

echo "<h2>🔧 Agregar Campos a estudianteprograma - MANUAL</h2>";
echo "<hr>";

try {
    // PASO 1: Conectar
    echo "<div class='step'>";
    echo "<h3>PASO 1: Conectando a la base de datos...</h3>";
    $pdo = Conexion::Conectar();

    // Obtener nombre de la BD
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $dbName = $result['db'];

    echo "<div class='success'>✓ Conexión exitosa a la base de datos: <strong>{$dbName}</strong></div>";
    echo "</div>";

    // PASO 2: Verificar tabla
    echo "<div class='step'>";
    echo "<h3>PASO 2: Verificando tabla 'estudianteprograma'...</h3>";

    $stmt = $pdo->query("SHOW TABLES LIKE 'estudianteprograma'");
    if ($stmt->rowCount() == 0) {
        echo "<div class='error'>❌ ERROR: La tabla 'estudianteprograma' NO existe en la base de datos '{$dbName}'</div>";
        echo "</div></div></body></html>";
        exit;
    }

    echo "<div class='success'>✓ Tabla 'estudianteprograma' encontrada</div>";

    // Mostrar estructura actual
    echo "<h4>Estructura ANTES:</h4>";
    $stmt = $pdo->query("DESCRIBE estudianteprograma");
    $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";
    foreach ($campos as $campo) {
        echo "<tr>";
        echo "<td>{$campo['Field']}</td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // PASO 3: Agregar montoPagado
    echo "<div class='step'>";
    echo "<h3>PASO 3: Agregando campo 'montoPagado'...</h3>";

    $sql1 = "ALTER TABLE `estudianteprograma`
             ADD COLUMN `montoPagado` DECIMAL(10,2) NOT NULL DEFAULT 0.00
             COMMENT 'Monto total pagado'
             AFTER `costomatricula`";

    echo "<pre>" . htmlspecialchars($sql1) . "</pre>";

    try {
        $pdo->exec($sql1);
        echo "<div class='success'>✓ Campo 'montoPagado' agregado exitosamente</div>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<div class='warning'>⚠ El campo 'montoPagado' ya existe</div>";
        } else {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    echo "</div>";

    // PASO 4: Agregar pagoCompleto
    echo "<div class='step'>";
    echo "<h3>PASO 4: Agregando campo 'pagoCompleto'...</h3>";

    $sql2 = "ALTER TABLE `estudianteprograma`
             ADD COLUMN `pagoCompleto` TINYINT(1) NOT NULL DEFAULT 0
             COMMENT '0=Pago parcial, 1=Pago completo'
             AFTER `montoPagado`";

    echo "<pre>" . htmlspecialchars($sql2) . "</pre>";

    try {
        $pdo->exec($sql2);
        echo "<div class='success'>✓ Campo 'pagoCompleto' agregado exitosamente</div>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<div class='warning'>⚠ El campo 'pagoCompleto' ya existe</div>";
        } else {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    echo "</div>";

    // PASO 5: Verificar resultado
    echo "<div class='step'>";
    echo "<h3>PASO 5: Verificando resultado final...</h3>";

    $stmt = $pdo->query("DESCRIBE estudianteprograma");
    $camposFinales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $montoPagadoExiste = false;
    $pagoCompletoExiste = false;

    echo "<table>";
    echo "<tr><th>#</th><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";
    $i = 1;
    foreach ($camposFinales as $campo) {
        $highlight = "";
        if ($campo['Field'] == 'montoPagado') {
            $montoPagadoExiste = true;
            $highlight = "class='highlight'";
        }
        if ($campo['Field'] == 'pagoCompleto') {
            $pagoCompletoExiste = true;
            $highlight = "class='highlight'";
        }

        echo "<tr $highlight>";
        echo "<td>{$i}</td>";
        echo "<td><strong>{$campo['Field']}</strong></td>";
        echo "<td>{$campo['Type']}</td>";
        echo "<td>{$campo['Null']}</td>";
        echo "<td>{$campo['Default']}</td>";
        echo "</tr>";
        $i++;
    }
    echo "</table>";

    echo "<hr>";

    if ($montoPagadoExiste && $pagoCompletoExiste) {
        echo "<div class='success'>";
        echo "<h2 style='color: green;'>✓ ¡ÉXITO COMPLETO!</h2>";
        echo "<p>Los campos han sido agregados correctamente:</p>";
        echo "<ul>";
        echo "<li>✓ <strong>montoPagado</strong> - DECIMAL(10,2)</li>";
        echo "<li>✓ <strong>pagoCompleto</strong> - TINYINT(1)</li>";
        echo "</ul>";
        echo "<p><strong>Ahora puedes registrar inscripciones sin problemas.</strong></p>";
        echo "<br>";
        echo "<a href='inscripcion' style='display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Ir a Inscripción</a>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<h3>❌ Faltan campos:</h3>";
        if (!$montoPagadoExiste) echo "<p>• montoPagado NO existe</p>";
        if (!$pagoCompletoExiste) echo "<p>• pagoCompleto NO existe</p>";
        echo "</div>";
    }

    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error Fatal:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h4>Detalles:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #999;'><small>" . date('Y-m-d H:i:s') . "</small></p>";
echo "</div>";
echo "</body></html>";
?>
