<?php
/**
 * Script de diagnóstico para verificar el costo de matrícula
 * Este script verifica los datos de programas y su costo de matrícula
 */

require_once "modelos/conexion.modelo.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Costo de Matrícula</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        h2 { color: #764ba2; margin-top: 30px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico: Costo de Matrícula en Programas</h1>

        <?php
        try {
            $pdo = Conexion::conectar();

            // 1. Verificar estructura de la tabla programa
            echo '<h2>📋 1. Estructura de la tabla "programa"</h2>';
            $stmt = $pdo->prepare("DESCRIBE programa");
            $stmt->execute();
            $campos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<table>';
            echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>';

            $costomatriculaExists = false;
            foreach ($campos as $campo) {
                echo '<tr>';
                echo '<td><strong>' . htmlspecialchars($campo['Field']) . '</strong></td>';
                echo '<td>' . htmlspecialchars($campo['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($campo['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($campo['Key']) . '</td>';
                echo '<td>' . htmlspecialchars($campo['Default']) . '</td>';
                echo '</tr>';

                if ($campo['Field'] == 'CostoMatricula') {
                    $costomatriculaExists = true;
                }
            }
            echo '</table>';

            if ($costomatriculaExists) {
                echo '<div class="info-box"><span class="success">✓ El campo CostoMatricula EXISTE</span></div>';
            } else {
                echo '<div class="info-box"><span class="error">✗ El campo CostoMatricula NO EXISTE</span></div>';
            }

            // 2. Listar todos los programas con sus costos
            echo '<h2>📊 2. Programas registrados y sus costos</h2>';
            $stmt = $pdo->prepare("SELECT ProgramaID, NombrePrograma, GradoAcademico, Costo, CostoMatricula, Estado FROM programa ORDER BY ProgramaID");
            $stmt->execute();
            $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($programas)) {
                echo '<div class="info-box"><span class="warning">⚠ No hay programas registrados</span></div>';
            } else {
                echo '<table>';
                echo '<tr><th>ID</th><th>Nombre</th><th>Grado</th><th>Costo Total</th><th>Costo Matrícula</th><th>Estado</th><th>Observación</th></tr>';

                foreach ($programas as $prog) {
                    $costoMatricula = $prog['CostoMatricula'];
                    $observacion = '';

                    if ($costoMatricula === null) {
                        $observacion = '<span class="error">NULL</span>';
                        $costoMatriculaDisplay = '<span class="error">NULL</span>';
                    } elseif ($costoMatricula == 0) {
                        $observacion = '<span class="warning">CERO</span>';
                        $costoMatriculaDisplay = '<span class="warning">Bs. 0.00</span>';
                    } else {
                        $observacion = '<span class="success">OK</span>';
                        $costoMatriculaDisplay = 'Bs. ' . number_format($costoMatricula, 2);
                    }

                    echo '<tr>';
                    echo '<td>' . $prog['ProgramaID'] . '</td>';
                    echo '<td>' . htmlspecialchars($prog['NombrePrograma']) . '</td>';
                    echo '<td>' . htmlspecialchars($prog['GradoAcademico']) . '</td>';
                    echo '<td>Bs. ' . number_format($prog['Costo'], 2) . '</td>';
                    echo '<td>' . $costoMatriculaDisplay . '</td>';
                    echo '<td>' . ($prog['Estado'] == 1 ? '<span class="success">ACTIVO</span>' : '<span class="error">INACTIVO</span>') . '</td>';
                    echo '<td>' . $observacion . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }

            // 3. Verificar campos hidden en el formulario
            echo '<h2>🔧 3. Instrucciones de prueba</h2>';
            echo '<div class="info-box">';
            echo '<p><strong>Para probar el modal de descuento:</strong></p>';
            echo '<ol>';
            echo '<li>Ir a: <code>http://localhost/POSGRADOFCS/inscripcion</code></li>';
            echo '<li>Abrir la consola del navegador (F12)</li>';
            echo '<li>Seleccionar un estudiante</li>';
            echo '<li>Seleccionar un grado académico</li>';
            echo '<li>Seleccionar un programa</li>';
            echo '<li>Observar en la consola los logs: <code>=== PROGRAMA SELECCIONADO ===</code></li>';
            echo '<li>Marcar el checkbox "PAGO COMPLETO DEL PROGRAMA"</li>';
            echo '<li>Observar en la consola los logs: <code>=== MODAL DE DESCUENTO ===</code></li>';
            echo '<li>Verificar que aparezcan los valores de Costo Programa y Costo Matrícula</li>';
            echo '</ol>';
            echo '</div>';

            // 4. Solución si hay problemas
            echo '<h2>💡 4. Soluciones posibles</h2>';
            echo '<div class="info-box">';

            $programasConProblema = 0;
            foreach ($programas as $prog) {
                if ($prog['CostoMatricula'] === null || $prog['CostoMatricula'] == 0) {
                    $programasConProblema++;
                }
            }

            if ($programasConProblema > 0) {
                echo '<p class="warning"><strong>⚠ Se encontraron ' . $programasConProblema . ' programa(s) sin costo de matrícula definido</strong></p>';
                echo '<p>Para actualizar los programas con costo de matrícula:</p>';
                echo '<ol>';
                echo '<li>Ir al módulo de programas en el sistema</li>';
                echo '<li>Editar cada programa</li>';
                echo '<li>Ingresar un valor en el campo "Costo de Matrícula"</li>';
                echo '<li>Guardar los cambios</li>';
                echo '</ol>';

                echo '<p><strong>O ejecutar este SQL para poner un valor por defecto:</strong></p>';
                echo '<div class="code">';
                echo 'UPDATE programa SET CostoMatricula = 500.00 WHERE CostoMatricula IS NULL OR CostoMatricula = 0;';
                echo '</div>';
            } else {
                echo '<p class="success"><strong>✓ Todos los programas tienen costo de matrícula definido</strong></p>';
            }

            echo '</div>';

        } catch (Exception $e) {
            echo '<div class="info-box"><span class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</span></div>';
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
