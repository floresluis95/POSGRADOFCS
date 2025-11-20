<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/matricula.modelo.php';
require_once 'controladores/matricula.controlador.php';

echo "<h2>Test de Formulario de Inscripción</h2>";

// Procesar formulario
if (isset($_POST['registrarMatricula'])) {
    echo "<h3 style='color: blue;'>Formulario Enviado!</h3>";
    echo "<pre>";
    echo "Datos POST recibidos:\n";
    print_r($_POST);
    echo "\n\nDatos FILES recibidos:\n";
    print_r($_FILES);
    echo "</pre>";

    $controlador = new MatriculaControladores();
    $controlador->RegistrarMatriculaControlador();
}

// Obtener estudiantes y programas de prueba
try {
    $pdo = Conexion::Conectar();

    $stmt = $pdo->query("SELECT EstudianteID, Nombre, Apaterno, Ci FROM estudiante WHERE Estado = 1 LIMIT 5");
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, GradoAcademico FROM programa WHERE (Estado = 'ACTIVO' OR Estado = 1) LIMIT 5");
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Formulario Inscripción</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .required { color: red; }
    </style>
</head>
<body>
    <h1>🧪 Test de Formulario de Inscripción</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Estudiante <span class="required">*</span></label>
            <select name="idcliente" required>
                <option value="">Seleccione un estudiante</option>
                <?php foreach ($estudiantes as $est): ?>
                    <option value="<?= $est['EstudianteID'] ?>">
                        <?= $est['Ci'] ?> - <?= $est['Nombre'] ?> <?= $est['Apaterno'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Programa <span class="required">*</span></label>
            <select name="programa" required>
                <option value="">Seleccione un programa</option>
                <?php foreach ($programas as $prog): ?>
                    <option value="<?= $prog['ProgramaID'] ?>">
                        <?= $prog['NombrePrograma'] ?> (<?= $prog['GradoAcademico'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Monto Matrícula <span class="required">*</span></label>
            <input type="number" name="montoMatricula" step="0.01" min="0" value="1500" required>
        </div>

        <div class="form-group">
            <label>Número de Vaucher <span class="required">*</span></label>
            <input type="text" name="numeroVaucher" value="<?= rand(10000, 99999) ?>" required>
        </div>

        <div class="form-group">
            <label>Fecha Inscripción <span class="required">*</span></label>
            <input type="date" name="fechaInscripcion" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Comprobante (Imagen) <span class="required">*</span></label>
            <input type="file" name="comprobanteImagen" accept="image/*" required>
            <small style="color: #666;">JPG, PNG, GIF o PDF - Máximo 5MB</small>
        </div>

        <button type="submit" name="registrarMatricula">
            💾 Registrar Matrícula (TEST)
        </button>
    </form>

    <hr style="margin: 30px 0;">

    <h3>📋 Instrucciones:</h3>
    <ol>
        <li>Completa todos los campos del formulario</li>
        <li>Selecciona una imagen para el comprobante</li>
        <li>Haz clic en "Registrar Matrícula"</li>
        <li>Verifica si aparece mensaje de éxito o error</li>
        <li>Revisa los logs de PHP: <code>C:\xampp\apache\logs\error.log</code></li>
    </ol>

    <h3>🔍 Verificación en Base de Datos:</h3>
    <p>Para verificar si se guardó:</p>
    <pre style="background: #f4f4f4; padding: 10px; border-radius: 5px;">
SELECT * FROM inscripcion ORDER BY idInscripcion DESC LIMIT 1;
    </pre>

    <p><a href="index.php?ruta=inscripcion" style="color: #2196F3;">← Volver a la página de inscripción normal</a></p>

    <script>
        console.log('Test de Formulario de Inscripción cargado');
    </script>
</body>
</html>
