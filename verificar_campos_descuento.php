<?php
/**
 * Script para verificar campos de descuento
 * Verifica si los campos ya existen y están correctos
 */

require_once "modelos/conexion.modelo.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Campos de Descuento</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        h1 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 15px; margin-bottom: 30px; }
        h2 { color: #764ba2; margin-top: 30px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 5px; color: #721c24; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 5px; color: #856404; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0; border-radius: 5px; color: #0c5460; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f8f9fa; }
        .highlight { background: #fff3cd !important; font-weight: bold; }
        .btn { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; margin-right: 10px; }
        .btn:hover { background: #218838; }
        .btn-primary { background: #667eea; }
        .btn-primary:hover { background: #5568d3; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .check-icon { color: #28a745; font-size: 24px; font-weight: bold; }
        .cross-icon { color: #dc3545; font-size: 24px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Campos de Descuento</h1>

        <?php
        try {
            $pdo = Conexion::conectar();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo '<div class="info">';
            echo '<strong>📋 Verificando estructura de la tabla estudianteprograma...</strong>';
            echo '</div>';

            // Verificar estructura completa
            $stmt = $pdo->prepare("DESCRIBE estudianteprograma");
            $stmt->execute();
            $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar campos específicos
            $porcentajeDescuentoExists = false;
            $montoDescuentoExists = false;
            $montoPagadoExists = false;
            $pagoCompletoExists = false;

            $infoPorcentaje = null;
            $infoMonto = null;

            foreach ($campos as $campo) {
                if ($campo['Field'] == 'porcentajeDescuento') {
                    $porcentajeDescuentoExists = true;
                    $infoPorcentaje = $campo;
                }
                if ($campo['Field'] == 'montoDescuento') {
                    $montoDescuentoExists = true;
                    $infoMonto = $campo;
                }
                if ($campo['Field'] == 'montoPagado') {
                    $montoPagadoExists = true;
                }
                if ($campo['Field'] == 'pagoCompleto') {
                    $pagoCompletoExists = true;
                }
            }

            // Mostrar resultados de verificación
            echo '<h2>✅ Resultados de la verificación</h2>';

            echo '<table>';
            echo '<tr><th>Campo</th><th>Estado</th><th>Tipo</th><th>Default</th></tr>';

            // porcentajeDescuento
            echo '<tr class="' . ($porcentajeDescuentoExists ? 'highlight' : '') . '">';
            echo '<td><strong>porcentajeDescuento</strong></td>';
            echo '<td>' . ($porcentajeDescuentoExists ? '<span class="check-icon">✓</span> EXISTE' : '<span class="cross-icon">✗</span> NO EXISTE') . '</td>';
            echo '<td>' . ($porcentajeDescuentoExists ? htmlspecialchars($infoPorcentaje['Type']) : '-') . '</td>';
            echo '<td>' . ($porcentajeDescuentoExists ? htmlspecialchars($infoPorcentaje['Default']) : '-') . '</td>';
            echo '</tr>';

            // montoDescuento
            echo '<tr class="' . ($montoDescuentoExists ? 'highlight' : '') . '">';
            echo '<td><strong>montoDescuento</strong></td>';
            echo '<td>' . ($montoDescuentoExists ? '<span class="check-icon">✓</span> EXISTE' : '<span class="cross-icon">✗</span> NO EXISTE') . '</td>';
            echo '<td>' . ($montoDescuentoExists ? htmlspecialchars($infoMonto['Type']) : '-') . '</td>';
            echo '<td>' . ($montoDescuentoExists ? htmlspecialchars($infoMonto['Default']) : '-') . '</td>';
            echo '</tr>';

            // montoPagado
            echo '<tr>';
            echo '<td><strong>montoPagado</strong></td>';
            echo '<td>' . ($montoPagadoExists ? '<span class="check-icon">✓</span> EXISTE' : '<span class="cross-icon">✗</span> NO EXISTE') . '</td>';
            echo '<td colspan="2">Campo relacionado</td>';
            echo '</tr>';

            // pagoCompleto
            echo '<tr>';
            echo '<td><strong>pagoCompleto</strong></td>';
            echo '<td>' . ($pagoCompletoExists ? '<span class="check-icon">✓</span> EXISTE' : '<span class="cross-icon">✗</span> NO EXISTE') . '</td>';
            echo '<td colspan="2">Campo relacionado</td>';
            echo '</tr>';

            echo '</table>';

            // Mensaje final
            if ($porcentajeDescuentoExists && $montoDescuentoExists) {
                echo '<div class="success">';
                echo '<h2>🎉 ¡Todo está correcto!</h2>';
                echo '<p>Los campos <strong>porcentajeDescuento</strong> y <strong>montoDescuento</strong> ya existen en la base de datos.</p>';
                echo '<p><strong>El sistema está listo para usar.</strong></p>';
                echo '<ul>';
                echo '<li>✓ Ya puedes registrar inscripciones con descuento</li>';
                echo '<li>✓ Los descuentos se guardarán en la base de datos</li>';
                echo '<li>✓ Los descuentos se mostrarán en matriculados</li>';
                echo '<li>✓ Los datos están disponibles para PDFs</li>';
                echo '</ul>';
                echo '</div>';

                // Mostrar estructura completa
                echo '<h2>📋 Estructura completa de la tabla</h2>';
                echo '<table>';
                echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>';
                foreach ($campos as $campo) {
                    $rowClass = '';
                    if ($campo['Field'] == 'porcentajeDescuento' || $campo['Field'] == 'montoDescuento') {
                        $rowClass = ' class="highlight"';
                    }
                    echo '<tr' . $rowClass . '>';
                    echo '<td><strong>' . htmlspecialchars($campo['Field']) . '</strong></td>';
                    echo '<td>' . htmlspecialchars($campo['Type']) . '</td>';
                    echo '<td>' . htmlspecialchars($campo['Null']) . '</td>';
                    echo '<td>' . htmlspecialchars($campo['Default'] ?? 'NULL') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';

            } else {
                echo '<div class="warning">';
                echo '<h2>⚠ Campos faltantes</h2>';
                echo '<p>Algunos campos no existen. Ejecuta el siguiente SQL:</p>';
                echo '<pre>';
                if (!$porcentajeDescuentoExists) {
                    echo "ALTER TABLE estudianteprograma\n";
                    echo "ADD COLUMN porcentajeDescuento DECIMAL(5,2) NOT NULL DEFAULT 0.00\n";
                    echo "COMMENT 'Porcentaje de descuento aplicado (0-100)'\n";
                    echo "AFTER pagoCompleto;\n\n";
                }
                if (!$montoDescuentoExists) {
                    echo "ALTER TABLE estudianteprograma\n";
                    echo "ADD COLUMN montoDescuento DECIMAL(10,2) NOT NULL DEFAULT 0.00\n";
                    echo "COMMENT 'Monto total del descuento en bolivianos'\n";
                    echo "AFTER porcentajeDescuento;\n";
                }
                echo '</pre>';
                echo '</div>';
            }

            // Probar consulta
            if ($porcentajeDescuentoExists && $montoDescuentoExists) {
                echo '<h2>🧪 Prueba de consulta</h2>';

                $stmtTest = $pdo->prepare(
                    "SELECT
                        CONCAT(e.Nombre, ' ', e.Apaterno) as Estudiante,
                        p.NombrePrograma,
                        ep.montoPagado,
                        ep.porcentajeDescuento,
                        ep.montoDescuento,
                        ep.pagoCompleto
                    FROM estudianteprograma ep
                    INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                    INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                    WHERE ep.pagoCompleto = 1
                    ORDER BY ep.FechaInscripcion DESC
                    LIMIT 5"
                );
                $stmtTest->execute();
                $registros = $stmtTest->fetchAll(PDO::FETCH_ASSOC);

                if (count($registros) > 0) {
                    echo '<div class="info">';
                    echo '<p><strong>Últimos 5 pagos completos registrados:</strong></p>';
                    echo '<table>';
                    echo '<tr><th>Estudiante</th><th>Programa</th><th>Monto Pagado</th><th>% Desc</th><th>Monto Desc</th></tr>';
                    foreach ($registros as $reg) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($reg['Estudiante']) . '</td>';
                        echo '<td>' . htmlspecialchars($reg['NombrePrograma']) . '</td>';
                        echo '<td>Bs. ' . number_format($reg['montoPagado'], 2) . '</td>';
                        echo '<td>' . number_format($reg['porcentajeDescuento'], 2) . '%</td>';
                        echo '<td>Bs. ' . number_format($reg['montoDescuento'], 2) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<div class="info">';
                    echo '<p>No hay pagos completos registrados todavía.</p>';
                    echo '</div>';
                }
            }

        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h2>❌ Error</h2>';
            echo '<p><strong>Mensaje:</strong></p>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '</div>';
        }
        ?>

        <hr style="margin: 40px 0;">

        <div style="text-align: center;">
            <a href="inscripcion" class="btn btn-primary">Ir a Inscripción</a>
            <a href="matriculados" class="btn">Ver Matriculados</a>
        </div>

        <p style="text-align: center; color: #999; margin-top: 30px;">
            <strong>Script de verificación</strong><br>
            Fecha: <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>
