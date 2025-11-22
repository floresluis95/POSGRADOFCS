<?php
/**
 * Script de prueba para verificar módulos
 * Ejecutar: http://localhost/POSGRADOFCS/test_modulos.php?programaID=1&idinscripcion=1
 */

require_once 'modelos/pagomodulo.modelo.php';
require_once 'modelos/conexion.modelo.php';

echo "<h1>🧪 Test de Módulos con Estado de Pago</h1>";
echo "<hr>";

// Obtener parámetros
$programaID = isset($_GET['programaID']) ? (int)$_GET['programaID'] : 0;
$idinscripcion = isset($_GET['idinscripcion']) ? (int)$_GET['idinscripcion'] : 0;

echo "<h2>📋 Parámetros de Prueba</h2>";
echo "<p><strong>ProgramaID:</strong> $programaID</p>";
echo "<p><strong>idinscripcion:</strong> $idinscripcion</p>";

if ($programaID == 0 || $idinscripcion == 0) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
    echo "<h3>⚠️ Falta especificar parámetros</h3>";
    echo "<p>Usa: test_modulos.php?programaID=X&idinscripcion=Y</p>";
    echo "</div>";
    echo "<hr>";

    // Listar programas disponibles
    try {
        $pdo = Conexion::Conectar();
        $stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, Codigo FROM programa ORDER BY ProgramaID LIMIT 10");
        $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>📚 Programas Disponibles:</h3>";
        echo "<ul>";
        foreach ($programas as $prog) {
            echo "<li><strong>ID {$prog['ProgramaID']}</strong>: {$prog['NombrePrograma']} ({$prog['Codigo']})</li>";
        }
        echo "</ul>";

        // Listar inscripciones disponibles
        $stmt2 = $pdo->query("SELECT idInscripcion, EstudianteID, ProgramaID FROM estudianteprograma ORDER BY idInscripcion LIMIT 10");
        $inscripciones = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>📝 Inscripciones Disponibles:</h3>";
        echo "<ul>";
        foreach ($inscripciones as $insc) {
            echo "<li><strong>idInscripcion: {$insc['idInscripcion']}</strong> - EstudianteID: {$insc['EstudianteID']}, ProgramaID: {$insc['ProgramaID']}</li>";
        }
        echo "</ul>";

        // Sugerir URL de prueba
        if (!empty($inscripciones)) {
            $primera = $inscripciones[0];
            echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin-top: 20px;'>";
            echo "<h4>💡 URL de Prueba Sugerida:</h4>";
            echo "<p><a href='test_modulos.php?programaID={$primera['ProgramaID']}&idinscripcion={$primera['idInscripcion']}' style='color: #004085; font-weight: bold;'>";
            echo "test_modulos.php?programaID={$primera['ProgramaID']}&idinscripcion={$primera['idInscripcion']}";
            echo "</a></p>";
            echo "</div>";
        }

    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
        echo "<h3>❌ Error al consultar base de datos</h3>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }

    exit;
}

echo "<hr>";

// Test 1: Verificar si hay módulos en el programa
echo "<h2>🔍 Test 1: Módulos del Programa</h2>";
try {
    $modulos = PagoModuloModelo::ObtenerModulosPorProgramaModelo($programaID);

    if (empty($modulos)) {
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107;'>";
        echo "<h3>⚠️ No hay módulos en este programa</h3>";
        echo "<p>El programa con ID $programaID no tiene módulos registrados.</p>";
        echo "<p><strong>Solución:</strong> Debes crear módulos para este programa primero.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
        echo "<h3>✅ Se encontraron " . count($modulos) . " módulos</h3>";
        echo "</div>";

        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; margin-top: 15px;'>";
        echo "<thead style='background: #667eea; color: white;'>";
        echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Créditos</th><th>Costo</th></tr>";
        echo "</thead><tbody>";

        foreach ($modulos as $mod) {
            echo "<tr>";
            echo "<td>{$mod['ModuloID']}</td>";
            echo "<td><strong>{$mod['Codigo']}</strong></td>";
            echo "<td>{$mod['NombreModulo']}</td>";
            echo "<td>{$mod['Creditos']}</td>";
            echo "<td>Bs. {$mod['Costo']}</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
    }
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Error en Test 1</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";

// Test 2: Módulos con estado de pago
echo "<h2>🎯 Test 2: Módulos con Estado de Pago</h2>";
try {
    $modulosConEstado = PagoModuloModelo::ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion);

    if (empty($modulosConEstado)) {
        echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
        echo "<h3>❌ No se obtuvieron módulos con estado</h3>";
        echo "<p>Puede ser que:</p>";
        echo "<ul>";
        echo "<li>El programa no tiene módulos</li>";
        echo "<li>Hay un error en la consulta SQL</li>";
        echo "<li>Los parámetros son incorrectos</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745;'>";
        echo "<h3>✅ Se encontraron " . count($modulosConEstado) . " módulos con estado</h3>";
        echo "</div>";

        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; margin-top: 15px;'>";
        echo "<thead style='background: #667eea; color: white;'>";
        echo "<tr><th>Código</th><th>Nombre</th><th>Costo</th><th>Estado</th><th>Fecha Pago</th><th>Monto Pagado</th><th>Voucher</th></tr>";
        echo "</thead><tbody>";

        $contPagados = 0;
        $contPendientes = 0;

        foreach ($modulosConEstado as $mod) {
            $esPagado = (int)$mod['Pagado'] === 1;
            $colorFondo = $esPagado ? '#d4edda' : '#f8d7da';
            $estado = $esPagado ? '🟢 PAGADO' : '🔴 PENDIENTE';

            if ($esPagado) {
                $contPagados++;
            } else {
                $contPendientes++;
            }

            echo "<tr style='background: $colorFondo;'>";
            echo "<td><strong>{$mod['Codigo']}</strong></td>";
            echo "<td>{$mod['NombreModulo']}</td>";
            echo "<td>Bs. {$mod['Costo']}</td>";
            echo "<td><strong>$estado</strong></td>";
            echo "<td>" . ($mod['FechaPago'] ?? '-') . "</td>";
            echo "<td>" . ($mod['CostoPagado'] ? 'Bs. ' . $mod['CostoPagado'] : '-') . "</td>";
            echo "<td>" . ($mod['NumeroVaucher'] ?? '-') . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";

        echo "<div style='background: #d1ecf1; padding: 15px; border-left: 4px solid #0c5460; margin-top: 20px;'>";
        echo "<h3>📊 Resumen de Estados</h3>";
        echo "<p><strong>Total Módulos:</strong> " . count($modulosConEstado) . "</p>";
        echo "<p><strong>🟢 Pagados:</strong> $contPagados</p>";
        echo "<p><strong>🔴 Pendientes:</strong> $contPendientes</p>";
        echo "</div>";
    }

    // Mostrar JSON para debugging
    echo "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #6c757d; margin-top: 20px;'>";
    echo "<h3>📄 Respuesta JSON (para AJAX)</h3>";
    echo "<pre style='background: #fff; padding: 10px; border: 1px solid #ddd; overflow: auto;'>";
    echo json_encode($modulosConEstado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Error en Test 2</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #666; margin-top: 30px;'>";
echo "Test ejecutado el: " . date('d/m/Y H:i:s');
echo "</p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

h1 {
    color: #333;
    border-bottom: 3px solid #667eea;
    padding-bottom: 10px;
}

h2 {
    color: #555;
    margin-top: 25px;
}

table {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

th {
    text-align: left;
}

tr:nth-child(even) {
    background: #f9f9f9;
}
</style>
