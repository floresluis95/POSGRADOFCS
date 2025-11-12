<?php
/**
 * ARCHIVO DE DEBUG PARA BÚSQUEDA DE PROGRAMAS
 *
 * Acceder a: http://localhost/POSGRADOFCS/debug_busqueda.php?grado=MAESTRIA&fechaInicio=2025-01-01&fechaFin=2025-12-31
 *
 * Este archivo te mostrará:
 * 1. Los parámetros recibidos
 * 2. La consulta SQL generada
 * 3. Los resultados de la búsqueda
 */

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/programa.modelo.php';

echo "<h1>🔍 DEBUG - Búsqueda de Programas</h1>";
echo "<hr>";

// Capturar parámetros
$grado = isset($_GET['grado']) && !empty($_GET['grado']) ? $_GET['grado'] : null;
$fechaInicio = isset($_GET['fechaInicio']) && !empty($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
$fechaFin = isset($_GET['fechaFin']) && !empty($_GET['fechaFin']) ? $_GET['fechaFin'] : null;

echo "<h2>📋 Parámetros Recibidos:</h2>";
echo "<pre>";
echo "Grado Académico: " . ($grado ? $grado : "NO ESPECIFICADO") . "\n";
echo "Fecha Inicio (desde): " . ($fechaInicio ? $fechaInicio : "NO ESPECIFICADO") . "\n";
echo "Fecha Fin (hasta): " . ($fechaFin ? $fechaFin : "NO ESPECIFICADO") . "\n";
echo "</pre>";

echo "<h2>🔗 URL de Ejemplo:</h2>";
echo "<pre>debug_busqueda.php?grado=MAESTRIA&fechaInicio=2025-01-01&fechaFin=2025-12-31</pre>";

echo "<hr>";

// Ejecutar búsqueda
echo "<h2>🔍 Resultados de la Búsqueda:</h2>";

try {
    if ($grado !== null || $fechaInicio !== null || $fechaFin !== null) {
        $resultados = ProgramasModelos::BuscarProgramasConFiltros($grado, $fechaInicio, $fechaFin);
        echo "<p style='color: green;'>✅ Usando método: BuscarProgramasConFiltros()</p>";
    } else {
        $resultados = ProgramasModelos::ListaProgramaModelo();
        echo "<p style='color: blue;'>ℹ️ Usando método: ListaProgramaModelo() (sin filtros)</p>";
    }

    echo "<h3>Total de programas encontrados: " . count($resultados) . "</h3>";

    if (!empty($resultados)) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #667eea; color: white;'>
                <th>ID</th>
                <th>Programa</th>
                <th>Grado</th>
                <th>Código</th>
                <th>Fecha Inicio</th>
                <th>Sede</th>
                <th>Estado</th>
              </tr>";

        foreach ($resultados as $programa) {
            echo "<tr>";
            echo "<td>" . $programa['ProgramaID'] . "</td>";
            echo "<td>" . $programa['NombrePrograma'] . "</td>";
            echo "<td>" . $programa['GradoAcademico'] . "</td>";
            echo "<td>" . $programa['Codigo'] . "</td>";
            echo "<td>" . $programa['FechaInicio'] . "</td>";
            echo "<td>" . $programa['Sede'] . "</td>";
            echo "<td>" . $programa['Estado'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p style='color: orange; font-size: 18px;'>⚠️ No se encontraron resultados con los filtros especificados</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📝 Notas:</h2>";
echo "<ul>";
echo "<li>Verifica que hayas ejecutado el script SQL para actualizar el campo Estado</li>";
echo "<li>Los valores de grado deben ser exactos: DIPLOMADO, MAESTRIA, ESPECIALIDAD</li>";
echo "<li>Las fechas deben estar en formato: YYYY-MM-DD (ej: 2025-01-01)</li>";
echo "<li>Si ves resultados aquí pero no en la página principal, el problema está en el controlador o la vista</li>";
echo "</ul>";

echo "<hr>";
echo "<h2>🔧 Próximos Pasos:</h2>";
echo "<ol>";
echo "<li>Si este debug muestra resultados correctos, el problema está en la vista</li>";
echo "<li>Si no muestra resultados, verifica la base de datos</li>";
echo "<li>Abre la consola del navegador (F12) para ver los logs de JavaScript</li>";
echo "<li>Verifica que la URL se esté construyendo correctamente</li>";
echo "</ol>";

?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #667eea;
    }
    pre {
        background: #fff;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    table {
        background: white;
        margin-top: 20px;
    }
</style>
