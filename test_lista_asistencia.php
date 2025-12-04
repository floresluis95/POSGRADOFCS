<?php
/**
 * Archivo de prueba para verificar la generación de PDF
 */

// Habilitar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Generación de Lista de Asistencia</h2>";

// Test 1: Verificar que TCPDF existe
echo "<h3>Test 1: Verificar TCPDF</h3>";
$tcpdfPath = __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
if (file_exists($tcpdfPath)) {
    echo "✅ TCPDF encontrado en: " . $tcpdfPath . "<br>";
    require_once $tcpdfPath;
    echo "✅ TCPDF cargado correctamente<br>";
} else {
    echo "❌ TCPDF NO encontrado en: " . $tcpdfPath . "<br>";
}

// Test 2: Verificar archivo generar-lista-asistencia-pdf.php
echo "<h3>Test 2: Verificar archivo de generación</h3>";
$archivoPDF = __DIR__ . '/vistas/componentes/generar-lista-asistencia-pdf.php';
if (file_exists($archivoPDF)) {
    echo "✅ Archivo encontrado: " . $archivoPDF . "<br>";
} else {
    echo "❌ Archivo NO encontrado: " . $archivoPDF . "<br>";
}

// Test 3: Verificar conexión
echo "<h3>Test 3: Verificar modelo de conexión</h3>";
$conexionPath = __DIR__ . '/modelos/conexion.modelo.php';
if (file_exists($conexionPath)) {
    echo "✅ Modelo de conexión encontrado<br>";
    require_once $conexionPath;
    try {
        $conexion = Conexion::Conectar();
        if ($conexion) {
            echo "✅ Conexión a base de datos exitosa<br>";
        } else {
            echo "❌ No se pudo conectar a la base de datos<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error al conectar: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Modelo de conexión NO encontrado<br>";
}

// Test 4: Simular parámetros
echo "<h3>Test 4: Parámetros de prueba</h3>";
echo "Para probar, usa una URL como:<br>";
echo "<code>generar-lista-asistencia-pdf.php?moduloID=1&programaID=1&moduloNombre=Test&moduloCodigo=TEST01&programaNombre=Programa Test&grado=Maestría&docenteNombre=Dr. Test</code><br>";

echo "<h3>✅ Tests completados</h3>";
?>
