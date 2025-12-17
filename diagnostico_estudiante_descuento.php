<?php
/**
 * Diagnóstico de descuento para estudiante específica
 * Busca por CI o nombre para ver datos completos
 */

require_once "modelos/conexion.modelo.php";

header('Content-Type: text/html; charset=utf-8');

$ci = '13935838'; // CI de GABRIELA NICOLE PACO TORRICO
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico: Descuento de Estudiante</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        h2 { color: #764ba2; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
        .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        .highlight { background: #fff3cd !important; font-weight: bold; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico: Descuento de Estudiante</h1>

        <?php
        try {
            $pdo = Conexion::conectar();

            echo '<div class="info-box">';
            echo '<strong>Buscando estudiante con CI: ' . htmlspecialchars($ci) . '</strong>';
            echo '</div>';

            // Buscar estudiante
            $stmtEstudiante = $pdo->prepare(
                "SELECT * FROM estudiante WHERE Ci = :ci"
            );
            $stmtEstudiante->bindParam(':ci', $ci, PDO::PARAM_STR);
            $stmtEstudiante->execute();
            $estudiante = $stmtEstudiante->fetch(PDO::FETCH_ASSOC);

            if (!$estudiante) {
                echo '<div class="alert-box">';
                echo '<span class="error">❌ No se encontró estudiante con CI: ' . htmlspecialchars($ci) . '</span>';
                echo '</div>';
                exit;
            }

            // Mostrar datos del estudiante
            echo '<h2>👤 Datos del Estudiante</h2>';
            echo '<table>';
            echo '<tr><th>Campo</th><th>Valor</th></tr>';
            echo '<tr><td>ID</td><td>' . $estudiante['EstudianteID'] . '</td></tr>';
            echo '<tr><td>Nombre Completo</td><td><strong>' . htmlspecialchars($estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno']) . '</strong></td></tr>';
            echo '<tr><td>CI</td><td>' . htmlspecialchars($estudiante['Ci']) . '</td></tr>';
            echo '<tr><td>Correo</td><td>' . htmlspecialchars($estudiante['Correo']) . '</td></tr>';
            echo '</table>';

            // Buscar inscripciones
            $stmtInscripciones = $pdo->prepare(
                "SELECT
                    ep.*,
                    p.NombrePrograma,
                    p.Codigo,
                    p.Costo as CostoPrograma
                FROM estudianteprograma ep
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.EstudianteID = :estudianteID
                ORDER BY ep.FechaInscripcion DESC"
            );
            $stmtInscripciones->bindParam(':estudianteID', $estudiante['EstudianteID'], PDO::PARAM_INT);
            $stmtInscripciones->execute();
            $inscripciones = $stmtInscripciones->fetchAll(PDO::FETCH_ASSOC);

            if (empty($inscripciones)) {
                echo '<div class="alert-box">';
                echo '<span class="warning">⚠ El estudiante no tiene inscripciones registradas</span>';
                echo '</div>';
                exit;
            }

            // Mostrar cada inscripción
            foreach ($inscripciones as $idx => $insc) {
                echo '<h2>📋 Inscripción #' . ($idx + 1) . '</h2>';
                echo '<table>';
                echo '<tr><th>Campo</th><th>Valor</th><th>Observación</th></tr>';

                // ID Inscripción
                echo '<tr><td><strong>ID Inscripción</strong></td><td>' . $insc['idInscripcion'] . '</td><td>-</td></tr>';

                // Programa
                echo '<tr><td><strong>Programa</strong></td><td>' . htmlspecialchars($insc['NombrePrograma']) . '</td><td>Código: ' . htmlspecialchars($insc['Codigo']) . '</td></tr>';

                // Fecha
                echo '<tr><td><strong>Fecha Inscripción</strong></td><td>' . date('d/m/Y', strtotime($insc['FechaInscripcion'])) . '</td><td>-</td></tr>';

                // Costo Matrícula
                $costoMat = floatval($insc['costomatricula']);
                $obsMatricula = $costoMat == 0 ? '<span class="warning">⚠ CERO (no se cobró matrícula)</span>' : '<span class="success">✓ OK</span>';
                echo '<tr><td><strong>Costo Matrícula</strong></td><td>Bs. ' . number_format($costoMat, 2) . '</td><td>' . $obsMatricula . '</td></tr>';

                // Monto Pagado
                $montoPag = floatval($insc['montoPagado']);
                echo '<tr class="highlight"><td><strong>Monto Pagado</strong></td><td>Bs. ' . number_format($montoPag, 2) . '</td><td><span class="success">✓ Total pagado por el estudiante</span></td></tr>';

                // Pago Completo
                $pagoComp = intval($insc['pagoCompleto']);
                $txtPagoComp = $pagoComp == 1 ? '<span class="success">✓ SÍ - PAGO COMPLETO</span>' : '<span class="warning">NO - Solo matrícula</span>';
                echo '<tr><td><strong>Pago Completo</strong></td><td>' . $pagoComp . '</td><td>' . $txtPagoComp . '</td></tr>';

                // Porcentaje Descuento
                $porcentajeDesc = isset($insc['porcentajeDescuento']) ? floatval($insc['porcentajeDescuento']) : null;
                if ($porcentajeDesc === null) {
                    echo '<tr class="highlight"><td><strong>Porcentaje Descuento</strong></td><td><span class="error">NULL</span></td><td><span class="error">❌ Campo no existe en BD</span></td></tr>';
                } else {
                    $obsDesc = $porcentajeDesc > 0 ? '<span class="success">✓ Descuento aplicado</span>' : '<span class="warning">⚠ Sin descuento (0%)</span>';
                    echo '<tr class="highlight"><td><strong>Porcentaje Descuento</strong></td><td>' . number_format($porcentajeDesc, 2) . '%</td><td>' . $obsDesc . '</td></tr>';
                }

                // Monto Descuento
                $montoDesc = isset($insc['montoDescuento']) ? floatval($insc['montoDescuento']) : null;
                if ($montoDesc === null) {
                    echo '<tr class="highlight"><td><strong>Monto Descuento</strong></td><td><span class="error">NULL</span></td><td><span class="error">❌ Campo no existe en BD</span></td></tr>';
                } else {
                    $obsMontoDesc = $montoDesc > 0 ? '<span class="success">✓ Ahorro: Bs. ' . number_format($montoDesc, 2) . '</span>' : '<span class="warning">⚠ Sin descuento (Bs. 0.00)</span>';
                    echo '<tr class="highlight"><td><strong>Monto Descuento</strong></td><td>Bs. ' . number_format($montoDesc, 2) . '</td><td>' . $obsMontoDesc . '</td></tr>';
                }

                // Voucher
                echo '<tr><td><strong>N° Voucher</strong></td><td>' . htmlspecialchars($insc['nvauchermatricula']) . '</td><td>-</td></tr>';

                // Estado
                $colorEstado = $insc['Estado'] == 'ACTIVO' ? 'success' : 'error';
                echo '<tr><td><strong>Estado</strong></td><td><span class="' . $colorEstado . '">' . htmlspecialchars($insc['Estado']) . '</span></td><td>-</td></tr>';

                echo '</table>';

                // Análisis del descuento
                echo '<h2>🔍 Análisis del Descuento</h2>';
                echo '<div class="info-box">';

                if ($porcentajeDesc === null || $montoDesc === null) {
                    echo '<span class="error"><strong>❌ PROBLEMA: Los campos de descuento NO EXISTEN en la base de datos</strong></span><br>';
                    echo '<p>Debes ejecutar la migración para agregar los campos:</p>';
                    echo '<pre>http://localhost/POSGRADOFCS/verificar_campos_descuento.php</pre>';
                } elseif ($porcentajeDesc == 0 && $montoDesc == 0) {
                    echo '<span class="warning"><strong>⚠ Esta inscripción NO tiene descuento registrado</strong></span><br><br>';
                    echo '<strong>Posibles razones:</strong><br>';
                    echo '<ul>';
                    echo '<li>Se registró antes de implementar el sistema de descuentos</li>';
                    echo '<li>No se aplicó descuento en el momento de la inscripción</li>';
                    echo '<li>El descuento no se guardó correctamente</li>';
                    echo '</ul>';

                    // Calcular descuento basado en costo del programa
                    $costoPrograma = floatval($insc['CostoPrograma']);
                    if ($costoPrograma > 0 && $montoPag > 0 && $montoPag < $costoPrograma) {
                        $descuentoCalculado = $costoPrograma - $montoPag;
                        $porcentajeCalculado = ($descuentoCalculado / $costoPrograma) * 100;

                        echo '<br><strong>💡 Descuento calculado a partir de los datos:</strong><br>';
                        echo '<table>';
                        echo '<tr><th>Concepto</th><th>Valor</th></tr>';
                        echo '<tr><td>Costo del Programa</td><td>Bs. ' . number_format($costoPrograma, 2) . '</td></tr>';
                        echo '<tr><td>Monto Pagado</td><td>Bs. ' . number_format($montoPag, 2) . '</td></tr>';
                        echo '<tr><td>Descuento (diferencia)</td><td>Bs. ' . number_format($descuentoCalculado, 2) . '</td></tr>';
                        echo '<tr class="highlight"><td><strong>Porcentaje</strong></td><td><strong>' . number_format($porcentajeCalculado, 2) . '%</strong></td></tr>';
                        echo '</table>';

                        echo '<br><strong>🔧 Para actualizar esta inscripción con el descuento correcto:</strong>';
                        echo '<pre>';
                        echo "UPDATE estudianteprograma\n";
                        echo "SET porcentajeDescuento = " . number_format($porcentajeCalculado, 2) . ",\n";
                        echo "    montoDescuento = " . number_format($descuentoCalculado, 2) . "\n";
                        echo "WHERE idInscripcion = " . $insc['idInscripcion'] . ";";
                        echo '</pre>';
                    }
                } else {
                    echo '<span class="success"><strong>✓ Descuento registrado correctamente</strong></span><br><br>';
                    echo '<table>';
                    echo '<tr><th>Concepto</th><th>Valor</th></tr>';
                    echo '<tr><td>Porcentaje Descuento</td><td>' . number_format($porcentajeDesc, 2) . '%</td></tr>';
                    echo '<tr><td>Monto Descuento</td><td>Bs. ' . number_format($montoDesc, 2) . '</td></tr>';
                    echo '<tr><td>Monto Original</td><td>Bs. ' . number_format($montoPag + $montoDesc, 2) . '</td></tr>';
                    echo '<tr class="highlight"><td><strong>Monto Final Pagado</strong></td><td><strong>Bs. ' . number_format($montoPag, 2) . '</strong></td></tr>';
                    echo '</table>';
                }

                echo '</div>';
            }

        } catch (Exception $e) {
            echo '<div class="alert-box">';
            echo '<span class="error"><strong>❌ Error:</strong></span><br>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '</div>';
        }
        ?>

        <hr style="margin: 40px 0;">
        <p style="text-align: center; color: #999;">
            <strong>Diagnóstico completado</strong><br>
            Fecha: <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>
