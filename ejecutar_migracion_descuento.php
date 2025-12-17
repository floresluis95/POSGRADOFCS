<?php
/**
 * Script para ejecutar migración de campos de descuento
 * Agrega campos: porcentajeDescuento y montoDescuento
 */

require_once "modelos/conexion.modelo.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración: Campos de Descuento</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        h1 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 15px; margin-bottom: 30px; }
        h2 { color: #764ba2; margin-top: 30px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 15px 0; border-radius: 5px; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; border-radius: 5px; color: #721c24; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; border-radius: 5px; color: #856404; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 15px 0; border-radius: 5px; color: #0c5460; }
        .step { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 8px; border: 2px solid #e9ecef; }
        .step-number { display: inline-block; width: 35px; height: 35px; background: #667eea; color: white; border-radius: 50%; text-align: center; line-height: 35px; font-weight: bold; margin-right: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f8f9fa; }
        .highlight { background: #fff3cd; font-weight: bold; }
        .btn { display: inline-block; padding: 12px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; }
        .btn:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Migración: Campos de Descuento</h1>

        <?php
        try {
            $pdo = Conexion::conectar();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo '<div class="info">';
            echo '<strong>📋 Proceso de migración iniciado...</strong><br>';
            echo 'Se agregarán los campos para registrar descuentos en pagos completos.';
            echo '</div>';

            // PASO 1: Verificar si los campos ya existen
            echo '<div class="step">';
            echo '<h2><span class="step-number">1</span>Verificando campos existentes</h2>';

            $stmt = $pdo->prepare("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'postgradofcs'
                AND TABLE_NAME = 'estudianteprograma'
                AND COLUMN_NAME IN ('porcentajeDescuento', 'montoDescuento')
            ");
            $stmt->execute();
            $camposExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $porcentajeExists = in_array('porcentajeDescuento', $camposExistentes);
            $montoExists = in_array('montoDescuento', $camposExistentes);

            if ($porcentajeExists && $montoExists) {
                echo '<div class="warning">⚠ Los campos <strong>porcentajeDescuento</strong> y <strong>montoDescuento</strong> ya existen. No es necesario ejecutar la migración.</div>';
            } else {
                echo '<div class="info">✓ Campos a agregar:<br>';
                if (!$porcentajeExists) echo '- <strong>porcentajeDescuento</strong> (DECIMAL 5,2)<br>';
                if (!$montoExists) echo '- <strong>montoDescuento</strong> (DECIMAL 10,2)<br>';
                echo '</div>';
            }
            echo '</div>';

            // PASO 2: Agregar campo porcentajeDescuento
            if (!$porcentajeExists) {
                echo '<div class="step">';
                echo '<h2><span class="step-number">2</span>Agregando campo "porcentajeDescuento"</h2>';

                $sql = "ALTER TABLE `estudianteprograma`
                        ADD COLUMN `porcentajeDescuento` DECIMAL(5,2) NOT NULL DEFAULT 0.00
                        COMMENT 'Porcentaje de descuento aplicado (0-100)'
                        AFTER `pagoCompleto`";

                $pdo->exec($sql);
                echo '<div class="success">✓ Campo <strong>porcentajeDescuento</strong> agregado exitosamente</div>';
                echo '</div>';
            }

            // PASO 3: Agregar campo montoDescuento
            if (!$montoExists) {
                echo '<div class="step">';
                echo '<h2><span class="step-number">3</span>Agregando campo "montoDescuento"</h2>';

                $sql = "ALTER TABLE `estudianteprograma`
                        ADD COLUMN `montoDescuento` DECIMAL(10,2) NOT NULL DEFAULT 0.00
                        COMMENT 'Monto total del descuento en bolivianos'
                        AFTER `porcentajeDescuento`";

                $pdo->exec($sql);
                echo '<div class="success">✓ Campo <strong>montoDescuento</strong> agregado exitosamente</div>';
                echo '</div>';
            }

            // PASO 4: Mostrar estructura final
            echo '<div class="step">';
            echo '<h2><span class="step-number">4</span>Estructura final de la tabla</h2>';

            $stmt = $pdo->prepare("DESCRIBE estudianteprograma");
            $stmt->execute();
            $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<table>';
            echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th><th>Extra</th></tr>';

            foreach ($campos as $campo) {
                $rowClass = '';
                if ($campo['Field'] == 'porcentajeDescuento' || $campo['Field'] == 'montoDescuento') {
                    $rowClass = ' class="highlight"';
                }

                echo '<tr' . $rowClass . '>';
                echo '<td><strong>' . htmlspecialchars($campo['Field']) . '</strong></td>';
                echo '<td>' . htmlspecialchars($campo['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($campo['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($campo['Default']) . '</td>';
                echo '<td>' . htmlspecialchars($campo['Extra']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';

            // Resumen final
            echo '<div class="success">';
            echo '<h2>✅ Migración completada exitosamente</h2>';
            echo '<p><strong>Campos agregados:</strong></p>';
            echo '<ul>';
            echo '<li><strong>porcentajeDescuento:</strong> DECIMAL(5,2) - Guarda el porcentaje aplicado (ej: 10.50%)</li>';
            echo '<li><strong>montoDescuento:</strong> DECIMAL(10,2) - Guarda el monto del descuento en Bs.</li>';
            echo '</ul>';
            echo '<p><strong>Próximos pasos:</strong></p>';
            echo '<ol>';
            echo '<li>El sistema ya guardará automáticamente los descuentos aplicados</li>';
            echo '<li>Los descuentos se mostrarán en la lista de matriculados</li>';
            echo '<li>Los descuentos aparecerán en los PDFs generados</li>';
            echo '</ol>';
            echo '</div>';

            echo '<div style="text-align: center; margin-top: 30px;">';
            echo '<a href="inscripcion" class="btn">Ir a Inscripción</a>';
            echo '<a href="matriculados" class="btn" style="background: #667eea; margin-left: 10px;">Ver Matriculados</a>';
            echo '</div>';

        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h2>❌ Error en la migración</h2>';
            echo '<p><strong>Mensaje de error:</strong></p>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '</div>';
        }
        ?>

        <hr style="margin: 40px 0;">
        <p style="text-align: center; color: #999;">
            <strong>Script de migración</strong><br>
            Fecha: <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>
