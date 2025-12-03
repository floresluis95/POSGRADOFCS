<?php
/**
 * Script de diagnóstico de sesión
 * Muestra toda la información de la sesión activa
 */

session_start();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 15px 0;
        }
        .success-box {
            background: #d4edda;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 15px 0;
        }
        .warning-box {
            background: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
        .error-box {
            background: #f8d7da;
            padding: 15px;
            border-left: 4px solid #dc3545;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #667eea;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
            font-family: monospace;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background-color: #5568d3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Diagnóstico de Sesión del Sistema</h2>

        <?php if (isset($_SESSION['Validar']) && $_SESSION['Validar'] === true): ?>
            <div class="success-box">
                <h3>✅ Sesión Activa</h3>
                <p>Hay una sesión iniciada en el sistema.</p>
            </div>

            <h3>📊 Información de la Sesión</h3>
            <table>
                <tr>
                    <th>Variable de Sesión</th>
                    <th>Valor</th>
                </tr>
                <?php foreach ($_SESSION as $key => $value): ?>
                    <tr>
                        <td class="label"><?php echo htmlspecialchars($key); ?></td>
                        <td class="value">
                            <?php
                            if (is_array($value)) {
                                echo "<pre>" . print_r($value, true) . "</pre>";
                            } else {
                                echo htmlspecialchars($value);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h3>🎯 Verificación del Tipo de Usuario</h3>
            <?php if (isset($_SESSION['Tipo'])): ?>
                <?php
                $tipo = $_SESSION['Tipo'];
                $descripcion = '';
                $clase = 'info-box';

                switch($tipo) {
                    case 'EST':
                        $descripcion = 'Estudiante - Debe ver sidebar con "Historial de Notas"';
                        $clase = 'success-box';
                        break;
                    case 'DOC':
                        $descripcion = 'Docente - Debe ver sidebar con opciones de docente';
                        $clase = 'info-box';
                        break;
                    case 'ADM':
                        $descripcion = 'Administrador - Debe ver sidebar completo';
                        $clase = 'info-box';
                        break;
                    case 'SEC':
                        $descripcion = 'Secretaria - Debe ver sidebar de secretaria';
                        $clase = 'info-box';
                        break;
                    default:
                        $descripcion = 'Tipo desconocido - ⚠️ ESTO PUEDE CAUSAR PROBLEMAS';
                        $clase = 'error-box';
                }
                ?>
                <div class="<?php echo $clase; ?>">
                    <p><strong>Tipo detectado:</strong> <span style="font-size: 1.5em; font-weight: bold;"><?php echo $tipo; ?></span></p>
                    <p><?php echo $descripcion; ?></p>
                </div>
            <?php else: ?>
                <div class="error-box">
                    <p>❌ <strong>ERROR:</strong> La variable de sesión 'Tipo' NO está definida.</p>
                    <p>Esto causará que el sidebar no se muestre correctamente.</p>
                </div>
            <?php endif; ?>

            <?php if ($tipo === 'EST'): ?>
                <h3>👨‍🎓 Información del Estudiante</h3>
                <div class="info-box">
                    <p><strong>Estudiante ID:</strong> <?php echo isset($_SESSION['EstudianteID']) ? $_SESSION['EstudianteID'] : 'NO DEFINIDO ❌'; ?></p>
                    <p><strong>Nombre:</strong> <?php echo isset($_SESSION['Nombres']) ? $_SESSION['Nombres'] : ''; ?>
                       <?php echo isset($_SESSION['ApellidoPaterno']) ? $_SESSION['ApellidoPaterno'] : ''; ?>
                       <?php echo isset($_SESSION['ApellidoMaterno']) ? $_SESSION['ApellidoMaterno'] : ''; ?>
                    </p>
                    <p><strong>CI:</strong> <?php echo isset($_SESSION['CedulaIdentidad']) ? $_SESSION['CedulaIdentidad'] : 'NO DEFINIDO'; ?></p>
                </div>

                <h3>🔍 Verificación del Sidebar</h3>
                <div class="info-box">
                    <p>Para que el sidebar se muestre correctamente, la condición en <code>sidebar.php</code> es:</p>
                    <code style="display: block; background: #f8f9fa; padding: 10px; margin: 10px 0;">
                        if ($_SESSION["Tipo"] == "EST")
                    </code>
                    <p><strong>Evaluación actual:</strong>
                        <?php if ($_SESSION['Tipo'] == 'EST'): ?>
                            <span style="color: green; font-weight: bold;">✅ TRUE - El sidebar DEBE mostrarse</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">❌ FALSE - El sidebar NO se mostrará</span>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px;">
                <a href="panel" class="btn">← Ir al Panel</a>
                <a href="historialnotaestudiante" class="btn">Ver Historial de Notas</a>
                <a href="salir" class="btn btn-danger">Cerrar Sesión</a>
            </div>

        <?php else: ?>
            <div class="warning-box">
                <h3>⚠️ No hay sesión activa</h3>
                <p>Por favor, inicie sesión primero.</p>
            </div>
            <div style="margin-top: 20px;">
                <a href="ingreso" class="btn">Ir a Inicio de Sesión</a>
            </div>
        <?php endif; ?>

        <hr style="margin: 30px 0;">
        <h3>📝 Todas las Variables de Sesión (RAW)</h3>
        <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;">
<?php print_r($_SESSION); ?>
        </pre>
    </div>
</body>
</html>
