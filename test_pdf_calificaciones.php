<?php
/**
 * Test de Generación de PDF de Calificaciones
 */

session_start();
$_SESSION['Validar'] = true;
$_SESSION['Usuario'] = '1234567';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Test PDF Calificaciones</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    table th { background: #667eea; color: white; }
</style>";
echo "</head><body>";

echo "<h1>🧪 Test de Generación de PDF de Calificaciones</h1>";

// Test 1: Verificar archivo PDF existe
echo "<div class='box'>";
echo "<h2>1. Verificar Archivo PDF</h2>";

$archivoPDF = __DIR__ . '/vistas/componentes/reporte-calificaciones-pdf.php';
if (file_exists($archivoPDF)) {
    echo "<p class='success'>✅ Archivo existe: reporte-calificaciones-pdf.php</p>";
    echo "<p><strong>Ruta:</strong> <code>$archivoPDF</code></p>";
    echo "<p><strong>Tamaño:</strong> " . filesize($archivoPDF) . " bytes</p>";
    echo "<p><strong>Última modificación:</strong> " . date("Y-m-d H:i:s", filemtime($archivoPDF)) . "</p>";
} else {
    echo "<p class='error'>❌ Archivo NO existe</p>";
    echo "<p>Ruta buscada: <code>$archivoPDF</code></p>";
}

echo "</div>";

// Test 2: Verificar módulos con calificaciones
echo "<div class='box'>";
echo "<h2>2. Módulos con Calificaciones</h2>";

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/calificacion.modelo.php';

$modulos = []; // Inicializar variable

try {
    $conexion = Conexion::conectar();

    // Buscar módulos con calificaciones
    $stmt = $conexion->prepare("
        SELECT DISTINCT
            m.Idmodulo,
            m.nombremodulo,
            m.codigomodulo,
            m.estadomodulo,
            p.NombrePrograma,
            p.IdPrograma,
            p.GradoAcademico,
            COUNT(c.CalificacionID) as TotalCalificaciones
        FROM modulos m
        INNER JOIN programa p ON m.IdPrograma = p.IdPrograma
        LEFT JOIN calificacion c ON c.ModuloID = m.Idmodulo AND c.ProgramaID = p.IdPrograma
        GROUP BY m.Idmodulo, m.nombremodulo, m.codigomodulo, m.estadomodulo, p.NombrePrograma, p.IdPrograma, p.GradoAcademico
        HAVING TotalCalificaciones > 0
        LIMIT 5
    ");
    $stmt->execute();
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($modulos) > 0) {
        echo "<p class='success'>✅ Encontrados " . count($modulos) . " módulos con calificaciones</p>";

        echo "<table>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>Módulo</th>";
        echo "<th>Código</th>";
        echo "<th>Programa</th>";
        echo "<th>Estado</th>";
        echo "<th>Calificaciones</th>";
        echo "<th>Test PDF</th>";
        echo "</tr>";

        foreach ($modulos as $i => $mod) {
            $urlPDF = 'vistas/componentes/reporte-calificaciones-pdf.php?' .
                'moduloID=' . $mod['Idmodulo'] .
                '&programaID=' . $mod['IdPrograma'] .
                '&moduloNombre=' . urlencode($mod['nombremodulo']) .
                '&moduloCodigo=' . urlencode($mod['codigomodulo']) .
                '&programaNombre=' . urlencode($mod['NombrePrograma']) .
                '&gradoAcademico=' . urlencode($mod['GradoAcademico']) .
                '&docenteNombre=' . urlencode('Docente de Prueba') .
                '&fechaInicio=' . urlencode('2024-01-15') .
                '&fechaFin=' . urlencode('2024-02-28');

            $estadoBadge = '';
            if ($mod['estadomodulo'] === 'VALIDADO' || $mod['estadomodulo'] === 'CERRADO') {
                $estadoBadge = '<span style="background:#dc3545;color:white;padding:2px 6px;border-radius:3px;">' . $mod['estadomodulo'] . '</span>';
            } else {
                $estadoBadge = '<span style="background:#28a745;color:white;padding:2px 6px;border-radius:3px;">ACTIVO</span>';
            }

            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>" . htmlspecialchars($mod['nombremodulo']) . "</td>";
            echo "<td>" . htmlspecialchars($mod['codigomodulo']) . "</td>";
            echo "<td>" . htmlspecialchars($mod['NombrePrograma']) . "</td>";
            echo "<td>" . $estadoBadge . "</td>";
            echo "<td>" . $mod['TotalCalificaciones'] . "</td>";
            echo "<td>";
            echo "<a href='$urlPDF' target='_blank' style='background:#667eea;color:white;padding:4px 10px;text-decoration:none;border-radius:4px;'>🖨️ Ver PDF</a>";
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";

    } else {
        echo "<p class='warning'>⚠️ No hay módulos con calificaciones registradas</p>";
        echo "<p class='info'>💡 Para crear calificaciones de prueba:</p>";
        echo "<ol>";
        echo "<li>Ve a 'rnotasestudiante'</li>";
        echo "<li>Selecciona un docente</li>";
        echo "<li>Evalúa un módulo con estudiantes</li>";
        echo "<li>Regresa aquí y actualiza</li>";
        echo "</ol>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 3: Test directo de URL
echo "<div class='box'>";
echo "<h2>3. Test Directo con Parámetros de Ejemplo</h2>";

if (count($modulos) > 0) {
    $modPrueba = $modulos[0];

    echo "<p><strong>Módulo de prueba:</strong></p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $modPrueba['Idmodulo'] . "</li>";
    echo "<li><strong>Nombre:</strong> " . $modPrueba['nombremodulo'] . "</li>";
    echo "<li><strong>Código:</strong> " . $modPrueba['codigomodulo'] . "</li>";
    echo "</ul>";

    $urlDirecta = 'vistas/componentes/reporte-calificaciones-pdf.php?' .
        'moduloID=' . $modPrueba['Idmodulo'] .
        '&programaID=' . $modPrueba['IdPrograma'] .
        '&moduloNombre=' . urlencode($modPrueba['nombremodulo']) .
        '&moduloCodigo=' . urlencode($modPrueba['codigomodulo']) .
        '&programaNombre=' . urlencode($modPrueba['NombrePrograma']) .
        '&gradoAcademico=' . urlencode($modPrueba['GradoAcademico']) .
        '&docenteNombre=' . urlencode('Dr. Juan Pérez') .
        '&fechaInicio=2024-01-15' .
        '&fechaFin=2024-02-28';

    echo "<p><strong>URL Completa:</strong></p>";
    echo "<pre style='font-size:10px;'>$urlDirecta</pre>";

    echo "<p style='margin-top:20px;'>";
    echo "<a href='$urlDirecta' target='_blank' style='background:#667eea;color:white;padding:12px 24px;text-decoration:none;border-radius:4px;font-size:14px;'>🖨️ Abrir PDF en Nueva Pestaña</a>";
    echo "</p>";

} else {
    echo "<p class='warning'>⚠️ No hay módulos disponibles para probar</p>";
}

echo "</div>";

// Test 4: Verificar JavaScript
echo "<div class='box'>";
echo "<h2>4. Test del Formulario JavaScript</h2>";

echo "<p class='info'>💡 Prueba el formulario de fechas aquí:</p>";

echo "<button id='testFormulario' style='background:#1dc9b7;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;'>
    📅 Probar Formulario de Fechas
</button>";

echo "</div>";

// Test 5: Verificar imágenes
echo "<div class='box'>";
echo "<h2>5. Verificar Imágenes del PDF</h2>";

$logoUTO = __DIR__ . '/extensiones/imagenespdf/logouto.png';
$logoFCS = __DIR__ . '/extensiones/imagenespdf/logofcs.png';

echo "<table>";
echo "<tr><th>Imagen</th><th>Estado</th><th>Ruta</th></tr>";

echo "<tr>";
echo "<td>Logo UTO</td>";
if (file_exists($logoUTO)) {
    echo "<td class='success'>✅ Existe</td>";
    echo "<td><code>$logoUTO</code></td>";
} else {
    echo "<td class='error'>❌ No existe</td>";
    echo "<td><code>$logoUTO</code></td>";
}
echo "</tr>";

echo "<tr>";
echo "<td>Logo FCS</td>";
if (file_exists($logoFCS)) {
    echo "<td class='success'>✅ Existe</td>";
    echo "<td><code>$logoFCS</code></td>";
} else {
    echo "<td class='error'>❌ No existe</td>";
    echo "<td><code>$logoFCS</code></td>";
}
echo "</tr>";

echo "</table>";

echo "</div>";

// Test 6: Instrucciones
echo "<div class='box'>";
echo "<h2>6. Instrucciones de Uso</h2>";

echo "<ol style='line-height: 2em;'>";
echo "<li><strong>Test desde este script:</strong> Usa los botones 'Ver PDF' de la tabla de arriba</li>";
echo "<li><strong>Test desde la aplicación:</strong>";
echo "<ul>";
echo "<li>Ve a <a href='index.php?ruta=rnotasestudiante'>rnotasestudiante</a></li>";
echo "<li>Selecciona un docente</li>";
echo "<li>Click en 'Ver Módulos Cerrados'</li>";
echo "<li>Click en botón 'Imprimir' (solo en módulos cerrados)</li>";
echo "<li>Completa el formulario de fechas</li>";
echo "<li>Click en 'Generar PDF'</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Solución de problemas:</strong>";
echo "<ul>";
echo "<li>Si no abre el PDF, verifica que no haya bloqueador de pop-ups</li>";
echo "<li>Si muestra error, revisa la consola del navegador (F12)</li>";
echo "<li>Si el PDF está en blanco, verifica que haya calificaciones registradas</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";

echo "</div>";

// JavaScript para test
echo "<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>";
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
echo "<script>
$('#testFormulario').on('click', function() {
    Swal.fire({
        title: '<i class=\"la la-calendar\"></i> Fechas del Módulo',
        html: `
            <div style=\"text-align: left; padding: 20px;\">
                <h5 style=\"color: #5867dd; margin-bottom: 15px;\">
                    📚 Módulo de Prueba
                </h5>
                <p style=\"margin-bottom: 20px; color: #666;\">
                    <strong>Código:</strong> TEST-001<br>
                    <strong>Programa:</strong> Programa de Prueba
                </p>

                <div class=\"form-group\" style=\"margin-bottom: 15px;\">
                    <label for=\"fechaInicio\" style=\"font-weight: bold; color: #333; display: block; margin-bottom: 5px;\">
                        Fecha de Inicio:
                    </label>
                    <input type=\"date\" id=\"fechaInicio\" class=\"swal2-input\" style=\"width: 100%; padding: 10px; margin: 0;\">
                </div>

                <div class=\"form-group\" style=\"margin-bottom: 15px;\">
                    <label for=\"fechaFin\" style=\"font-weight: bold; color: #333; display: block; margin-bottom: 5px;\">
                        Fecha de Finalización:
                    </label>
                    <input type=\"date\" id=\"fechaFin\" class=\"swal2-input\" style=\"width: 100%; padding: 10px; margin: 0;\">
                </div>

                <p style=\"margin-top: 15px; padding: 10px; background: #f8f9fa; border-left: 3px solid #5867dd; font-size: 13px; color: #555;\">
                    ℹ️ Estas fechas se mostrarán en el reporte PDF de calificaciones.
                </p>
            </div>
        `,
        width: '600px',
        showCancelButton: true,
        confirmButtonColor: '#5867dd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '🖨️ Generar PDF',
        cancelButtonText: 'Cancelar',
        focusConfirm: false,
        preConfirm: () => {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;

            if (!fechaInicio || !fechaFin) {
                Swal.showValidationMessage('Por favor, ingrese ambas fechas');
                return false;
            }

            if (fechaInicio > fechaFin) {
                Swal.showValidationMessage('La fecha de inicio debe ser anterior o igual a la fecha de finalización');
                return false;
            }

            return { fechaInicio: fechaInicio, fechaFin: fechaFin };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                icon: 'success',
                title: 'Fechas Válidas',
                html: `
                    <p><strong>Fecha Inicio:</strong> \${result.value.fechaInicio}</p>
                    <p><strong>Fecha Fin:</strong> \${result.value.fechaFin}</p>
                    <p style=\"margin-top:15px;color:#666;\">En la aplicación real, ahora se generaría el PDF</p>
                `
            });
        }
    });
});
</script>";

echo "</body></html>";
?>
