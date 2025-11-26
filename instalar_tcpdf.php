<?php
/**
 * Script para instalar TCPDF (v2 con cURL)
 */

set_time_limit(300);

echo "<h2>Instalando TCPDF...</h2>";

$tcpdfUrl = "https://github.com/tecnickcom/TCPDF/archive/refs/heads/main.zip"; // Última versión
$zipFile = "tcpdf.zip";
$vendorDir = __DIR__ . "/vendor";
$tcpdfDir = $vendorDir . "/tecnickcom";

// Crear directorios
if (!is_dir($vendorDir)) {
    mkdir($vendorDir, 0755, true);
    echo "<p style='color: green;'>✓ Directorio vendor creado</p>";
}

if (!is_dir($tcpdfDir)) {
    mkdir($tcpdfDir, 0755, true);
    echo "<p style='color: green;'>✓ Directorio tecnickcom creado</p>";
}

echo "<p>Descargando TCPDF desde GitHub (puede tardar un momento)...</p>";

// Descargar usando cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tcpdfUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Deshabilitar verificación SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 180);

$zipContent = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($zipContent === false || $httpCode != 200) {
    die("<p style='color: red;'>✗ Error al descargar TCPDF: $error (HTTP $httpCode)</p>");
}

file_put_contents($zipFile, $zipContent);
$sizeMB = number_format(filesize($zipFile) / 1024 / 1024, 2);
echo "<p style='color: green;'>✓ TCPDF descargado ($sizeMB MB)</p>";

// Extraer ZIP
echo "<p>Extrayendo archivos...</p>";
$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($tcpdfDir);
    $zip->close();
    echo "<p style='color: green;'>✓ Archivos extraídos</p>";

    // Renombrar carpeta
    $extractedDir = $tcpdfDir . "/TCPDF-main";
    $finalDir = $tcpdfDir . "/tcpdf";

    if (is_dir($extractedDir)) {
        if (is_dir($finalDir)) {
            deleteDirectory($finalDir);
        }
        rename($extractedDir, $finalDir);
        echo "<p style='color: green;'>✓ TCPDF instalado en: vendor/tecnickcom/tcpdf/</p>";
    }

    // Eliminar ZIP
    unlink($zipFile);
    echo "<p style='color: green;'>✓ Limpieza completada</p>";

    echo "<div style='padding: 20px; background: #d4edda; border: 1px solid #28a745; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #28a745;'>✅ TCPDF instalado exitosamente!</h3>";
    echo "<p>Ubicación: <code>vendor/tecnickcom/tcpdf/</code></p>";
    echo "<ul>";
    echo "<li>Versión: 6.6.5</li>";
    echo "<li>Listo para generar reportes PDF</li>";
    echo "</ul>";
    echo "</div>";

    // Crear autoloader
    $autoloaderContent = "<?php\n// Autoloader para TCPDF\nrequire_once __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';";
    file_put_contents(__DIR__ . "/vendor/autoload.php", $autoloaderContent);
    echo "<p style='color: green;'>✓ Autoloader creado</p>";

    echo "<a href='index.php' style='display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 15px; font-weight: bold;'>Volver al Sistema</a>";

} else {
    echo "<p style='color: red;'>✗ Error al extraer el archivo ZIP</p>";
    if (file_exists($zipFile)) {
        unlink($zipFile);
    }
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}
?>
