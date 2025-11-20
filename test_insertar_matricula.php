<?php
// Script de prueba para insertar una matrícula de ejemplo
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/matricula.modelo.php';

echo "<h2>Test de Inserción de Matrícula</h2>";

// Verificar si existen estudiantes y programas
try {
    $pdo = Conexion::Conectar();

    // Obtener un estudiante de ejemplo
    $stmt = $pdo->query("SELECT EstudianteID, Nombre, Apaterno, Ci FROM estudiante WHERE Estado = 1 LIMIT 1");
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        die("✗ No hay estudiantes activos en la BD");
    }

    echo "<h3>Estudiante de prueba:</h3>";
    echo "ID: " . $estudiante['EstudianteID'] . "<br>";
    echo "Nombre: " . $estudiante['Nombre'] . " " . $estudiante['Apaterno'] . "<br>";
    echo "CI: " . $estudiante['Ci'] . "<br><br>";

    // Obtener un programa de ejemplo
    $stmt = $pdo->query("SELECT ProgramaID, NombrePrograma, GradoAcademico, Costo FROM programa WHERE Estado = 'ACTIVO' OR Estado = 1 LIMIT 1");
    $programa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$programa) {
        die("✗ No hay programas activos en la BD");
    }

    echo "<h3>Programa de prueba:</h3>";
    echo "ID: " . $programa['ProgramaID'] . "<br>";
    echo "Nombre: " . $programa['NombrePrograma'] . "<br>";
    echo "Grado: " . $programa['GradoAcademico'] . "<br>";
    echo "Costo: Bs. " . $programa['Costo'] . "<br><br>";

    // Verificar si ya existe una inscripción
    $existe = MatriculaModelos::VerificarInscripcionExistente($estudiante['EstudianteID'], $programa['ProgramaID']);

    if ($existe) {
        echo "<h3 style='color: orange;'>⚠ Ya existe una inscripción de este estudiante en este programa</h3>";
        echo "<p>No se insertará un duplicado.</p>";
    } else {
        echo "<h3>Insertando matrícula de prueba...</h3>";

        // Crear una imagen de prueba (pixel transparente PNG)
        $imagenPrueba = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        $datosMatricula = array(
            "EstudianteID" => $estudiante['EstudianteID'],
            "ProgramaID" => $programa['ProgramaID'],
            "costomatricula" => 500,
            "nvauchermatricula" => 12345,
            "FechaInscripcion" => date('Y-m-d'),
            "foto" => $imagenPrueba
        );

        $resultado = MatriculaModelos::RegistrarMatriculaModelo($datosMatricula);

        if ($resultado == 'exitoso') {
            echo "<h3 style='color: green;'>✓ Matrícula insertada exitosamente</h3>";

            // Obtener el último ID insertado
            $stmt = $pdo->query("SELECT * FROM inscripcion ORDER BY idInscripcion DESC LIMIT 1");
            $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

            echo "<h3>Detalles de la inscripción:</h3>";
            echo "<table border='1' cellpadding='5'>";
            foreach ($inscripcion as $key => $value) {
                if ($key == 'foto') {
                    echo "<tr><td><strong>$key</strong></td><td>[BLOB - " . strlen($value) . " bytes]</td></tr>";
                } else {
                    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
                }
            }
            echo "</table>";

        } else {
            echo "<h3 style='color: red;'>✗ Error al insertar matrícula: $resultado</h3>";
        }
    }

    // Mostrar todas las inscripciones
    echo "<hr><h3>Lista de todas las inscripciones:</h3>";
    $inscripciones = MatriculaModelos::ListarInscripcionesModelo();

    if (empty($inscripciones)) {
        echo "<p>No hay inscripciones registradas</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Estudiante</th>";
        echo "<th>CI</th>";
        echo "<th>Programa</th>";
        echo "<th>Costo</th>";
        echo "<th>Fecha</th>";
        echo "<th>Estado</th>";
        echo "</tr>";

        foreach ($inscripciones as $ins) {
            echo "<tr>";
            echo "<td>" . $ins['idInscripcion'] . "</td>";
            echo "<td>" . $ins['Nombre'] . " " . $ins['Apaterno'] . "</td>";
            echo "<td>" . $ins['Ci'] . "</td>";
            echo "<td>" . $ins['NombrePrograma'] . "</td>";
            echo "<td>Bs. " . number_format($ins['costomatricula'], 2) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($ins['FechaInscripcion'])) . "</td>";
            echo "<td>" . $ins['Estado'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<hr>
<p><a href="test_matricula.php">Volver al test principal</a></p>
<p><a href="index.php?ruta=inscripcion">Ir a la página de inscripción</a></p>
