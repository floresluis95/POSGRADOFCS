<?php
/**
 * Script de diagnóstico para el problema de inscripción
 */

require_once 'modelos/conexion.modelo.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Diagnóstico: Inscripción</title>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
.container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h2 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
h3 { color: #764ba2; margin-top: 30px; }
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0; }
.warning { color: orange; font-weight: bold; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
table th { background: #667eea; color: white; padding: 12px; text-align: left; }
table td { padding: 10px; border-bottom: 1px solid #ddd; }
.highlight { background: #d4edda; font-weight: bold; }
code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
.section { background: #f9f9f9; padding: 15px; border-left: 4px solid #667eea; margin: 15px 0; }
hr { border: none; border-top: 2px solid #e0e0e0; margin: 30px 0; }
</style>";
echo "</head><body>";
echo "<div class='container'>";

echo "<h2>🔍 Diagnóstico: Error de Inscripción</h2>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

try {
    $pdo = Conexion::Conectar();

    // ==========================================
    // 1. VERIFICAR ESTRUCTURA DE LA TABLA
    // ==========================================
    echo "<h3>1️⃣ Estructura Actual de la Tabla 'estudianteprograma'</h3>";

    $stmt = $pdo->query("DESCRIBE estudianteprograma");
    $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>#</th><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";

    $camposRequeridos = ['montoPagado', 'pagoCompleto'];
    $camposEncontrados = [];
    $contador = 1;

    foreach ($estructura as $campo) {
        $highlight = in_array($campo['Field'], $camposRequeridos) ? "class='highlight'" : "";
        if (in_array($campo['Field'], $camposRequeridos)) {
            $camposEncontrados[] = $campo['Field'];
        }

        echo "<tr $highlight>";
        echo "<td>{$contador}</td>";
        echo "<td><strong>" . htmlspecialchars($campo['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($campo['Default']) . "</td>";
        echo "</tr>";
        $contador++;
    }
    echo "</table>";

    // Verificar campos faltantes
    $camposFaltantes = array_diff($camposRequeridos, $camposEncontrados);

    if (count($camposFaltantes) > 0) {
        echo "<div class='error'>";
        echo "❌ <strong>PROBLEMA ENCONTRADO:</strong> Faltan los siguientes campos:<br>";
        foreach ($camposFaltantes as $campo) {
            echo "• <code>$campo</code><br>";
        }
        echo "<br><strong>SOLUCIÓN:</strong> Ejecutar la migración en:<br>";
        echo "<code>http://localhost/POSGRADOFCS/ejecutar_migracion_pago_completo_estudianteprograma.php</code>";
        echo "</div>";
    } else {
        echo "<div class='success'>✓ Todos los campos requeridos están presentes</div>";
    }

    echo "<hr>";

    // ==========================================
    // 2. PROBAR INSERT DE PRUEBA
    // ==========================================
    echo "<h3>2️⃣ Prueba de INSERT (Sin Guardar)</h3>";

    // Verificar si hay estudiantes y programas para probar
    $stmtEstudiantes = $pdo->query("SELECT EstudianteID, Nombre, Apaterno FROM estudiante LIMIT 1");
    $estudiante = $stmtEstudiantes->fetch(PDO::FETCH_ASSOC);

    $stmtProgramas = $pdo->query("SELECT ProgramaID, NombrePrograma FROM programa LIMIT 1");
    $programa = $stmtProgramas->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante || !$programa) {
        echo "<div class='warning'>⚠ No hay estudiantes o programas para probar el INSERT</div>";
    } else {
        echo "<div class='section'>";
        echo "<strong>Datos de prueba:</strong><br>";
        echo "• Estudiante: {$estudiante['Nombre']} {$estudiante['Apaterno']} (ID: {$estudiante['EstudianteID']})<br>";
        echo "• Programa: {$programa['NombrePrograma']} (ID: {$programa['ProgramaID']})<br>";
        echo "</div>";

        // Intentar preparar el statement (sin ejecutar)
        try {
            $sql = "INSERT INTO estudianteprograma
                    (EstudianteID, ProgramaID, costomatricula, montoPagado, pagoCompleto, nvauchermatricula, FechaInscripcion, Estado)
                    VALUES (:estudianteID, :programaID, :costomatricula, :montoPagado, :pagoCompleto, :nvaucher, :fechaInscripcion, 'ACTIVO')";

            $stmt = $pdo->prepare($sql);
            echo "<div class='success'>✓ La consulta SQL se puede preparar correctamente</div>";

            echo "<div class='section'>";
            echo "<strong>Consulta SQL preparada:</strong><br>";
            echo "<pre>" . htmlspecialchars($sql) . "</pre>";
            echo "</div>";

        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "❌ <strong>ERROR al preparar la consulta:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";
        }
    }

    echo "<hr>";

    // ==========================================
    // 3. VERIFICAR ÚLTIMO ERROR EN LOGS
    // ==========================================
    echo "<h3>3️⃣ Verificación de Configuración PHP</h3>";

    echo "<table>";
    echo "<tr><th>Configuración</th><th>Valor</th></tr>";
    echo "<tr><td>display_errors</td><td>" . ini_get('display_errors') . "</td></tr>";
    echo "<tr><td>error_reporting</td><td>" . error_reporting() . "</td></tr>";
    echo "<tr><td>log_errors</td><td>" . ini_get('log_errors') . "</td></tr>";
    echo "<tr><td>error_log</td><td>" . ini_get('error_log') . "</td></tr>";
    echo "</table>";

    echo "<hr>";

    // ==========================================
    // 4. RECOMENDACIONES
    // ==========================================
    echo "<h3>4️⃣ Recomendaciones</h3>";

    if (count($camposFaltantes) > 0) {
        echo "<div class='error'>";
        echo "<strong>🔴 ACCIÓN REQUERIDA:</strong><br><br>";
        echo "1. Ejecutar la migración de base de datos:<br>";
        echo "<code>http://localhost/POSGRADOFCS/ejecutar_migracion_pago_completo_estudianteprograma.php</code><br><br>";
        echo "2. Volver a intentar la inscripción<br>";
        echo "</div>";
    } else {
        echo "<div class='section'>";
        echo "<strong>🔍 Los campos están presentes. Pasos adicionales:</strong><br><br>";
        echo "1. Verificar logs de error en el navegador (F12 > Console)<br>";
        echo "2. Revisar el archivo de logs de PHP<br>";
        echo "3. Probar registrar una inscripción y capturar el error exacto<br>";
        echo "</div>";

        // Crear un script para activar el modo debug
        echo "<h4>Activar Modo Debug Temporal</h4>";
        echo "<div class='section'>";
        echo "Agrega esto al inicio de <code>modelos/matricula.modelo.php</code>:<br>";
        echo "<pre>error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);</pre>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Error Fatal:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "<br><br><strong>Stack Trace:</strong><br>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center; color: #999;'><small>Script ejecutado: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</div>";
echo "</body></html>";
?>
