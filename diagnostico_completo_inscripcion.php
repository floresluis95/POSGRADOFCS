<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Diagnóstico Completo - Inscripción</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #1976D2; border-bottom: 3px solid #1976D2; padding-bottom: 10px; }
        h2 { color: #388E3C; margin-top: 0; }
        .success { background: #e8f5e9; border-left: 5px solid #4CAF50; padding: 15px; margin: 10px 0; }
        .error { background: #ffebee; border-left: 5px solid #f44336; padding: 15px; margin: 10px 0; }
        .warning { background: #fff3e0; border-left: 5px solid #FF9800; padding: 15px; margin: 10px 0; }
        .info { background: #e3f2fd; border-left: 5px solid #2196F3; padding: 15px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .btn { padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
        .btn:hover { background: #1976D2; }
    </style>
</head>
<body>";

echo "<h1>🔍 Diagnóstico Completo del Sistema de Inscripción</h1>";

// ==========================================
// 1. VERIFICAR CONEXIÓN A BASE DE DATOS
// ==========================================
echo "<div class='section'>";
echo "<h2>1. Conexión a Base de Datos</h2>";

try {
    require_once 'modelos/conexion.modelo.php';
    $pdo = Conexion::Conectar();
    echo "<div class='success'>✓ Conexión exitosa a la base de datos</div>";

    // Verificar tabla inscripcion
    $stmt = $pdo->query("SHOW TABLES LIKE 'inscripcion'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✓ Tabla 'inscripcion' existe</div>";

        // Mostrar estructura
        $stmt = $pdo->query("DESCRIBE inscripcion");
        echo "<h3>Estructura de la tabla:</h3>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td><strong>" . $row['Field'] . "</strong></td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>✗ Tabla 'inscripcion' NO existe</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error de conexión: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ==========================================
// 2. VERIFICAR ARCHIVOS PHP
// ==========================================
echo "<div class='section'>";
echo "<h2>2. Verificación de Archivos PHP</h2>";

$archivos = [
    'modelos/matricula.modelo.php' => 'Modelo de Matrícula',
    'controladores/matricula.controlador.php' => 'Controlador de Matrícula',
    'vistas/componentes/inscripcion.php' => 'Vista de Inscripción'
];

foreach ($archivos as $ruta => $descripcion) {
    if (file_exists($ruta)) {
        echo "<div class='success'>✓ <strong>$descripcion:</strong> <code>$ruta</code> existe</div>";
    } else {
        echo "<div class='error'>✗ <strong>$descripcion:</strong> <code>$ruta</code> NO encontrado</div>";
    }
}

// Verificar que las clases estén cargadas
echo "<h3>Clases PHP Disponibles:</h3>";
try {
    require_once 'modelos/matricula.modelo.php';
    require_once 'controladores/matricula.controlador.php';

    if (class_exists('MatriculaModelos')) {
        echo "<div class='success'>✓ Clase <code>MatriculaModelos</code> disponible</div>";

        $methods = get_class_methods('MatriculaModelos');
        echo "<div class='info'><strong>Métodos disponibles:</strong><br>";
        foreach ($methods as $method) {
            echo "• " . $method . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>✗ Clase <code>MatriculaModelos</code> NO disponible</div>";
    }

    if (class_exists('MatriculaControladores')) {
        echo "<div class='success'>✓ Clase <code>MatriculaControladores</code> disponible</div>";

        $methods = get_class_methods('MatriculaControladores');
        echo "<div class='info'><strong>Métodos disponibles:</strong><br>";
        foreach ($methods as $method) {
            echo "• " . $method . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='error'>✗ Clase <code>MatriculaControladores</code> NO disponible</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error al cargar clases: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ==========================================
// 3. VERIFICAR DATOS DE PRUEBA
// ==========================================
echo "<div class='section'>";
echo "<h2>3. Datos de Prueba Disponibles</h2>";

try {
    // Estudiantes activos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM estudiante WHERE Estado = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='info'>📊 Estudiantes activos: <strong>" . $row['total'] . "</strong></div>";

    if ($row['total'] > 0) {
        $stmt = $pdo->query("SELECT EstudianteID, Nombre, Apaterno, Ci FROM estudiante WHERE Estado = 1 LIMIT 3");
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>CI</th></tr>";
        while ($est = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>" . $est['EstudianteID'] . "</td><td>" . $est['Nombre'] . " " . $est['Apaterno'] . "</td><td>" . $est['Ci'] . "</td></tr>";
        }
        echo "</table>";
    }

    // Programas activos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM programa WHERE Estado = 'ACTIVO' OR Estado = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='info'>📊 Programas activos: <strong>" . $row['total'] . "</strong></div>";

    if ($row['total'] > 0) {
        $stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, GradoAcademico FROM programa WHERE Estado = 'ACTIVO' OR Estado = 1 LIMIT 3");
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Grado</th></tr>";
        while ($prog = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>" . $prog['ProgramaID'] . "</td><td>" . $prog['NombrePrograma'] . "</td><td>" . $prog['GradoAcademico'] . "</td></tr>";
        }
        echo "</table>";
    }

    // Inscripciones existentes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inscripcion");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='info'>📊 Inscripciones registradas: <strong>" . $row['total'] . "</strong></div>";

    if ($row['total'] > 0) {
        $stmt = $pdo->query("SELECT i.idInscripcion, i.FechaInscripcion, e.Nombre, e.Apaterno, p.NombrePrograma
                             FROM inscripcion i
                             INNER JOIN estudiante e ON i.EstudianteID = e.EstudianteID
                             INNER JOIN programa p ON i.ProgramaID = p.ProgramaID
                             ORDER BY i.idInscripcion DESC LIMIT 5");
        echo "<h3>Últimas inscripciones:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Fecha</th><th>Estudiante</th><th>Programa</th></tr>";
        while ($insc = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>" . $insc['idInscripcion'] . "</td><td>" . $insc['FechaInscripcion'] . "</td><td>" . $insc['Nombre'] . " " . $insc['Apaterno'] . "</td><td>" . $insc['NombrePrograma'] . "</td></tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error al consultar datos: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ==========================================
// 4. TEST DE INSERCIÓN DIRECTA
// ==========================================
echo "<div class='section'>";
echo "<h2>4. Test de Inserción Directa</h2>";

try {
    // Obtener un estudiante y programa de prueba
    $stmt = $pdo->query("SELECT EstudianteID FROM estudiante WHERE Estado = 1 LIMIT 1");
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT ProgramaID FROM programa WHERE Estado = 'ACTIVO' OR Estado = 1 LIMIT 1");
    $programa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($estudiante && $programa) {
        echo "<div class='info'>Estudiante ID: " . $estudiante['EstudianteID'] . " | Programa ID: " . $programa['ProgramaID'] . "</div>";

        // Crear imagen de prueba (pixel transparente PNG)
        $imagenPrueba = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        $datosTest = array(
            "EstudianteID" => $estudiante['EstudianteID'],
            "ProgramaID" => $programa['ProgramaID'],
            "costomatricula" => 1500,
            "nvauchermatricula" => rand(10000, 99999),
            "FechaInscripcion" => date('Y-m-d'),
            "foto" => $imagenPrueba
        );

        echo "<div class='warning'>⚠️ <strong>Nota:</strong> Esto insertará un registro de prueba en la base de datos</div>";
        echo "<div class='info'><strong>Datos del test:</strong><br>";
        echo "• Estudiante ID: " . $datosTest['EstudianteID'] . "<br>";
        echo "• Programa ID: " . $datosTest['ProgramaID'] . "<br>";
        echo "• Costo: " . $datosTest['costomatricula'] . "<br>";
        echo "• Vaucher: " . $datosTest['nvauchermatricula'] . "<br>";
        echo "• Fecha: " . $datosTest['FechaInscripcion'] . "<br>";
        echo "• Imagen: " . strlen($imagenPrueba) . " bytes<br>";
        echo "</div>";

        if (isset($_GET['ejecutar_test'])) {
            $resultado = MatriculaModelos::RegistrarMatriculaModelo($datosTest);

            if ($resultado == 'exitoso') {
                echo "<div class='success'><strong>✓ TEST EXITOSO!</strong><br>";
                echo "El registro se insertó correctamente en la base de datos.</div>";

                // Mostrar el último registro
                $stmt = $pdo->query("SELECT * FROM inscripcion ORDER BY idInscripcion DESC LIMIT 1");
                $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<div class='info'><h3>Último registro insertado:</h3>";
                echo "<table>";
                foreach ($ultimo as $key => $value) {
                    if ($key == 'foto') {
                        echo "<tr><td><strong>$key</strong></td><td>[BLOB - " . strlen($value) . " bytes]</td></tr>";
                    } else {
                        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
                    }
                }
                echo "</table></div>";
            } elseif ($resultado == 'duplicado') {
                echo "<div class='warning'>⚠️ El estudiante ya está inscrito en este programa</div>";
            } else {
                echo "<div class='error'>✗ Error al insertar. Revisa el archivo error.log</div>";
            }
        } else {
            echo "<a href='?ejecutar_test=1' class='btn' onclick='return confirm(\"¿Ejecutar test de inserción?\")'>▶️ Ejecutar Test de Inserción</a>";
        }
    } else {
        echo "<div class='error'>✗ No hay estudiantes o programas disponibles para el test</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error en test: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ==========================================
// 5. VERIFICAR LOGS
// ==========================================
echo "<div class='section'>";
echo "<h2>5. Logs del Sistema</h2>";

$logFile = 'C:/xampp/apache/logs/error.log';
if (file_exists($logFile)) {
    echo "<div class='success'>✓ Archivo de log encontrado: <code>$logFile</code></div>";
    echo "<div class='info'><strong>Últimas líneas del log (filtradas por 'RegistrarMatricula'):</strong><br>";
    echo "<pre style='background: #2d2d2d; color: #00ff00; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto;'>";

    $lines = file($logFile);
    $filtered = array_filter($lines, function($line) {
        return stripos($line, 'RegistrarMatricula') !== false ||
               stripos($line, 'matricula') !== false ||
               stripos($line, 'inscripcion') !== false;
    });

    if (empty($filtered)) {
        echo "No se encontraron logs relacionados con matrícula/inscripción.\n";
        echo "Esto significa que el controlador probablemente NO se está ejecutando.";
    } else {
        echo implode('', array_slice($filtered, -20)); // Últimas 20 líneas
    }

    echo "</pre></div>";
} else {
    echo "<div class='warning'>⚠️ Archivo de log no encontrado en la ubicación esperada</div>";
}

echo "</div>";

// ==========================================
// 6. CONCLUSIONES Y RECOMENDACIONES
// ==========================================
echo "<div class='section'>";
echo "<h2>6. Análisis y Recomendaciones</h2>";

$problemas = [];
$recomendaciones = [];

// Verificar tabla
$stmt = $pdo->query("SHOW TABLES LIKE 'inscripcion'");
if ($stmt->rowCount() == 0) {
    $problemas[] = "La tabla 'inscripcion' no existe en la base de datos";
    $recomendaciones[] = "Crear la tabla 'inscripcion' con la estructura correcta";
}

// Verificar datos
$stmt = $pdo->query("SELECT COUNT(*) as total FROM estudiante WHERE Estado = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row['total'] == 0) {
    $problemas[] = "No hay estudiantes activos";
    $recomendaciones[] = "Agregar al menos un estudiante activo en el sistema";
}

$stmt = $pdo->query("SELECT COUNT(*) as total FROM programa WHERE Estado = 'ACTIVO' OR Estado = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row['total'] == 0) {
    $problemas[] = "No hay programas activos";
    $recomendaciones[] = "Agregar al menos un programa activo en el sistema";
}

if (empty($problemas)) {
    echo "<div class='success'><h3>✓ Sistema Listo</h3>";
    echo "<p>No se detectaron problemas estructurales. El sistema está configurado correctamente.</p>";
    echo "<p><strong>Siguiente paso:</strong> Usar el formulario de prueba para verificar el registro.</p>";
    echo "</div>";
} else {
    echo "<div class='error'><h3>✗ Problemas Detectados:</h3><ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul></div>";

    echo "<div class='warning'><h3>📋 Recomendaciones:</h3><ol>";
    foreach ($recomendaciones as $rec) {
        echo "<li>$rec</li>";
    }
    echo "</ol></div>";
}

echo "</div>";

// ==========================================
// 7. ACCIONES
// ==========================================
echo "<div class='section'>";
echo "<h2>7. Acciones Disponibles</h2>";
echo "<a href='test_formulario_inscripcion.php' class='btn'>📝 Formulario de Test Simplificado</a>";
echo "<a href='index.php?ruta=inscripcion' class='btn'>🏠 Ir a Inscripción Normal</a>";
echo "<a href='?ejecutar_test=1' class='btn' onclick='return confirm(\"¿Ejecutar test de inserción?\")'>🧪 Ejecutar Test de Inserción</a>";
echo "</div>";

echo "</body></html>";
?>
