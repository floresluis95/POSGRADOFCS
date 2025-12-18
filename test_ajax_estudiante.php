<?php
/**
 * Test del archivo AJAX de estudiantes
 */

echo "<h2>Test AJAX Estudiantes</h2>";

// Obtener ID desde GET o usar 1 por defecto
$estudianteID = isset($_GET['id']) ? intval($_GET['id']) : 1;

echo "<h3>Información de Debug:</h3>";
echo "<ul>";
echo "<li><strong>Directorio actual:</strong> " . __DIR__ . "</li>";
echo "<li><strong>Archivo AJAX:</strong> " . __DIR__ . "/ajax/estudiantes.ajax.php</li>";
echo "<li><strong>¿Existe el archivo?</strong> " . (file_exists(__DIR__ . "/ajax/estudiantes.ajax.php") ? "SÍ ✅" : "NO ❌") . "</li>";
echo "<li><strong>Estudiante ID:</strong> {$estudianteID}</li>";
echo "</ul>";

// Simular POST
$_POST['idestudiante'] = $estudianteID;

echo "<h3>Resultado del AJAX:</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border: 1px solid #ccc; border-radius: 5px;'>";

// Capturar la salida
ob_start();
include 'ajax/estudiantes.ajax.php';
$output = ob_get_clean();

echo $output;
echo "</div>";

echo "<br><br><strong>Interpretación:</strong><br>";
echo "Si ves un JSON válido arriba (con llaves y datos), el AJAX funciona correctamente.";
echo "<br><br><a href='listar_estudiantes.php'>← Volver a lista de estudiantes</a>";
echo " | <a href='ordenpago'>Ir a Orden de Pago →</a>";
?>
