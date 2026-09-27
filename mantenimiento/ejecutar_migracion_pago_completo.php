<?php
/**
 * Script de Migración - Pago Completo del Programa
 * Ejecutar este script UNA SOLA VEZ para agregar las columnas necesarias
 */

require_once 'modelos/conexion.modelo.php';

echo "<h2>Migración: Agregar campos de Pago Completo</h2>";
echo "<hr>";

try {
    $pdo = Conexion::Conectar();

    echo "<h3>Paso 1: Verificando conexión a la base de datos...</h3>";
    echo "<p style='color: green;'>✓ Conexión exitosa</p>";

    echo "<h3>Paso 2: Agregando columna 'montoPagado'...</h3>";
    try {
        $sql1 = "ALTER TABLE estudianteprograma
                 ADD COLUMN montoPagado DECIMAL(10,2) DEFAULT 0 AFTER costomatricula";
        $pdo->exec($sql1);
        echo "<p style='color: green;'>✓ Columna 'montoPagado' agregada correctamente</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p style='color: orange;'>⚠ La columna 'montoPagado' ya existe</p>";
        } else {
            throw $e;
        }
    }

    echo "<h3>Paso 3: Agregando columna 'pagoCompleto'...</h3>";
    try {
        $sql2 = "ALTER TABLE estudianteprograma
                 ADD COLUMN pagoCompleto TINYINT(1) DEFAULT 0 AFTER montoPagado";
        $pdo->exec($sql2);
        echo "<p style='color: green;'>✓ Columna 'pagoCompleto' agregada correctamente</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p style='color: orange;'>⚠ La columna 'pagoCompleto' ya existe</p>";
        } else {
            throw $e;
        }
    }

    echo "<h3>Paso 4: Actualizando registros existentes...</h3>";
    $sql3 = "UPDATE estudianteprograma
             SET montoPagado = costomatricula,
                 pagoCompleto = 0
             WHERE montoPagado IS NULL OR montoPagado = 0";
    $stmt = $pdo->prepare($sql3);
    $stmt->execute();
    $rowsUpdated = $stmt->rowCount();
    echo "<p style='color: green;'>✓ {$rowsUpdated} registros actualizados</p>";

    echo "<h3>Paso 5: Verificando estructura de la tabla...</h3>";
    $sql4 = "DESCRIBE estudianteprograma";
    $stmt = $pdo->query($sql4);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #667eea; color: white;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";

    $camposEncontrados = ['montoPagado' => false, 'pagoCompleto' => false];

    foreach ($columns as $col) {
        $style = '';
        if ($col['Field'] == 'montoPagado' || $col['Field'] == 'pagoCompleto') {
            $style = 'background: #d4edda; font-weight: bold;';
            $camposEncontrados[$col['Field']] = true;
        }

        echo "<tr style='{$style}'>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<br><h3>Resultado Final:</h3>";
    if ($camposEncontrados['montoPagado'] && $camposEncontrados['pagoCompleto']) {
        echo "<div style='background: #d4edda; padding: 20px; border: 2px solid #28a745; border-radius: 10px;'>";
        echo "<h2 style='color: #28a745; margin: 0;'>✓ MIGRACIÓN EXITOSA</h2>";
        echo "<p>Las columnas 'montoPagado' y 'pagoCompleto' han sido agregadas correctamente.</p>";
        echo "<p><strong>La funcionalidad de Pago Completo está lista para usarse.</strong></p>";
        echo "</div>";

        echo "<br><h3>Siguientes pasos:</h3>";
        echo "<ol>";
        echo "<li>Ir al módulo de <strong>Inscripción</strong></li>";
        echo "<li>Registrar un nuevo estudiante</li>";
        echo "<li>Marcar el checkbox <strong>'PAGO COMPLETO DEL PROGRAMA'</strong></li>";
        echo "<li>Verificar en <strong>Matriculados</strong> que aparezca con badge verde</li>";
        echo "</ol>";

        echo "<br><p style='color: red;'><strong>IMPORTANTE:</strong> Puedes eliminar este archivo después de ejecutarlo.</p>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border: 2px solid #dc3545; border-radius: 10px;'>";
        echo "<h2 style='color: #dc3545; margin: 0;'>✗ ERROR EN LA MIGRACIÓN</h2>";
        echo "<p>No se pudieron agregar todas las columnas necesarias.</p>";
        echo "</div>";
    }

} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border: 2px solid #dc3545; border-radius: 10px;'>";
    echo "<h2 style='color: #dc3545; margin: 0;'>✗ ERROR</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>
