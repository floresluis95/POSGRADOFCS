<?php
/**
 * Archivo de depuración para capturar datos POST
 */

session_start();

echo "<h2>DEPURACIÓN DE DATOS POST</h2>";
echo "<p>Este archivo captura los datos que se envían desde el formulario.</p>";

echo "<h3>1. Sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>2. Datos POST recibidos:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>3. Datos GET recibidos:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h3>4. Variables de servidor:</h3>";
echo "<pre>";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'No disponible') . "\n";
echo "</pre>";

echo "<hr>";
echo "<h3>ANÁLISIS:</h3>";

if (empty($_POST)) {
    echo "<p style='color: red;'>⚠️ NO se recibieron datos POST. El formulario no se está enviando correctamente.</p>";
    echo "<p>Posibles causas:</p>";
    echo "<ul>";
    echo "<li>El formulario no se está submiteando</li>";
    echo "<li>El método no es POST</li>";
    echo "<li>Hay un error de JavaScript</li>";
    echo "</ul>";
} else {
    echo "<p style='color: green;'>✓ Se recibieron datos POST</p>";

    // Verificar campos requeridos
    $camposRequeridos = ['moduloID', 'programaID', 'moduloNombre', 'docenteNombre'];
    $faltantes = [];

    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            $faltantes[] = $campo;
        }
    }

    if (!empty($faltantes)) {
        echo "<p style='color: orange;'>⚠️ Faltan campos requeridos:</p>";
        echo "<ul>";
        foreach ($faltantes as $campo) {
            echo "<li>" . $campo . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: green;'>✓ Todos los campos requeridos están presentes</p>";
        echo "<p><strong>El PDF debería generarse correctamente.</strong></p>";
    }
}
?>
