<?php
/**
 * Script para actualizar descuentos retroactivos
 * Calcula y actualiza descuentos para inscripciones antiguas
 * que se hicieron antes de implementar el sistema
 */

require_once "modelos/conexion.modelo.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Descuentos Retroactivos</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        h1 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 15px; }
        h2 { color: #764ba2; margin-top: 30px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .highlight { background: #fff3cd !important; }
        .btn { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; }
        .btn:hover { background: #218838; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Actualizar Descuentos Retroactivos</h1>

        <div class="warning">
            <strong>⚠ IMPORTANTE:</strong><br>
            Este script actualizará los descuentos de inscripciones antiguas que se hicieron con pago completo ANTES de implementar el sistema de descuentos.<br>
            Calcula el descuento basándose en la diferencia entre el costo del programa y el monto pagado.
        </div>

        <?php
        try {
            $pdo = Conexion::conectar();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Buscar inscripciones con pago completo que no tienen descuento registrado
            $stmt = $pdo->prepare(
                "SELECT
                    ep.idInscripcion,
                    ep.EstudianteID,
                    ep.ProgramaID,
                    ep.montoPagado,
                    ep.porcentajeDescuento,
                    ep.montoDescuento,
                    ep.FechaInscripcion,
                    CONCAT(e.Nombre, ' ', e.Apaterno, ' ', e.Amaterno) as NombreCompleto,
                    e.Ci,
                    p.NombrePrograma,
                    p.Codigo,
                    p.Costo as CostoPrograma,
                    p.CostoMatricula
                FROM estudianteprograma ep
                INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                WHERE ep.pagoCompleto = 1
                AND (ep.porcentajeDescuento = 0 OR ep.porcentajeDescuento IS NULL)
                AND (ep.montoDescuento = 0 OR ep.montoDescuento IS NULL)
                AND ep.Estado = 'ACTIVO'
                ORDER BY ep.FechaInscripcion DESC"
            );
            $stmt->execute();
            $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($inscripciones)) {
                echo '<div class="success">';
                echo '<h2>✓ No hay inscripciones por actualizar</h2>';
                echo '<p>Todas las inscripciones con pago completo ya tienen su descuento registrado correctamente.</p>';
                echo '</div>';
            } else {
                echo '<div class="info">';
                echo '<h2>📋 Se encontraron ' . count($inscripciones) . ' inscripción(es) para actualizar</h2>';
                echo '</div>';

                // Calcular descuentos
                $actualizaciones = [];
                foreach ($inscripciones as $insc) {
                    $costoTotal = floatval($insc['CostoPrograma']) + floatval($insc['CostoMatricula']);
                    $montoPagado = floatval($insc['montoPagado']);

                    // Solo si pagó menos que el costo total
                    if ($montoPagado < $costoTotal && $montoPagado > 0) {
                        $montoDescuento = $costoTotal - $montoPagado;
                        $porcentajeDescuento = ($montoDescuento / $costoTotal) * 100;

                        $actualizaciones[] = [
                            'idInscripcion' => $insc['idInscripcion'],
                            'estudiante' => $insc['NombreCompleto'],
                            'ci' => $insc['Ci'],
                            'programa' => $insc['NombrePrograma'],
                            'costoTotal' => $costoTotal,
                            'montoPagado' => $montoPagado,
                            'montoDescuento' => $montoDescuento,
                            'porcentajeDescuento' => $porcentajeDescuento
                        ];
                    }
                }

                if (empty($actualizaciones)) {
                    echo '<div class="info">';
                    echo '<p>No se detectaron descuentos en las inscripciones encontradas.</p>';
                    echo '<p>Posiblemente todas pagaron el monto completo sin descuento.</p>';
                    echo '</div>';
                } else {
                    // Mostrar tabla de actualizaciones propuestas
                    echo '<h2>💰 Descuentos Calculados</h2>';
                    echo '<table>';
                    echo '<tr>
                            <th>Estudiante</th>
                            <th>CI</th>
                            <th>Programa</th>
                            <th>Costo Total</th>
                            <th>Pagado</th>
                            <th>Descuento</th>
                            <th>%</th>
                          </tr>';

                    foreach ($actualizaciones as $act) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($act['estudiante']) . '</td>';
                        echo '<td>' . htmlspecialchars($act['ci']) . '</td>';
                        echo '<td>' . htmlspecialchars($act['programa']) . '</td>';
                        echo '<td>Bs. ' . number_format($act['costoTotal'], 2) . '</td>';
                        echo '<td>Bs. ' . number_format($act['montoPagado'], 2) . '</td>';
                        echo '<td class="highlight">Bs. ' . number_format($act['montoDescuento'], 2) . '</td>';
                        echo '<td class="highlight">' . number_format($act['porcentajeDescuento'], 2) . '%</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    // Aplicar actualizaciones
                    if (isset($_GET['confirmar']) && $_GET['confirmar'] == 'si') {
                        echo '<h2>🔄 Aplicando actualizaciones...</h2>';

                        $pdo->beginTransaction();

                        $stmtUpdate = $pdo->prepare(
                            "UPDATE estudianteprograma
                            SET porcentajeDescuento = :porcentaje,
                                montoDescuento = :monto
                            WHERE idInscripcion = :id"
                        );

                        $actualizados = 0;
                        $errores = 0;

                        foreach ($actualizaciones as $act) {
                            try {
                                $stmtUpdate->execute([
                                    ':porcentaje' => $act['porcentajeDescuento'],
                                    ':monto' => $act['montoDescuento'],
                                    ':id' => $act['idInscripcion']
                                ]);
                                $actualizados++;

                                echo '<div class="success">';
                                echo '✓ Actualizado: ' . htmlspecialchars($act['estudiante']) . ' - ' . number_format($act['porcentajeDescuento'], 2) . '% de descuento';
                                echo '</div>';
                            } catch (Exception $e) {
                                $errores++;
                                echo '<div class="error">';
                                echo '✗ Error al actualizar: ' . htmlspecialchars($act['estudiante']) . '<br>';
                                echo 'Error: ' . htmlspecialchars($e->getMessage());
                                echo '</div>';
                            }
                        }

                        if ($errores == 0) {
                            $pdo->commit();
                            echo '<div class="success">';
                            echo '<h2>✅ Actualización Completada</h2>';
                            echo '<p>Se actualizaron <strong>' . $actualizados . '</strong> registro(s) exitosamente.</p>';
                            echo '</div>';
                        } else {
                            $pdo->rollBack();
                            echo '<div class="error">';
                            echo '<h2>❌ Actualización Cancelada</h2>';
                            echo '<p>Se encontraron errores. No se aplicó ningún cambio.</p>';
                            echo '</div>';
                        }

                    } else {
                        // Mostrar botón de confirmación
                        echo '<div class="warning">';
                        echo '<h2>⚠ Confirmar Actualización</h2>';
                        echo '<p>¿Deseas aplicar estos descuentos a las inscripciones?</p>';
                        echo '<p><strong>Esta acción modificará ' . count($actualizaciones) . ' registro(s) en la base de datos.</strong></p>';
                        echo '<a href="?confirmar=si" class="btn" style="margin-right: 10px;">✓ Sí, Actualizar</a>';
                        echo '<a href="matriculados" class="btn" style="background: #6c757d;">✗ Cancelar</a>';
                        echo '</div>';
                    }
                }
            }

        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h2>❌ Error</h2>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '</div>';
        }
        ?>

        <hr style="margin: 40px 0;">
        <p style="text-align: center; color: #999;">
            <strong>Script de actualización retroactiva</strong><br>
            Fecha: <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>
