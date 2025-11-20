<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/matricula.modelo.php';
require_once 'controladores/matricula.controlador.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Test Final - estudianteprograma</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #1976D2; border-bottom: 3px solid #1976D2; padding-bottom: 10px; }
        .success { background: #e8f5e9; border-left: 5px solid #4CAF50; padding: 15px; margin: 10px 0; }
        .error { background: #ffebee; border-left: 5px solid #f44336; padding: 15px; margin: 10px 0; }
        .info { background: #e3f2fd; border-left: 5px solid #2196F3; padding: 15px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        .btn { padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #1976D2; }
        pre { background: #2d2d2d; color: #00ff00; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>";

echo "<h1>✅ Test Final - Tabla estudianteprograma</h1>";

// 1. Verificar tabla
echo "<div class='info'>";
echo "<h2>1. Verificación de Tabla</h2>";

try {
    $pdo = Conexion::Conectar();

    $stmt = $pdo->query("SHOW TABLES LIKE 'estudianteprograma'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✓ Tabla 'estudianteprograma' existe</div>";

        // Mostrar estructura
        $stmt = $pdo->query("DESCRIBE estudianteprograma");
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td><strong>" . $row['Field'] . "</strong></td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM estudianteprograma");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<div class='info'>📊 Registros actuales: <strong>" . $row['total'] . "</strong></div>";

    } else {
        echo "<div class='error'>✗ Tabla 'estudianteprograma' NO existe</div>";
        echo "<div class='error'>❌ <strong>ERROR CRÍTICO:</strong> La tabla no existe en la base de datos.</div>";
        echo "</div></body></html>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
    echo "</div></body></html>";
    exit;
}

echo "</div>";

// 2. Procesar formulario si se envía
if (isset($_POST['registrarMatricula'])) {
    echo "<div class='info'>";
    echo "<h2>2. Resultado del Registro</h2>";

    $controlador = new MatriculaControladores();
    ob_start();
    $controlador->RegistrarMatriculaControlador();
    $output = ob_get_clean();

    // Verificar si se insertó
    $stmt = $pdo->query("SELECT * FROM estudianteprograma ORDER BY idInscripcion DESC LIMIT 1");
    $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ultimo) {
        echo "<div class='success'><h3>✓ REGISTRO EXITOSO</h3>";
        echo "<table>";
        foreach ($ultimo as $key => $value) {
            if ($key == 'foto') {
                echo "<tr><td><strong>$key</strong></td><td>[BLOB - " . strlen($value) . " bytes]</td></tr>";
            } else {
                echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
            }
        }
        echo "</table></div>";
    }

    // Mostrar output de SweetAlert
    if (!empty($output)) {
        echo "<div class='info'><h4>Output del Controlador:</h4>";
        echo htmlspecialchars($output);
        echo "</div>";
    }

    echo "</div>";
}

// 3. Mostrar formulario
echo "<div class='info'>";
echo "<h2>3. Formulario de Prueba</h2>";

// Obtener datos
$stmt = $pdo->query("SELECT EstudianteID, Nombre, Apaterno, Ci FROM estudiante WHERE Estado = 1 LIMIT 5");
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, GradoAcademico FROM programa WHERE Estado = 'ACTIVO' OR Estado = 1 LIMIT 5");
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($estudiantes)) {
    echo "<div class='error'>✗ No hay estudiantes activos</div>";
} elseif (empty($programas)) {
    echo "<div class='error'>✗ No hay programas activos</div>";
} else {
    echo "<form method='POST' enctype='multipart/form-data'>";

    echo "<table>";
    echo "<tr><td><strong>Estudiante:</strong></td><td>";
    echo "<select name='idcliente' required>";
    echo "<option value=''>Seleccione...</option>";
    foreach ($estudiantes as $est) {
        echo "<option value='" . $est['EstudianteID'] . "'>" . $est['Ci'] . " - " . $est['Nombre'] . " " . $est['Apaterno'] . "</option>";
    }
    echo "</select></td></tr>";

    echo "<tr><td><strong>Programa:</strong></td><td>";
    echo "<select name='programa' required>";
    echo "<option value=''>Seleccione...</option>";
    foreach ($programas as $prog) {
        echo "<option value='" . $prog['ProgramaID'] . "'>" . $prog['NombrePrograma'] . " (" . $prog['GradoAcademico'] . ")</option>";
    }
    echo "</select></td></tr>";

    echo "<tr><td><strong>Monto Matrícula:</strong></td><td>";
    echo "<input type='number' name='montoMatricula' value='1500' step='0.01' min='0' required>";
    echo "</td></tr>";

    echo "<tr><td><strong>Número Vaucher:</strong></td><td>";
    echo "<input type='text' name='numeroVaucher' value='" . rand(10000, 99999) . "' required>";
    echo "</td></tr>";

    echo "<tr><td><strong>Fecha Inscripción:</strong></td><td>";
    echo "<input type='date' name='fechaInscripcion' value='" . date('Y-m-d') . "' required>";
    echo "</td></tr>";

    echo "<tr><td><strong>Comprobante:</strong></td><td>";
    echo "<input type='file' name='comprobanteImagen' accept='image/*'>";
    echo "<br><small>JPG, PNG, GIF - Máx 5MB (Opcional)</small>";
    echo "</td></tr>";

    echo "</table>";

    echo "<button type='submit' name='registrarMatricula' class='btn'>💾 Registrar en estudianteprograma</button>";
    echo "</form>";
}

echo "</div>";

// 4. Mostrar últimos registros
echo "<div class='info'>";
echo "<h2>4. Últimos Registros</h2>";

$stmt = $pdo->query("SELECT ep.*, e.Nombre, e.Apaterno, e.Ci, p.NombrePrograma
                     FROM estudianteprograma ep
                     INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                     INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                     ORDER BY ep.idInscripcion DESC
                     LIMIT 5");

$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($registros)) {
    echo "<div class='info'>No hay registros aún. Usa el formulario de arriba para crear uno.</div>";
} else {
    echo "<table>";
    echo "<tr><th>ID</th><th>Fecha</th><th>Estudiante</th><th>Programa</th><th>Costo</th><th>Estado</th></tr>";
    foreach ($registros as $reg) {
        echo "<tr>";
        echo "<td>" . $reg['idInscripcion'] . "</td>";
        echo "<td>" . $reg['FechaInscripcion'] . "</td>";
        echo "<td>" . $reg['Ci'] . " - " . $reg['Nombre'] . " " . $reg['Apaterno'] . "</td>";
        echo "<td>" . $reg['NombrePrograma'] . "</td>";
        echo "<td>Bs. " . number_format($reg['costomatricula'], 2) . "</td>";
        echo "<td>" . $reg['Estado'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "</div>";

// 5. SQL Query de verificación
echo "<div class='info'>";
echo "<h2>5. Consulta SQL para Verificar</h2>";
echo "<pre>SELECT * FROM estudianteprograma ORDER BY idInscripcion DESC LIMIT 5;</pre>";
echo "<p>Ejecuta esta consulta en phpMyAdmin para ver los registros.</p>";
echo "</div>";

echo "<div style='text-align: center; padding: 20px;'>";
echo "<a href='index.php?ruta=inscripcion' class='btn'>🏠 Volver a Inscripción</a>";
echo "</div>";

echo "</body></html>";
?>
