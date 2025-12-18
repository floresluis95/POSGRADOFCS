<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>✅ Verificación Orden de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #ddd;
            background: #f9f9f9;
        }
        .check-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .check-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .check-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .details {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }
        .actions {
            margin-top: 20px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .actions a {
            display: inline-block;
            margin: 5px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .actions a:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación del Sistema de Orden de Pago</h1>
        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>

        <?php
        $checks = [];
        $allGood = true;

        // 1. Verificar archivo principal
        $archivoOrdenPago = __DIR__ . '/vistas/componentes/ordenpago.php';
        if (file_exists($archivoOrdenPago)) {
            $checks[] = [
                'status' => 'success',
                'title' => 'Archivo principal ordenpago.php',
                'message' => 'El archivo principal existe correctamente',
                'details' => 'Ruta: ' . $archivoOrdenPago
            ];
        } else {
            $checks[] = [
                'status' => 'error',
                'title' => 'Archivo principal ordenpago.php',
                'message' => 'ERROR: No se encuentra el archivo principal',
                'details' => 'Se esperaba en: ' . $archivoOrdenPago
            ];
            $allGood = false;
        }

        // 2. Verificar archivo AJAX
        $archivoAjax = __DIR__ . '/ajax/estudiantes.ajax.php';
        if (file_exists($archivoAjax)) {
            $checks[] = [
                'status' => 'success',
                'title' => 'Archivo AJAX de estudiantes',
                'message' => 'El endpoint AJAX existe correctamente',
                'details' => 'Ruta: ' . $archivoAjax
            ];
        } else {
            $checks[] = [
                'status' => 'error',
                'title' => 'Archivo AJAX de estudiantes',
                'message' => 'ERROR: No se encuentra el archivo AJAX',
                'details' => 'Se esperaba en: ' . $archivoAjax
            ];
            $allGood = false;
        }

        // 3. Verificar conexión a base de datos
        try {
            require_once __DIR__ . '/modelos/conexion.modelo.php';
            $pdo = Conexion::Conectar();

            $checks[] = [
                'status' => 'success',
                'title' => 'Conexión a Base de Datos',
                'message' => 'Conexión exitosa a la base de datos',
                'details' => 'Driver: ' . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME)
            ];
        } catch (Exception $e) {
            $checks[] = [
                'status' => 'error',
                'title' => 'Conexión a Base de Datos',
                'message' => 'ERROR: No se pudo conectar a la base de datos',
                'details' => $e->getMessage()
            ];
            $allGood = false;
        }

        // 4. Verificar tabla ordenpago
        if (isset($pdo)) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'ordenpago'");
                if ($stmt->rowCount() > 0) {
                    // Verificar estructura de la tabla
                    $stmt = $pdo->query("DESCRIBE ordenpago");
                    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    $expectedColumns = ['IdOrdenPago', 'NumeroOrden', 'EstudianteID', 'ProgramaID', 'MontoTotal', 'MontoFinal'];
                    $missingColumns = array_diff($expectedColumns, $columns);

                    if (empty($missingColumns)) {
                        $checks[] = [
                            'status' => 'success',
                            'title' => 'Tabla ordenpago',
                            'message' => 'La tabla existe con todas las columnas requeridas',
                            'details' => 'Columnas: ' . implode(', ', $columns)
                        ];
                    } else {
                        $checks[] = [
                            'status' => 'warning',
                            'title' => 'Tabla ordenpago',
                            'message' => 'La tabla existe pero le faltan columnas',
                            'details' => 'Columnas faltantes: ' . implode(', ', $missingColumns)
                        ];
                    }
                } else {
                    $checks[] = [
                        'status' => 'error',
                        'title' => 'Tabla ordenpago',
                        'message' => 'ERROR: La tabla ordenpago no existe',
                        'details' => 'Debe crear la tabla ordenpago en la base de datos'
                    ];
                    $allGood = false;
                }
            } catch (Exception $e) {
                $checks[] = [
                    'status' => 'error',
                    'title' => 'Tabla ordenpago',
                    'message' => 'ERROR al verificar la tabla',
                    'details' => $e->getMessage()
                ];
                $allGood = false;
            }
        }

        // 5. Verificar que hay estudiantes
        if (isset($pdo)) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM estudiante WHERE Estado = 1");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalEstudiantes = $result['total'];

                if ($totalEstudiantes > 0) {
                    $checks[] = [
                        'status' => 'success',
                        'title' => 'Estudiantes activos',
                        'message' => "Se encontraron {$totalEstudiantes} estudiantes activos",
                        'details' => 'La tabla estudiante tiene datos para trabajar'
                    ];
                } else {
                    $checks[] = [
                        'status' => 'warning',
                        'title' => 'Estudiantes activos',
                        'message' => 'No hay estudiantes activos en la base de datos',
                        'details' => 'Debe registrar estudiantes antes de generar órdenes de pago'
                    ];
                }
            } catch (Exception $e) {
                $checks[] = [
                    'status' => 'error',
                    'title' => 'Estudiantes activos',
                    'message' => 'ERROR al consultar estudiantes',
                    'details' => $e->getMessage()
                ];
            }
        }

        // 6. Verificar Select2
        $select2 = __DIR__ . '/vistas/recursos/assets/vendors/general/select2/dist/js/select2.full.js';
        if (file_exists($select2)) {
            $checks[] = [
                'status' => 'success',
                'title' => 'Librería Select2',
                'message' => 'Select2 está instalado correctamente',
                'details' => 'Ruta: vistas/recursos/assets/vendors/general/select2/dist/js/select2.full.js'
            ];
        } else {
            $checks[] = [
                'status' => 'warning',
                'title' => 'Librería Select2',
                'message' => 'No se encuentra Select2 en la ruta esperada',
                'details' => 'Puede que esté en otra ubicación o cause problemas de carga'
            ];
        }

        // 7. Verificar SweetAlert
        $sweetalert = __DIR__ . '/vistas/recursos/sweetalert.min.js';
        if (file_exists($sweetalert)) {
            $checks[] = [
                'status' => 'success',
                'title' => 'Librería SweetAlert',
                'message' => 'SweetAlert está instalado correctamente',
                'details' => 'Ruta: vistas/recursos/sweetalert.min.js'
            ];
        } else {
            $checks[] = [
                'status' => 'warning',
                'title' => 'Librería SweetAlert',
                'message' => 'No se encuentra SweetAlert en la ruta esperada',
                'details' => 'Puede que esté en otra ubicación'
            ];
        }

        // Mostrar resultados
        foreach ($checks as $check) {
            $icon = $check['status'] === 'success' ? '✅' : ($check['status'] === 'error' ? '❌' : '⚠️');
            echo "<div class='check-item {$check['status']}'>";
            echo "<div><span class='icon'>{$icon}</span><strong>{$check['title']}</strong></div>";
            echo "<div style='margin-left: 30px;'>{$check['message']}</div>";
            if (!empty($check['details'])) {
                echo "<div class='details'>{$check['details']}</div>";
            }
            echo "</div>";
        }

        // Resumen final
        echo "<div class='actions'>";
        if ($allGood) {
            echo "<h2 style='color: #28a745;'>✅ Sistema Configurado Correctamente</h2>";
            echo "<p>Todos los archivos y configuraciones están en su lugar. El sistema está listo para usarse.</p>";
            echo "<a href='ordenpago'>🚀 Ir a Orden de Pago</a>";
            echo "<a href='test_ajax_estudiante.php?id=1'>🧪 Probar AJAX</a>";
        } else {
            echo "<h2 style='color: #dc3545;'>❌ Se Encontraron Problemas</h2>";
            echo "<p>Revisa los errores marcados arriba y corrígelos antes de usar el sistema.</p>";
        }
        echo "<a href='listar_estudiantes.php'>📋 Ver Estudiantes</a>";
        echo "</div>";
        ?>

        <hr style='margin-top: 30px;'>
        <h3>📚 Archivos de Documentación</h3>
        <ul>
            <li><a href="SOLUCION_AJAX_ESTUDIANTES.md" target="_blank">SOLUCION_AJAX_ESTUDIANTES.md</a> - Solución implementada para AJAX</li>
            <li><a href="NUEVA_ESTRUCTURA_ORDENPAGO.md" target="_blank">NUEVA_ESTRUCTURA_ORDENPAGO.md</a> - Documentación de la nueva estructura</li>
        </ul>

        <hr style='margin-top: 30px;'>
        <p style='text-align: center; color: #666; font-size: 12px;'>
            Desarrollado el 18/12/2025 - Sistema de Orden de Pago POSGRADOFCS
        </p>
    </div>
</body>
</html>
