<?php
/**
 * Verificar qué versión del archivo AJAX se está cargando
 */

echo "<!DOCTYPE html><html><head><title>Verificación Archivo AJAX</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}";
echo ".box{background:white;padding:20px;margin:10px 0;border-radius:8px;}";
echo "pre{background:#f8f9fa;padding:10px;border-radius:4px;overflow-x:auto;}</style>";
echo "</head><body>";

echo "<h1>🔍 Verificación del Archivo AJAX</h1>";

$archivo = __DIR__ . '/ajax/calificacion.ajax.php';

echo "<div class='box'>";
echo "<h2>1. Ruta del Archivo</h2>";
echo "<p><strong>Ruta completa:</strong><br><code>$archivo</code></p>";
echo "<p><strong>Existe:</strong> " . (file_exists($archivo) ? '✅ Sí' : '❌ No') . "</p>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>2. Primeras 15 líneas del archivo</h2>";
$contenido = file_get_contents($archivo);
$lineas = explode("\n", $contenido);
echo "<pre>";
for ($i = 0; $i < min(15, count($lineas)); $i++) {
    echo sprintf("%2d: %s\n", $i + 1, htmlspecialchars($lineas[$i]));
}
echo "</pre>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>3. Verificar línea 7 específicamente</h2>";
echo "<p><strong>Línea 7:</strong></p>";
echo "<pre>" . htmlspecialchars($lineas[6]) . "</pre>";

if (strpos($lineas[6], '__DIR__') !== false) {
    echo "<p style='color:green;font-weight:bold;'>✅ La línea 7 contiene __DIR__ (CORRECTO)</p>";
} else if (strpos($lineas[6], 'require_once') !== false) {
    echo "<p style='color:red;font-weight:bold;'>❌ La línea 7 NO contiene __DIR__ (INCORRECTO)</p>";
    echo "<p>Contiene: <code>" . htmlspecialchars($lineas[6]) . "</code></p>";
}
echo "</div>";

echo "<div class='box'>";
echo "<h2>4. Información del archivo</h2>";
echo "<ul>";
echo "<li><strong>Tamaño:</strong> " . filesize($archivo) . " bytes</li>";
echo "<li><strong>Última modificación:</strong> " . date("Y-m-d H:i:s", filemtime($archivo)) . "</li>";
echo "<li><strong>Permisos:</strong> " . substr(sprintf('%o', fileperms($archivo)), -4) . "</li>";
echo "</ul>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>5. Acciones</h2>";
echo "<p><a href='limpiar_cache_php.php' style='background:#667eea;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>🗑️ Limpiar Caché PHP</a></p>";
echo "<p><a href='test_ajax_simple.php' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;display:inline-block;'>🧪 Test AJAX Simple</a></p>";
echo "</div>";

echo "</body></html>";
?>
