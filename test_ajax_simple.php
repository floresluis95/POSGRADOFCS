<?php
/**
 * Test simple del endpoint AJAX
 * Accede a: http://localhost/POSGRADOFCS/test_ajax_simple.php
 */

// Simular una sesión válida
session_start();
$_SESSION['Validar'] = true;
$_SESSION['Usuario'] = '1234567'; // Cambia por un CI real de tu BD

// Simular POST
$_POST['accion'] = 'obtenerDocente';

// Capturar todo el output
ob_start();

// Incluir el archivo AJAX
include 'ajax/calificacion.ajax.php';

$output = ob_get_clean();

// Mostrar resultado
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test AJAX</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 Test AJAX - obtenerDocente</h1>

    <div class="box">
        <h2>Sesión Simulada:</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>

    <div class="box">
        <h2>Respuesta del AJAX:</h2>
        <pre><?php echo htmlspecialchars($output); ?></pre>
    </div>

    <div class="box">
        <h2>Validación JSON:</h2>
        <?php
        $json = json_decode($output, true);
        if ($json === null) {
            echo "<p class='error'>❌ ERROR: No es JSON válido</p>";
            echo "<p>Error: " . json_last_error_msg() . "</p>";
            echo "<p>Primeros 500 caracteres:</p>";
            echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . "</pre>";
        } else {
            echo "<p class='success'>✅ JSON válido</p>";
            echo "<pre>" . print_r($json, true) . "</pre>";
        }
        ?>
    </div>

    <div class="box">
        <h2>Longitud de la respuesta:</h2>
        <p><?php echo strlen($output); ?> caracteres</p>
    </div>

    <div class="box">
        <h2>Acciones:</h2>
        <p><a href="diagnostico_sesion_docente.php">Ir a Diagnóstico de Sesión</a></p>
        <p><a href="index.php?ruta=notasdocente">Ir a Notasdocente</a></p>
    </div>
</body>
</html>
