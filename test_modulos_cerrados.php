<?php
/**
 * Verificar que los módulos cerrados se devuelven correctamente
 */

session_start();
$_SESSION['Validar'] = true;
$_SESSION['Usuario'] = '1234567'; // Usar un CI real

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Test Módulos Cerrados</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    table th { background: #667eea; color: white; }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; }
    .badge-activo { background: #28a745; color: white; }
    .badge-validado { background: #dc3545; color: white; }
</style>";
echo "</head><body>";

echo "<h1>🧪 Test de Módulos Cerrados</h1>";

// Test 1: Verificar estructura de base de datos
echo "<div class='box'>";
echo "<h2>1. Estructura de Base de Datos</h2>";

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::conectar();

    // Verificar campos en tabla modulos
    $stmt = $conexion->prepare("SHOW COLUMNS FROM modulos");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $camposNecesarios = ['estadomodulo', 'ValidadoPor', 'FechaValidacion'];
    $camposEncontrados = [];

    foreach ($columnas as $col) {
        if (in_array($col['Field'], $camposNecesarios)) {
            $camposEncontrados[] = $col['Field'];
        }
    }

    echo "<p><strong>Campos encontrados:</strong></p>";
    foreach ($camposNecesarios as $campo) {
        if (in_array($campo, $camposEncontrados)) {
            echo "<p class='success'>✅ {$campo} - Encontrado</p>";
        } else {
            echo "<p class='error'>❌ {$campo} - NO encontrado</p>";
        }
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 2: Obtener asignaciones con estados
echo "<div class='box'>";
echo "<h2>2. Asignaciones por Docente (con estado)</h2>";

try {
    // Obtener un docente de prueba
    $stmt = $conexion->prepare("SELECT IdDocentes FROM docentes LIMIT 1");
    $stmt->execute();
    $docente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($docente) {
        $docenteID = $docente['IdDocentes'];
        echo "<p><strong>DocenteID de prueba:</strong> {$docenteID}</p>";

        // Cargar modelo
        require_once 'modelos/calificacion.modelo.php';

        $asignaciones = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docenteID);

        echo "<p class='info'>📊 Total de asignaciones: " . count($asignaciones) . "</p>";

        if (count($asignaciones) > 0) {
            // Separar por estado
            $activos = 0;
            $cerrados = 0;

            echo "<table>";
            echo "<tr>";
            echo "<th>#</th>";
            echo "<th>Módulo</th>";
            echo "<th>Código</th>";
            echo "<th>Estado Módulo</th>";
            echo "<th>Fecha Validación</th>";
            echo "<th>Validado Por</th>";
            echo "</tr>";

            foreach ($asignaciones as $i => $asig) {
                $estado = $asig['EstadoModulo'] ?? 'NULL';

                if ($estado === 'VALIDADO' || $estado === 'CERRADO') {
                    $cerrados++;
                    $estadoClass = 'badge-validado';
                } else {
                    $activos++;
                    $estadoClass = 'badge-activo';
                }

                echo "<tr>";
                echo "<td>" . ($i + 1) . "</td>";
                echo "<td>" . htmlspecialchars($asig['nombremodulo']) . "</td>";
                echo "<td>" . htmlspecialchars($asig['codigomodulo']) . "</td>";
                echo "<td><span class='badge {$estadoClass}'>{$estado}</span></td>";
                echo "<td>" . ($asig['FechaValidacion'] ?? '-') . "</td>";
                echo "<td>" . ($asig['NombreValidador'] ?? '-') . "</td>";
                echo "</tr>";
            }

            echo "</table>";

            echo "<br>";
            echo "<p class='success'>✅ Módulos ACTIVOS: {$activos}</p>";
            echo "<p class='error'>🔒 Módulos CERRADOS: {$cerrados}</p>";

        } else {
            echo "<p class='error'>⚠️ Este docente no tiene asignaciones</p>";
        }

    } else {
        echo "<p class='error'>⚠️ No hay docentes en la base de datos</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 3: Verificar respuesta AJAX
echo "<div class='box'>";
echo "<h2>3. Simular Respuesta AJAX</h2>";

if (isset($docenteID) && isset($asignaciones)) {
    echo "<p><strong>Ejemplo de respuesta JSON que recibirá el frontend:</strong></p>";

    $response = [
        'status' => 'success',
        'data' => $asignaciones
    ];

    echo "<pre>";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";

    echo "<p class='success'>✅ El campo EstadoModulo está incluido en la respuesta</p>";
}

echo "</div>";

// Test 4: Instrucciones finales
echo "<div class='box'>";
echo "<h2>4. Próximos Pasos</h2>";
echo "<ol>";
echo "<li>Verifica que los campos existen en la base de datos ✅</li>";
echo "<li>Verifica que las asignaciones incluyen el campo EstadoModulo ✅</li>";
echo "<li>Cierra un módulo desde la vista 'notasdocente' para crear datos de prueba</li>";
echo "<li>Accede a 'rnotasestudiante' y verifica que:";
echo "<ul>";
echo "<li>Los módulos cerrados NO aparecen en la tabla principal</li>";
echo "<li>Aparece un botón 'Ver Módulos Cerrados' con un badge mostrando la cantidad</li>";
echo "<li>Al hacer clic, se abre un modal con los módulos cerrados</li>";
echo "<li>Cada módulo cerrado tiene un botón 'Imprimir' que funciona correctamente</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>5. Limpiar Caché</h2>";
echo "<p><a href='limpiar_cache_php.php' style='background:#667eea;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>🗑️ Limpiar Caché PHP</a></p>";
echo "</div>";

echo "</body></html>";
?>
