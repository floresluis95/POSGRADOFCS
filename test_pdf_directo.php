<?php
/**
 * Test Directo de PDF - Redirección
 */

session_start();
$_SESSION['Validar'] = true;
$_SESSION['Usuario'] = '1234567';

// Parámetros de prueba
$params = [
    'moduloID' => '1',
    'programaID' => '1',
    'moduloNombre' => 'Módulo de Prueba',
    'moduloCodigo' => 'TEST-001',
    'programaNombre' => 'Programa de Prueba',
    'gradoAcademico' => 'Maestría',
    'docenteNombre' => 'Dr. Juan Pérez',
    'fechaInicio' => '2024-01-15',
    'fechaFin' => '2024-02-28'
];

// Construir URL
$url = 'vistas/componentes/reporte-calificaciones-pdf.php?' . http_build_query($params);

// Mostrar información
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test PDF Directo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
        }
        .url {
            background: #2d3748;
            color: #68d391;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn:hover {
            background: #5867dd;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Directo de Generación de PDF</h1>

        <div class="info">
            <h3>Parámetros de Prueba:</h3>
            <ul>
                <?php foreach ($params as $key => $value): ?>
                    <li><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <h3>URL Generada:</h3>
        <div class="url"><?php echo htmlspecialchars($url); ?></div>

        <?php
        // Verificar que el archivo existe
        $archivoPDF = __DIR__ . '/vistas/componentes/reporte-calificaciones-pdf.php';
        if (file_exists($archivoPDF)) {
            echo '<p class="success">✅ Archivo PDF existe: ' . $archivoPDF . '</p>';
        } else {
            echo '<p class="error">❌ Archivo PDF NO existe: ' . $archivoPDF . '</p>';
        }
        ?>

        <h3>Opciones de Prueba:</h3>

        <a href="<?php echo $url; ?>" target="_blank" class="btn">
            🖨️ Abrir PDF en Nueva Pestaña
        </a>

        <a href="<?php echo $url; ?>" class="btn">
            📄 Abrir PDF en Misma Pestaña
        </a>

        <button onclick="testPDFConAjax()" class="btn" style="background: #1dc9b7;">
            🔍 Test con AJAX
        </button>

        <div id="resultado" style="margin-top: 20px;"></div>

        <hr style="margin: 30px 0;">

        <h3>Instrucciones:</h3>
        <ol>
            <li>Click en "Abrir PDF en Nueva Pestaña" para verificar que el PDF se genera</li>
            <li>Si no se abre:
                <ul>
                    <li>Revisa la consola del navegador (F12)</li>
                    <li>Verifica que no haya bloqueador de pop-ups</li>
                    <li>Verifica que existe el archivo en vistas/componentes/</li>
                </ul>
            </li>
            <li>Si el PDF está en blanco:
                <ul>
                    <li>Verifica que hay calificaciones en la base de datos</li>
                    <li>Revisa los logs de PHP (xampp/apache/logs/error.log)</li>
                </ul>
            </li>
        </ol>

        <hr style="margin: 30px 0;">

        <h3>Enlaces Rápidos:</h3>
        <a href="test_pdf_calificaciones.php" class="btn" style="background: #5867dd;">
            📊 Test Completo de PDF
        </a>
        <a href="index.php?ruta=rnotasestudiante" class="btn" style="background: #1dc9b7;">
            📝 Ir a rnotasestudiante
        </a>
    </div>

    <script>
    function testPDFConAjax() {
        const url = '<?php echo $url; ?>';
        const resultado = document.getElementById('resultado');

        resultado.innerHTML = '<p style="color: blue;">⏳ Probando acceso al PDF...</p>';

        fetch(url)
            .then(response => {
                if (response.ok) {
                    resultado.innerHTML = '<p style="color: green;">✅ El PDF se puede acceder correctamente</p>';
                    resultado.innerHTML += '<p>Status: ' + response.status + '</p>';
                    resultado.innerHTML += '<p>Content-Type: ' + response.headers.get('Content-Type') + '</p>';

                    // Intentar abrir
                    window.open(url, '_blank');
                } else {
                    resultado.innerHTML = '<p style="color: red;">❌ Error al acceder al PDF</p>';
                    resultado.innerHTML += '<p>Status: ' + response.status + '</p>';
                }
            })
            .catch(error => {
                resultado.innerHTML = '<p style="color: red;">❌ Error: ' + error.message + '</p>';
            });
    }
    </script>
</body>
</html>
