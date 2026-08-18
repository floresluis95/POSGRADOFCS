<?php
/**
 * Diagnóstico para vista de historial de módulos estudiante
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/modulopagos_core.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico - Módulos Estudiante</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .success { background-color: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 15px 0; }
        .error { background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        .info { background-color: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Diagnóstico - Historial de Módulos Estudiante</h2>

        <?php if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']): ?>
            <div class="error">
                <h3>❌ No hay sesión activa</h3>
                <p>Por favor, inicie sesión como estudiante primero.</p>
                <a href="ingreso" style="padding: 10px 20px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px;">Ir a Inicio de Sesión</a>
            </div>
        <?php else: ?>

            <div class="info">
                <h3>📊 Información de Sesión</h3>
                <p><strong>Usuario:</strong> <code><?php echo isset($_SESSION['Usuario']) ? $_SESSION['Usuario'] : 'NO DEFINIDO'; ?></code></p>
                <p><strong>Tipo:</strong> <code><?php echo isset($_SESSION['Tipo']) ? $_SESSION['Tipo'] : 'NO DEFINIDO'; ?></code></p>
                <p><strong>EstudianteID:</strong> <code><?php echo isset($_SESSION['EstudianteID']) ? $_SESSION['EstudianteID'] : 'NO DEFINIDO'; ?></code></p>
            </div>

            <?php
            if (isset($_SESSION['EstudianteID'])) {
                $estudianteID = $_SESSION['EstudianteID'];

                echo "<h3>1️⃣ Probando método ObtenerProgramasEstudianteConInscripcionModelo()</h3>";

                try {
                    $programas = PagoModuloModelo::ObtenerProgramasEstudianteConInscripcionModelo($estudianteID);

                    if (!empty($programas)) {
                        echo "<div class='success'>";
                        echo "<p>✅ Se encontraron " . count($programas) . " programas inscritos</p>";
                        echo "</div>";

                        echo "<h4>Programas encontrados:</h4>";
                        echo "<pre>" . print_r($programas, true) . "</pre>";

                        // Probar el método de detalle con el primer programa
                        if (count($programas) > 0) {
                            $primerPrograma = $programas[0];
                            $programaID = $primerPrograma['ProgramaID'];

                            echo "<h3>2️⃣ Probando método ObtenerDetalleModulosEstudianteModelo() con ProgramaID: {$programaID}</h3>";

                            $detalle = PagoModuloModelo::ObtenerDetalleModulosEstudianteModelo($estudianteID, $programaID);

                            echo "<div class='success'>";
                            echo "<p>✅ Método ejecutado exitosamente</p>";
                            echo "</div>";

                            echo "<h4>Detalle obtenido:</h4>";
                            echo "<pre>" . print_r($detalle, true) . "</pre>";

                            echo "<h4>Resumen:</h4>";
                            echo "<ul>";
                            echo "<li><strong>Total módulos:</strong> " . $detalle['resumen']['totalModulos'] . "</li>";
                            echo "<li><strong>Módulos pagados:</strong> " . $detalle['resumen']['modulosPagados'] . "</li>";
                            echo "<li><strong>Módulos pendientes:</strong> " . $detalle['resumen']['modulosPendientes'] . "</li>";
                            echo "<li><strong>Costo total:</strong> Bs. " . number_format($detalle['resumen']['costoTotal'], 2) . "</li>";
                            echo "<li><strong>Monto pagado:</strong> Bs. " . number_format($detalle['resumen']['montoPagado'], 2) . "</li>";
                            echo "<li><strong>Monto pendiente:</strong> Bs. " . number_format($detalle['resumen']['montoPendiente'], 2) . "</li>";
                            echo "</ul>";
                        }

                    } else {
                        echo "<div class='error'>";
                        echo "<p>❌ No se encontraron programas inscritos para el estudiante ID: {$estudianteID}</p>";
                        echo "</div>";
                    }

                } catch (Exception $e) {
                    echo "<div class='error'>";
                    echo "<p>❌ Error al ejecutar el método: " . $e->getMessage() . "</p>";
                    echo "</div>";
                }

                // Probar el endpoint AJAX
                echo "<h3>3️⃣ Probar endpoint AJAX</h3>";
                echo "<div class='info'>";
                echo "<p>Haga clic en los botones para probar los endpoints AJAX:</p>";
                echo "<button onclick='probarProgramas()' style='padding: 10px 20px; background-color: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px;'>Probar obtenerProgramasEstudiante</button>";
                echo "<button onclick='probarDetalle()' style='padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px;'>Probar obtenerDetalleModulos</button>";
                echo "<div id='resultado_ajax' style='margin-top: 20px;'></div>";
                echo "</div>";

            } else {
                echo "<div class='error'>";
                echo "<p>❌ No se encontró EstudianteID en la sesión</p>";
                echo "</div>";
            }
            ?>

        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="panel" style="padding: 10px 20px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px;">← Volver al Panel</a>
        </div>
    </div>

    <script src="vistas/recursos/assets/vendors/general/jquery/dist/jquery.js"></script>
    <script>
    function probarProgramas() {
        $('#resultado_ajax').html('<p>Cargando...</p>');

        $.ajax({
            url: 'ajax/pagomodulo.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                accion: 'obtenerProgramasEstudiante'
            },
            success: function(response) {
                $('#resultado_ajax').html('<pre style="background: #d4edda; padding: 15px; border-radius: 5px;">' + JSON.stringify(response, null, 2) + '</pre>');
            },
            error: function(xhr, status, error) {
                $('#resultado_ajax').html('<pre style="background: #f8d7da; padding: 15px; border-radius: 5px;">Error: ' + error + '\n\nRespuesta:\n' + xhr.responseText + '</pre>');
            }
        });
    }

    function probarDetalle() {
        $('#resultado_ajax').html('<p>Cargando...</p>');

        // Primero obtener un programaID
        $.ajax({
            url: 'ajax/pagomodulo.ajax.php',
            type: 'POST',
            dataType: 'json',
            data: {
                accion: 'obtenerProgramasEstudiante'
            },
            success: function(response) {
                if (response.success && response.programas.length > 0) {
                    var programaID = response.programas[0].ProgramaID;

                    $.ajax({
                        url: 'ajax/pagomodulo.ajax.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            accion: 'obtenerDetalleModulos',
                            programaID: programaID
                        },
                        success: function(response) {
                            $('#resultado_ajax').html('<pre style="background: #d4edda; padding: 15px; border-radius: 5px;">' + JSON.stringify(response, null, 2) + '</pre>');
                        },
                        error: function(xhr, status, error) {
                            $('#resultado_ajax').html('<pre style="background: #f8d7da; padding: 15px; border-radius: 5px;">Error: ' + error + '\n\nRespuesta:\n' + xhr.responseText + '</pre>');
                        }
                    });
                } else {
                    $('#resultado_ajax').html('<pre style="background: #f8d7da; padding: 15px; border-radius: 5px;">No se encontraron programas</pre>');
                }
            },
            error: function(xhr, status, error) {
                $('#resultado_ajax').html('<pre style="background: #f8d7da; padding: 15px; border-radius: 5px;">Error: ' + error + '\n\nRespuesta:\n' + xhr.responseText + '</pre>');
            }
        });
    }
    </script>
</body>
</html>
