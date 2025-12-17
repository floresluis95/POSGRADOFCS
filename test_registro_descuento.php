<?php
/**
 * Script de prueba para ver qué datos se están enviando
 * al registrar una inscripción con descuento
 */

// Registrar TODOS los datos POST en un log
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logFile = __DIR__ . '/debug_inscripcion.log';
    $timestamp = date('Y-m-d H:i:s');

    $logContent = "\n\n========================================\n";
    $logContent .= "REGISTRO DE INSCRIPCIÓN - $timestamp\n";
    $logContent .= "========================================\n\n";

    $logContent .= "DATOS POST:\n";
    $logContent .= print_r($_POST, true);

    $logContent .= "\n\nDATOS ESPECÍFICOS:\n";
    $logContent .= "- idcliente: " . (isset($_POST['idcliente']) ? $_POST['idcliente'] : 'NO ENVIADO') . "\n";
    $logContent .= "- programa: " . (isset($_POST['programa']) ? $_POST['programa'] : 'NO ENVIADO') . "\n";
    $logContent .= "- montoMatricula: " . (isset($_POST['montoMatricula']) ? $_POST['montoMatricula'] : 'NO ENVIADO') . "\n";
    $logContent .= "- pagoCompleto: " . (isset($_POST['pagoCompleto']) ? $_POST['pagoCompleto'] : 'NO ENVIADO') . "\n";
    $logContent .= "- costoTotalPrograma: " . (isset($_POST['costoTotalPrograma']) ? $_POST['costoTotalPrograma'] : 'NO ENVIADO') . "\n";
    $logContent .= "- costoMatriculaPrograma: " . (isset($_POST['costoMatriculaPrograma']) ? $_POST['costoMatriculaPrograma'] : 'NO ENVIADO') . "\n";
    $logContent .= "- porcentajeDescuento: " . (isset($_POST['porcentajeDescuento']) ? $_POST['porcentajeDescuento'] : 'NO ENVIADO') . "\n";
    $logContent .= "- montoDescuento: " . (isset($_POST['montoDescuento']) ? $_POST['montoDescuento'] : 'NO ENVIADO') . "\n";
    $logContent .= "- numeroVaucher: " . (isset($_POST['numeroVaucher']) ? $_POST['numeroVaucher'] : 'NO ENVIADO') . "\n";
    $logContent .= "- fechaInscripcion: " . (isset($_POST['fechaInscripcion']) ? $_POST['fechaInscripcion'] : 'NO ENVIADO') . "\n";

    file_put_contents($logFile, $logContent, FILE_APPEND);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Registro de Descuento</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test de Registro con Descuento</h1>

        <div class="info">
            <strong>📋 Instrucciones:</strong><br>
            1. Ve a la página de inscripción<br>
            2. Registra un estudiante con pago completo y descuento<br>
            3. Vuelve a esta página para ver los datos capturados
        </div>

        <?php
        $logFile = __DIR__ . '/debug_inscripcion.log';

        if (file_exists($logFile)) {
            echo '<h2>📝 Último registro capturado:</h2>';

            $logContent = file_get_contents($logFile);
            $registros = explode("========================================", $logContent);
            $ultimoRegistro = end($registros);

            if (trim($ultimoRegistro)) {
                echo '<pre>' . htmlspecialchars($ultimoRegistro) . '</pre>';

                echo '<div class="success">';
                echo '<strong>✓ Datos capturados</strong><br>';
                echo 'Ahora revisa si los valores son correctos.';
                echo '</div>';
            } else {
                echo '<div class="warning">';
                echo '<strong>⚠ No hay registros todavía</strong><br>';
                echo 'Registra una inscripción para ver los datos aquí.';
                echo '</div>';
            }

            echo '<h3>🔧 Acciones:</h3>';
            echo '<a href="inscripcion" class="btn">Ir a Inscripción</a>';
            echo '<a href="?limpiar=1" class="btn" style="background: #dc3545; margin-left: 10px;">Limpiar Log</a>';

        } else {
            echo '<div class="warning">';
            echo '<strong>⚠ No se ha registrado ninguna inscripción todavía</strong><br>';
            echo 'El archivo de log se creará automáticamente cuando registres una inscripción.';
            echo '</div>';

            echo '<a href="inscripcion" class="btn">Ir a Inscripción</a>';
        }

        if (isset($_GET['limpiar'])) {
            if (file_exists($logFile)) {
                unlink($logFile);
                echo '<div class="success">✓ Log limpiado</div>';
                echo '<meta http-equiv="refresh" content="1">';
            }
        }
        ?>

        <hr style="margin: 40px 0;">

        <h2>🔍 Diagnóstico de Problema</h2>

        <div class="info">
            <strong>Problema Común: El monto se guarda sin descuento</strong><br><br>

            <strong>Causa posible:</strong><br>
            El campo <code>montoMatricula</code> se actualiza con el monto CON descuento, pero el servidor usa ese valor directamente como <code>montoPagado</code>.<br><br>

            <strong>Lo que debería pasar:</strong><br>
            <ol>
                <li>Usuario aplica descuento del 10%</li>
                <li>Campo <code>montoMatricula</code> = Bs. 11,250 (con descuento)</li>
                <li>Campo <code>porcentajeDescuento</code> = 10</li>
                <li>Campo <code>montoDescuento</code> = Bs. 1,250</li>
                <li>Servidor guarda:
                    <ul>
                        <li><code>montoPagado</code> = Bs. 11,250</li>
                        <li><code>porcentajeDescuento</code> = 10</li>
                        <li><code>montoDescuento</code> = Bs. 1,250</li>
                    </ul>
                </li>
            </ol>

            <strong>Lo que puede estar pasando:</strong><br>
            El servidor está tomando el monto TOTAL sin descuento en lugar del campo <code>montoMatricula</code>.
        </div>

        <h2>📋 Verificar en el código:</h2>
        <pre>
Archivo: controladores/matricula.controlador.php

Buscar las líneas donde se asigna el monto:

if ($pagoCompleto) {
    $montoMatricula = 0;  ← AQUÍ PUEDE ESTAR EL PROBLEMA
    $montoPagado = $costoTotalPrograma;  ← ESTO DEBERÍA SER $_POST['montoMatricula']
} else {
    $montoMatricula = floatval($_POST['montoMatricula']);
    $montoPagado = $montoMatricula;
}

<strong style="color: red;">CORRECCIÓN NECESARIA:</strong>

if ($pagoCompleto) {
    $montoMatricula = 0;  // No se cobra matrícula
    // El monto pagado debe venir del formulario (ya tiene descuento aplicado)
    $montoPagado = floatval($_POST['montoMatricula']);
} else {
    $montoMatricula = floatval($_POST['montoMatricula']);
    $montoPagado = $montoMatricula;
}
        </pre>
    </div>
</body>
</html>
