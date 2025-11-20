<?php
// Test para verificar que el campo CostoMatricula se muestra correctamente
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/programa.modelo.php';

echo "<h2>Test de Costo de Matrícula</h2>";

try {
    $pdo = Conexion::Conectar();

    // Obtener un programa de ejemplo
    $stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, GradoAcademico, Costo, CostoMatricula FROM programa WHERE (Estado = 'ACTIVO' OR Estado = 1) LIMIT 3");
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($programas)) {
        die("✗ No hay programas activos en la BD");
    }

    echo "<h3>Programas con su Costo de Matrícula:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #4CAF50; color: white;'>";
    echo "<th>ID</th>";
    echo "<th>Nombre del Programa</th>";
    echo "<th>Grado</th>";
    echo "<th>Costo Total</th>";
    echo "<th>Costo Matrícula</th>";
    echo "<th>Acción</th>";
    echo "</tr>";

    foreach ($programas as $programa) {
        $costoMatricula = $programa['CostoMatricula'] ? 'Bs. ' . number_format($programa['CostoMatricula'], 2) : '<span style="color: orange;">No definido</span>';

        echo "<tr>";
        echo "<td>" . $programa['ProgramaID'] . "</td>";
        echo "<td><strong>" . $programa['NombrePrograma'] . "</strong></td>";
        echo "<td>" . $programa['GradoAcademico'] . "</td>";
        echo "<td style='color: green;'><strong>Bs. " . number_format($programa['Costo'], 2) . "</strong></td>";
        echo "<td style='color: blue;'><strong>" . $costoMatricula . "</strong></td>";
        echo "<td><button onclick='testAjax(" . $programa['ProgramaID'] . ")'>Probar AJAX</button></td>";
        echo "</tr>";
    }

    echo "</table>";

    // Verificar que el método MostrarDetallePrograma funcione
    echo "<hr><h3>Test del Método MostrarDetallePrograma:</h3>";

    $primerPrograma = $programas[0];
    $detalle = ProgramasModelos::MostrarDetallePrograma($primerPrograma['ProgramaID']);

    if ($detalle) {
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 5px solid #2196F3;'>";
        echo "<h4 style='color: #1976D2; margin-top: 0;'>✓ Método funciona correctamente</h4>";
        echo "<p><strong>Programa:</strong> " . $detalle['NombrePrograma'] . "</p>";
        echo "<p><strong>Código:</strong> " . $detalle['Codigo'] . "</p>";
        echo "<p><strong>Costo Total:</strong> <span style='color: green; font-weight: bold;'>Bs. " . number_format($detalle['Costo'], 2) . "</span></p>";

        if (isset($detalle['CostoMatricula'])) {
            echo "<p><strong>Costo Matrícula:</strong> <span style='color: blue; font-weight: bold;'>Bs. " . number_format($detalle['CostoMatricula'], 2) . "</span></p>";
        } else {
            echo "<p style='color: orange;'><strong>⚠ Campo CostoMatricula no encontrado en la respuesta</strong></p>";
        }

        echo "<p><strong>Duración:</strong> " . $detalle['DuracionMeses'] . " meses</p>";
        echo "<p><strong>Módulos:</strong> " . $detalle['Modulos'] . "</p>";
        echo "<p><strong>Sede:</strong> " . $detalle['Sede'] . "</p>";
        echo "</div>";

        // Mostrar todos los campos del detalle
        echo "<hr><h4>Todos los campos devueltos:</h4>";
        echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
        print_r($detalle);
        echo "</pre>";
    } else {
        echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; border-left: 5px solid #f44336;'>";
        echo "<p style='color: #c62828;'>✗ No se pudo obtener el detalle del programa</p>";
        echo "</div>";
    }

    // Test de respuesta JSON como lo recibirá JavaScript
    echo "<hr><h3>Test de Respuesta JSON (como lo recibirá JavaScript):</h3>";
    echo "<div style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
    echo "<strong>JSON del programa " . $primerPrograma['ProgramaID'] . ":</strong><br>";
    echo "<pre>" . json_encode($detalle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; border-left: 5px solid #f44336;'>";
    echo "<h3 style='color: #c62828;'>✗ Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "</div>";
}
?>

<script>
function testAjax(programaId) {
    console.log('Probando AJAX para programa:', programaId);

    fetch('ajax/programa.ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'idprograma=' + programaId
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta AJAX:', data);

        let mensaje = '✓ RESPUESTA AJAX EXITOSA\\n\\n';
        mensaje += 'Programa: ' + data.NombrePrograma + '\\n';
        mensaje += 'Código: ' + data.Codigo + '\\n';
        mensaje += 'Costo Total: Bs. ' + parseFloat(data.Costo).toFixed(2) + '\\n';

        if (data.CostoMatricula) {
            mensaje += 'Costo Matrícula: Bs. ' + parseFloat(data.CostoMatricula).toFixed(2) + '\\n';
            mensaje += '\\n✓ Campo CostoMatricula presente en la respuesta';
        } else {
            mensaje += '\\n⚠ Campo CostoMatricula NO presente en la respuesta';
        }

        alert(mensaje);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('✗ Error al obtener datos del programa');
    });
}
</script>

<hr>
<div style="text-align: center; margin: 30px 0;">
    <a href="index.php?ruta=inscripcion" style="display: inline-block; padding: 12px 24px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Ir a la Página de Inscripción
    </a>
    <a href="resumen_pruebas_matricula.html" style="display: inline-block; padding: 12px 24px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-left: 10px;">
        Ver Resumen de Pruebas
    </a>
</div>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        color: #1976D2;
        border-bottom: 3px solid #1976D2;
        padding-bottom: 10px;
    }
    h3 {
        color: #388E3C;
    }
    button {
        background: #2196F3;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    button:hover {
        background: #1976D2;
    }
</style>
