<?php
/**
 * Diagnóstico de orden de pago
 */

session_start();
require_once 'modelos/conexion.modelo.php';

echo "<h2>Diagnóstico del Sistema de Orden de Pago</h2>";

try {
    $conexion = Conexion::Conectar();
    echo "<p style='color: green;'>✓ Conexión a base de datos exitosa</p>";

    // Verificar tabla ordenpago
    $stmt = $conexion->query("SHOW TABLES LIKE 'ordenpago'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Tabla 'ordenpago' existe</p>";

        // Verificar estructura
        $stmt = $conexion->query("DESCRIBE ordenpago");
        $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p><strong>Estructura de la tabla:</strong></p>";
        echo "<ul>";
        foreach ($campos as $campo) {
            echo "<li>{$campo['Field']}: {$campo['Type']}</li>";
        }
        echo "</ul>";

        // Verificar registros
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM ordenpago");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total de órdenes registradas: <strong>{$result['total']}</strong></p>";

    } else {
        echo "<p style='color: red;'>✗ Tabla 'ordenpago' NO existe</p>";
    }

    // Verificar archivo AJAX
    $archivoAjax = __DIR__ . '/ajax/ordenpago.ajax.php';
    if (file_exists($archivoAjax)) {
        echo "<p style='color: green;'>✓ Archivo ajax/ordenpago.ajax.php existe</p>";

        // Verificar sintaxis
        $output = shell_exec('"C:\xampp\php\php.exe" -l "' . $archivoAjax . '" 2>&1');
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p style='color: green;'>✓ Sintaxis correcta en ordenpago.ajax.php</p>";
        } else {
            echo "<p style='color: red;'>✗ Error de sintaxis: $output</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Archivo ajax/ordenpago.ajax.php NO existe</p>";
    }

    // Verificar archivo PDF
    $archivoPDF = __DIR__ . '/vistas/componentes/generar-orden-pago-pdf.php';
    if (file_exists($archivoPDF)) {
        echo "<p style='color: green;'>✓ Archivo generar-orden-pago-pdf.php existe</p>";

        // Verificar sintaxis
        $output = shell_exec('"C:\xampp\php\php.exe" -l "' . $archivoPDF . '" 2>&1');
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p style='color: green;'>✓ Sintaxis correcta en generar-orden-pago-pdf.php</p>";
        } else {
            echo "<p style='color: red;'>✗ Error de sintaxis: $output</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Archivo generar-orden-pago-pdf.php NO existe</p>";
    }

    // Verificar archivo JavaScript
    $archivoJS = __DIR__ . '/vistas/recursos/assets/js/scripts/ordenpago.js';
    if (file_exists($archivoJS)) {
        echo "<p style='color: green;'>✓ Archivo ordenpago.js existe</p>";
        $size = filesize($archivoJS);
        echo "<p>Tamaño del archivo: " . number_format($size) . " bytes</p>";
    } else {
        echo "<p style='color: red;'>✗ Archivo ordenpago.js NO existe</p>";
    }

    // Verificar TCPDF
    $tcpdfPath = __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    if (file_exists($tcpdfPath)) {
        echo "<p style='color: green;'>✓ TCPDF está instalado</p>";
    } else {
        echo "<p style='color: red;'>✗ TCPDF NO está instalado</p>";
    }

    // Verificar un estudiante con pagos
    $stmt = $conexion->query("
        SELECT
            e.EstudianteID,
            e.Nombre,
            e.Apaterno,
            COUNT(pm.Idpagomodulo) as total_pagos
        FROM estudiante e
        LEFT JOIN estudianteprograma ep ON e.EstudianteID = ep.EstudianteID
        LEFT JOIN pagomodulo pm ON ep.idInscripcion = pm.idinscripcion
        WHERE e.Estado = 1
        GROUP BY e.EstudianteID
        HAVING total_pagos > 0
        LIMIT 1
    ");

    if ($stmt->rowCount() > 0) {
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Hay estudiantes con pagos registrados</p>";
        echo "<p>Ejemplo: {$estudiante['Nombre']} {$estudiante['Apaterno']} - Total pagos: {$estudiante['total_pagos']}</p>";
    } else {
        echo "<p style='color: orange;'>⚠ No hay estudiantes con pagos registrados</p>";
    }

    echo "<hr>";
    echo "<p><strong>Resumen:</strong> El sistema parece estar configurado correctamente.</p>";
    echo "<p>Si el PDF no se genera, verifique:</p>";
    echo "<ul>";
    echo "<li>La consola del navegador (F12) para errores de JavaScript</li>";
    echo "<li>Que los campos obligatorios del formulario estén completos</li>";
    echo "<li>Que el navegador permita ventanas emergentes (popups)</li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
