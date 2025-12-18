<?php
/**
 * Script de Corrección - Actualizar Monto Pagado en estudianteprograma
 *
 * Este script corrige los registros donde el campo 'montoPagado' está incorrecto.
 * Solo corrige registros con PAGO COMPLETO donde:
 *   montoPagado = Costo del Programa (SIN descuento)
 * Y debe ser:
 *   montoPagado = Costo del Programa - Descuento
 */

require_once 'modelos/conexion.modelo.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
h2 { color: #667eea; margin-top: 30px; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
th { background: #667eea; color: white; }
tr:nth-child(even) { background: #f8f9fa; }
.ok { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.warning { color: #ffc107; font-weight: bold; }
.info { background: #e7f3ff; padding: 15px; border-left: 4px solid #3699ff; margin: 20px 0; }
.success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; }
.btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px; cursor: pointer; border: none; font-size: 14px; }
.btn:hover { background: #5568d3; }
.btn-danger { background: #dc3545; }
.btn-danger:hover { background: #c82333; }
</style></head><body><div class='container'>";

echo "<h1>🔧 Corrección de Monto Pagado</h1>";
echo "<p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    $pdo = Conexion::Conectar();

    // Obtener registros con pago completo que necesitan corrección
    $stmt = $pdo->prepare("
        SELECT
            ep.idInscripcion,
            ep.EstudianteID,
            CONCAT(e.Nombre, ' ', e.Apaterno, ' ', e.Amaterno) as Estudiante,
            ep.ProgramaID,
            p.NombrePrograma,
            p.Costo as CostoPrograma,
            ep.costomatricula,
            ep.montoPagado,
            ep.montoDescuento,
            ep.porcentajeDescuento,
            ep.pagoCompleto,
            (p.Costo - ep.montoDescuento) as MontoEsperado
        FROM estudianteprograma ep
        INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        WHERE ep.pagoCompleto = 1
        ORDER BY ep.FechaInscripcion DESC
    ");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filtrar solo los que necesitan corrección
    $registrosACorregir = array_filter($registros, function($reg) {
        $montoPagado = floatval($reg['montoPagado']);
        $montoEsperado = floatval($reg['CostoPrograma']) - floatval($reg['montoDescuento']);
        $diferencia = abs($montoPagado - $montoEsperado);
        return $diferencia > 0.01;
    });

    if (empty($registrosACorregir)) {
        echo "<div class='success'>";
        echo "<h2>✓ ¡No hay registros que necesiten corrección!</h2>";
        echo "<p>Todos los registros de pago completo tienen los montos correctos.</p>";
        echo "</div>";
    } else {
        $totalACorregir = count($registrosACorregir);

        echo "<div class='warning'>";
        echo "<h2>⚠ Registros que necesitan corrección: $totalACorregir</h2>";
        echo "</div>";

        // Verificar si se debe ejecutar la corrección
        $ejecutarCorreccion = isset($_POST['confirmar_correccion']) && $_POST['confirmar_correccion'] == '1';

        if (!$ejecutarCorreccion) {
            // Mostrar vista previa de lo que se va a corregir
            echo "<div class='info'>";
            echo "<p><strong>Vista previa:</strong> A continuación se muestran los registros que se van a corregir.</p>";
            echo "<p>Revise cuidadosamente antes de continuar.</p>";
            echo "</div>";

            echo "<table>";
            echo "<tr>
                    <th>ID</th>
                    <th>Estudiante</th>
                    <th>Programa</th>
                    <th>Costo Original</th>
                    <th>Descuento</th>
                    <th>Monto Actual<br>(INCORRECTO)</th>
                    <th>Monto Nuevo<br>(CORRECTO)</th>
                  </tr>";

            foreach ($registrosACorregir as $reg) {
                $costoPrograma = floatval($reg['CostoPrograma']);
                $montoDescuento = floatval($reg['montoDescuento']);
                $porcentajeDescuento = floatval($reg['porcentajeDescuento']);
                $montoPagadoActual = floatval($reg['montoPagado']);
                $montoNuevo = $costoPrograma - $montoDescuento;

                echo "<tr>";
                echo "<td>" . $reg['idInscripcion'] . "</td>";
                echo "<td>" . htmlspecialchars($reg['Estudiante']) . "</td>";
                echo "<td>" . htmlspecialchars($reg['NombrePrograma']) . "</td>";
                echo "<td>Bs. " . number_format($costoPrograma, 2) . "</td>";
                echo "<td>Bs. " . number_format($montoDescuento, 2) . " (" . number_format($porcentajeDescuento, 1) . "%)</td>";
                echo "<td><span class='error'>Bs. " . number_format($montoPagadoActual, 2) . "</span></td>";
                echo "<td><span class='ok'>Bs. " . number_format($montoNuevo, 2) . "</span></td>";
                echo "</tr>";
            }

            echo "</table>";

            echo "<div class='info'>";
            echo "<h3>⚠ Importante:</h3>";
            echo "<ul>";
            echo "<li>Esta corrección actualizará el campo <code>montoPagado</code> en la tabla <code>estudianteprograma</code></li>";
            echo "<li>También actualizará los costos de los módulos en la tabla <code>pagomodulo</code> para reflejar el nuevo monto distribuido</li>";
            echo "<li>Se recomienda hacer un respaldo de la base de datos antes de ejecutar</li>";
            echo "</ul>";
            echo "</div>";

            // Botón para confirmar
            echo "<form method='POST'>";
            echo "<input type='hidden' name='confirmar_correccion' value='1'>";
            echo "<button type='submit' class='btn' onclick=\"return confirm('¿Está seguro de que desea corregir estos $totalACorregir registros?')\">✓ Ejecutar Corrección</button>";
            echo "<a href='diagnostico_monto_pagado.php' class='btn btn-danger'>✗ Cancelar</a>";
            echo "</form>";

        } else {
            // EJECUTAR LA CORRECCIÓN
            echo "<h2>🔄 Ejecutando correcciones...</h2>";

            $corregidos = 0;
            $errores = 0;

            $pdo->beginTransaction();

            try {
                foreach ($registrosACorregir as $reg) {
                    $idInscripcion = intval($reg['idInscripcion']);
                    $costoPrograma = floatval($reg['CostoPrograma']);
                    $montoDescuento = floatval($reg['montoDescuento']);
                    $montoNuevo = $costoPrograma - $montoDescuento;

                    // 1. Actualizar montoPagado en estudianteprograma
                    $stmtUpdate = $pdo->prepare("
                        UPDATE estudianteprograma
                        SET montoPagado = :montoNuevo
                        WHERE idInscripcion = :idInscripcion
                    ");
                    $stmtUpdate->bindParam(':montoNuevo', $montoNuevo, PDO::PARAM_STR);
                    $stmtUpdate->bindParam(':idInscripcion', $idInscripcion, PDO::PARAM_INT);

                    if ($stmtUpdate->execute()) {
                        // 2. Actualizar costos de módulos (redistribuir el nuevo monto)
                        // Obtener módulos pagados de esta inscripción
                        $stmtModulos = $pdo->prepare("
                            SELECT Idpagomodulo, IdModulo
                            FROM pagomodulo
                            WHERE idinscripcion = :idInscripcion
                            AND Estado = 'PAGADO'
                        ");
                        $stmtModulos->bindParam(':idInscripcion', $idInscripcion, PDO::PARAM_INT);
                        $stmtModulos->execute();
                        $modulos = $stmtModulos->fetchAll(PDO::FETCH_ASSOC);

                        if (count($modulos) > 0) {
                            // Calcular nuevo costo por módulo
                            $costoPorModulo = $montoNuevo / count($modulos);

                            // Actualizar cada módulo
                            $stmtUpdateModulo = $pdo->prepare("
                                UPDATE pagomodulo
                                SET costomodulo = :costoModulo
                                WHERE Idpagomodulo = :idPagoModulo
                            ");

                            foreach ($modulos as $modulo) {
                                $stmtUpdateModulo->bindParam(':costoModulo', $costoPorModulo, PDO::PARAM_STR);
                                $stmtUpdateModulo->bindParam(':idPagoModulo', $modulo['Idpagomodulo'], PDO::PARAM_INT);
                                $stmtUpdateModulo->execute();
                            }
                        }

                        $corregidos++;
                        echo "<p class='ok'>✓ Corregido: " . htmlspecialchars($reg['Estudiante']) . " (ID: $idInscripcion) - Nuevo monto: Bs. " . number_format($montoNuevo, 2) . "</p>";
                    } else {
                        $errores++;
                        echo "<p class='error'>✗ Error al corregir ID: $idInscripcion</p>";
                    }
                }

                $pdo->commit();

                echo "<div class='success'>";
                echo "<h2>✓ Corrección completada!</h2>";
                echo "<p><strong>Registros corregidos:</strong> $corregidos</p>";
                if ($errores > 0) {
                    echo "<p><strong>Errores:</strong> $errores</p>";
                }
                echo "<p><a href='diagnostico_monto_pagado.php' class='btn'>Ver Diagnóstico Actualizado</a></p>";
                echo "</div>";

            } catch (Exception $e) {
                $pdo->rollBack();
                echo "<div class='error'>";
                echo "<h2>✗ Error durante la corrección</h2>";
                echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p>Los cambios han sido revertidos.</p>";
                echo "</div>";
            }
        }
    }

} catch (PDOException $e) {
    echo "<p class='error'>Error al consultar la base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div></body></html>";
?>
