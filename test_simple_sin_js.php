<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Simple - Sin JavaScript</h1>";
echo "<h2>Diagnóstico de envío del formulario</h2>";

// Mostrar si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #e8f5e9; padding: 15px; border-left: 5px solid #4CAF50;'>";
    echo "<h3>✓ Formulario Enviado (POST detectado)</h3>";

    echo "<h4>Datos POST recibidos:</h4>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    echo "<h4>Archivos FILES recibidos:</h4>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";

    // Intentar procesar
    if (isset($_POST['registrarMatricula'])) {
        echo "<div style='background: #fff3e0; padding: 10px; margin: 10px 0;'>";
        echo "<strong>⚠️ Botón registrarMatricula detectado</strong>";
        echo "</div>";

        require_once 'modelos/conexion.modelo.php';
        require_once 'modelos/matricula.modelo.php';
        require_once 'controladores/matricula.controlador.php';

        echo "<h4>Ejecutando controlador...</h4>";

        // Capturar output
        ob_start();
        $controlador = new MatriculaControladores();
        $controlador->RegistrarMatriculaControlador();
        $output = ob_get_clean();

        echo "<div style='background: #f4f4f4; padding: 10px; margin: 10px 0;'>";
        echo "<h4>Output del Controlador:</h4>";
        if (empty($output)) {
            echo "<p style='color: red;'><strong>⚠️ El controlador no generó ninguna salida</strong></p>";
            echo "<p>Esto puede significar que:</p>";
            echo "<ul>";
            echo "<li>El controlador no detectó el POST</li>";
            echo "<li>Hay un error silencioso</li>";
            echo "<li>La validación falló antes de llegar al registro</li>";
            echo "</ul>";
        } else {
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
        echo "</div>";

        // Verificar último registro en BD
        try {
            $pdo = Conexion::Conectar();
            $stmt = $pdo->query("SELECT * FROM estudianteprograma ORDER BY idInscripcion DESC LIMIT 1");
            $ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ultimo) {
                echo "<div style='background: #e8f5e9; padding: 15px; margin: 10px 0;'>";
                echo "<h4>✓ Último registro en la BD:</h4>";
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
                foreach ($ultimo as $key => $value) {
                    if ($key == 'foto') {
                        echo "<tr><td><strong>$key</strong></td><td>[BLOB - " . strlen($value) . " bytes]</td></tr>";
                    } else {
                        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
                    }
                }
                echo "</table>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='background: #ffebee; padding: 10px;'>";
            echo "Error al consultar BD: " . $e->getMessage();
            echo "</div>";
        }
    }

    echo "</div>";
}

// Obtener datos para el formulario
require_once 'modelos/conexion.modelo.php';
$pdo = Conexion::Conectar();

$stmt = $pdo->query("SELECT EstudianteID, Nombre, Apaterno, Ci FROM estudiante WHERE Estado = 1 LIMIT 5");
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, GradoAcademico FROM programa WHERE Estado = 'ACTIVO' OR Estado = 1 LIMIT 5");
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Simple</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #1976D2; }
        .form-group { margin: 15px 0; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #45a049; }
        .info { background: #e3f2fd; padding: 15px; margin: 15px 0; border-left: 5px solid #2196F3; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
    </style>
</head>
<body>
    <h1>📝 Formulario de Test Simplificado</h1>

    <div class="info">
        <strong>Este es un formulario sin JavaScript.</strong><br>
        Te mostrará exactamente qué datos se envían y si llegan al controlador.
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Estudiante: *</label>
            <select name="idcliente" required>
                <option value="">Seleccione...</option>
                <?php foreach ($estudiantes as $est): ?>
                    <option value="<?= $est['EstudianteID'] ?>">
                        <?= $est['Ci'] ?> - <?= $est['Nombre'] ?> <?= $est['Apaterno'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Grado Académico: *</label>
            <select name="gradoAcademico" required>
                <option value="">Seleccione...</option>
                <option value="DIPLOMADO">DIPLOMADO</option>
                <option value="MAESTRIA">MAESTRÍA</option>
                <option value="ESPECIALIDAD">ESPECIALIDAD</option>
            </select>
        </div>

        <div class="form-group">
            <label>Programa: *</label>
            <select name="programa" required>
                <option value="">Seleccione...</option>
                <?php foreach ($programas as $prog): ?>
                    <option value="<?= $prog['ProgramaID'] ?>">
                        <?= $prog['NombrePrograma'] ?> (<?= $prog['GradoAcademico'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Monto Matrícula: *</label>
            <input type="number" name="montoMatricula" value="1500" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label>Número Vaucher: *</label>
            <input type="text" name="numeroVaucher" value="<?= rand(10000, 99999) ?>" required>
        </div>

        <div class="form-group">
            <label>Fecha Inscripción: *</label>
            <input type="date" name="fechaInscripcion" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Comprobante (Imagen): (Opcional)</label>
            <input type="file" name="comprobanteImagen" accept="image/*">
            <small style="color: #666;">JPG, PNG, GIF - Máx 5MB (Opcional)</small>
        </div>

        <button type="submit" name="registrarMatricula">
            💾 Registrar Matrícula (TEST)
        </button>
    </form>

    <hr style="margin: 30px 0;">

    <h3>📊 Registros Actuales en estudianteprograma</h3>
    <?php
    try {
        $stmt = $pdo->query("SELECT ep.*, e.Nombre, e.Apaterno, p.NombrePrograma
                             FROM estudianteprograma ep
                             INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
                             INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
                             ORDER BY ep.idInscripcion DESC
                             LIMIT 5");
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($registros)) {
            echo "<p>No hay registros aún.</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Fecha</th><th>Estudiante</th><th>Programa</th><th>Costo</th><th>Estado</th></tr>";
            foreach ($registros as $reg) {
                echo "<tr>";
                echo "<td>" . $reg['idInscripcion'] . "</td>";
                echo "<td>" . $reg['FechaInscripcion'] . "</td>";
                echo "<td>" . $reg['Nombre'] . " " . $reg['Apaterno'] . "</td>";
                echo "<td>" . $reg['NombrePrograma'] . "</td>";
                echo "<td>Bs. " . number_format($reg['costomatricula'], 2) . "</td>";
                echo "<td>" . $reg['Estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
    ?>

    <hr>
    <p><a href="index.php?ruta=inscripcion">← Volver a Inscripción Normal</a></p>

</body>
</html>
