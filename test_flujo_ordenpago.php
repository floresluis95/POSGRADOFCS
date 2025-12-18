<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🧪 Test Flujo Orden de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
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
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fafafa;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #667eea;
            color: white;
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
        <h1>🧪 Test del Flujo Completo de Orden de Pago</h1>
        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>

        <?php
        require_once __DIR__ . '/modelos/conexion.modelo.php';

        // ====================================
        // TEST 1: Verificar Estudiantes
        // ====================================
        echo '<div class="test-section">';
        echo '<h2>📋 Test 1: Estudiantes Disponibles</h2>';

        try {
            $pdo = Conexion::Conectar();
            $stmt = $pdo->query("
                SELECT EstudianteID, Ci, Complemento, Exp, Nombre, Apaterno, Amaterno
                FROM estudiante
                WHERE Estado = 1
                LIMIT 5
            ");
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($estudiantes) > 0) {
                echo '<p class="success">✅ Se encontraron ' . count($estudiantes) . ' estudiantes activos</p>';
                echo '<table>';
                echo '<tr><th>ID</th><th>CI</th><th>Nombre Completo</th></tr>';
                foreach ($estudiantes as $est) {
                    $ci = $est['Ci'] . ($est['Complemento'] ? '-' . $est['Complemento'] : '') . ' ' . $est['Exp'];
                    $nombre = $est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre'];
                    echo '<tr>';
                    echo '<td>' . $est['EstudianteID'] . '</td>';
                    echo '<td>' . $ci . '</td>';
                    echo '<td>' . $nombre . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="error">❌ No hay estudiantes activos</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
        }

        echo '</div>';

        // ====================================
        // TEST 2: Verificar Programas
        // ====================================
        echo '<div class="test-section">';
        echo '<h2>📚 Test 2: Programas Disponibles</h2>';

        try {
            $stmt = $pdo->query("
                SELECT ProgramaID, Codigo, NombrePrograma, GradoAcademico, CostoMatricula, Costo
                FROM programa
                WHERE Estado = 1
                ORDER BY GradoAcademico, NombrePrograma
                LIMIT 10
            ");
            $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($programas) > 0) {
                echo '<p class="success">✅ Se encontraron ' . count($programas) . ' programas activos</p>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Código</th><th>Programa</th><th>Grado</th><th>Matrícula</th><th>Programa</th></tr>';
                foreach ($programas as $prog) {
                    echo '<tr>';
                    echo '<td>' . $prog['ProgramaID'] . '</td>';
                    echo '<td>' . $prog['Codigo'] . '</td>';
                    echo '<td>' . $prog['NombrePrograma'] . '</td>';
                    echo '<td>' . $prog['GradoAcademico'] . '</td>';
                    echo '<td>Bs. ' . number_format($prog['CostoMatricula'], 2) . '</td>';
                    echo '<td>Bs. ' . number_format($prog['Costo'], 2) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="error">❌ No hay programas activos</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
        }

        echo '</div>';

        // ====================================
        // TEST 3: Test AJAX Estudiantes
        // ====================================
        echo '<div class="test-section">';
        echo '<h2>🔌 Test 3: AJAX de Estudiantes</h2>';

        if (count($estudiantes) > 0) {
            $testEstudianteID = $estudiantes[0]['EstudianteID'];
            $_POST['idestudiante'] = $testEstudianteID;

            ob_start();
            include 'ajax/estudiantes.ajax.php';
            $ajaxResponse = ob_get_clean();

            $jsonData = json_decode($ajaxResponse, true);

            if ($jsonData && !isset($jsonData['error'])) {
                echo '<p class="success">✅ AJAX de estudiantes funciona correctamente</p>';
                echo '<p><strong>Estudiante ID:</strong> ' . $testEstudianteID . '</p>';
                echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto;">';
                echo json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo '</pre>';
            } else {
                echo '<p class="error">❌ AJAX de estudiantes retornó error: ' . ($jsonData['error'] ?? 'Respuesta no es JSON válido') . '</p>';
            }
        } else {
            echo '<p class="warning">⚠️ No se puede probar AJAX sin estudiantes</p>';
        }

        echo '</div>';

        // ====================================
        // TEST 4: Test AJAX Programas (Filtro por Grado)
        // ====================================
        echo '<div class="test-section">';
        echo '<h2>🔌 Test 4: AJAX de Programas (Filtro por Grado)</h2>';

        $_POST = []; // Limpiar POST
        $_POST['grado'] = 'MAESTRIA';

        ob_start();
        include 'ajax/programa.ajax.php';
        $ajaxResponseProg = ob_get_clean();

        $jsonProg = json_decode($ajaxResponseProg, true);

        if ($jsonProg && !isset($jsonProg['error'])) {
            echo '<p class="success">✅ AJAX de programas (filtro) funciona correctamente</p>';
            echo '<p><strong>Grado:</strong> MAESTRIA</p>';
            echo '<p><strong>Programas encontrados:</strong> ' . count($jsonProg) . '</p>';
            echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto; max-height: 300px;">';
            echo json_encode($jsonProg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo '</pre>';
        } else {
            echo '<p class="error">❌ AJAX de programas retornó error</p>';
        }

        echo '</div>';

        // ====================================
        // TEST 5: Test AJAX Programas (Detalle)
        // ====================================
        echo '<div class="test-section">';
        echo '<h2>🔌 Test 5: AJAX de Programas (Detalle)</h2>';

        if (count($programas) > 0) {
            $_POST = []; // Limpiar POST
            $testProgramaID = $programas[0]['ProgramaID'];
            $_POST['idprograma'] = $testProgramaID;

            ob_start();
            include 'ajax/programa.ajax.php';
            $ajaxResponseDetail = ob_get_clean();

            $jsonDetail = json_decode($ajaxResponseDetail, true);

            if ($jsonDetail && !isset($jsonDetail['error'])) {
                echo '<p class="success">✅ AJAX de detalle de programa funciona correctamente</p>';
                echo '<p><strong>Programa ID:</strong> ' . $testProgramaID . '</p>';
                echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 5px; overflow-x: auto;">';
                echo json_encode($jsonDetail, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                echo '</pre>';
            } else {
                echo '<p class="error">❌ AJAX de detalle retornó error</p>';
            }
        } else {
            echo '<p class="warning">⚠️ No se puede probar AJAX sin programas</p>';
        }

        echo '</div>';

        // ====================================
        // TEST 6: Verificar archivos necesarios
        // ====================================
        echo '<div class="test-section">';
        echo '<h2>📁 Test 6: Archivos Necesarios</h2>';

        $archivos = [
            'vistas/componentes/ordenpago.php' => 'Página principal',
            'ajax/estudiantes.ajax.php' => 'AJAX de estudiantes',
            'ajax/programa.ajax.php' => 'AJAX de programas',
            'controladores/ordenpago.controlador.php' => 'Controlador de orden de pago',
            'modelos/ordenpago.modelo.php' => 'Modelo de orden de pago',
            'vistas/recursos/assets/vendors/general/select2/dist/js/select2.full.js' => 'Librería Select2',
            'vistas/recursos/sweetalert.min.js' => 'Librería SweetAlert'
        ];

        $todosExisten = true;
        echo '<table>';
        echo '<tr><th>Archivo</th><th>Descripción</th><th>Estado</th></tr>';

        foreach ($archivos as $ruta => $descripcion) {
            $existe = file_exists(__DIR__ . '/' . $ruta);
            if (!$existe) $todosExisten = false;

            echo '<tr>';
            echo '<td>' . $ruta . '</td>';
            echo '<td>' . $descripcion . '</td>';
            echo '<td>' . ($existe ? '<span class="success">✅ Existe</span>' : '<span class="error">❌ No encontrado</span>') . '</td>';
            echo '</tr>';
        }

        echo '</table>';

        if ($todosExisten) {
            echo '<p class="success">✅ Todos los archivos necesarios existen</p>';
        } else {
            echo '<p class="error">❌ Faltan algunos archivos</p>';
        }

        echo '</div>';

        // ====================================
        // RESUMEN FINAL
        // ====================================
        echo '<div class="actions">';
        echo '<h2>🎯 Resumen y Acciones</h2>';

        if ($todosExisten && count($estudiantes) > 0 && count($programas) > 0) {
            echo '<p class="success" style="font-size: 18px;">✅ ¡TODO ESTÁ LISTO PARA USAR!</p>';
            echo '<p>El sistema de Orden de Pago está completamente funcional.</p>';
            echo '<a href="ordenpago">🚀 Ir a Orden de Pago</a>';
        } else {
            echo '<p class="error" style="font-size: 18px;">❌ Hay problemas que resolver</p>';
            echo '<p>Revisa los tests anteriores para identificar los problemas.</p>';
        }

        echo '<a href="verificar_ordenpago.php">🔍 Verificación del Sistema</a>';
        echo '<a href="test_ajax_estudiante.php?id=' . ($estudiantes[0]['EstudianteID'] ?? 1) . '">🧪 Test AJAX Estudiante</a>';
        echo '<a href="listar_estudiantes.php">📋 Listar Estudiantes</a>';

        echo '</div>';
        ?>

        <hr style="margin-top: 30px;">
        <p style="text-align: center; color: #666; font-size: 12px;">
            Test del Flujo de Orden de Pago - <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>
</body>
</html>
