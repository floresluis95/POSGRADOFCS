<?php
/**
 * Archivo de prueba para verificar programas por grado académico
 */

// Incluir conexión
require_once 'modelos/conexion.modelo.php';

echo "<h2>Test de Programas por Grado Académico</h2>";
echo "<hr>";

// 1. Verificar todos los programas y sus grados académicos
echo "<h3>1. Todos los programas en la base de datos:</h3>";
try {
    $stmt = Conexion::Conectar()->prepare("SELECT ProgramaID, NombrePrograma, GradoAcademico, Estado FROM programa");
    $stmt->execute();
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($programas) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Grado Académico (valor exacto)</th><th>Estado</th></tr>";
        foreach ($programas as $programa) {
            echo "<tr>";
            echo "<td>{$programa['ProgramaID']}</td>";
            echo "<td>{$programa['NombrePrograma']}</td>";
            echo "<td><strong>" . htmlspecialchars($programa['GradoAcademico']) . "</strong> (length: " . strlen($programa['GradoAcademico']) . ")</td>";
            echo "<td>" . ($programa['Estado'] == 1 ? 'Activo' : 'Inactivo') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No hay programas en la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 2. Probar búsqueda por cada grado académico
$grados = ['DIPLOMADO', 'MAESTRIA', 'ESPECIALIDAD', 'MAESTRÍA'];

foreach ($grados as $grado) {
    echo "<h3>2. Programas con GradoAcademico = '$grado':</h3>";
    try {
        $stmt = Conexion::Conectar()->prepare(
            "SELECT ProgramaID, NombrePrograma, GradoAcademico, Codigo, Costo, Modulos, Sede
             FROM programa
             WHERE GradoAcademico = :gradoAcademico AND Estado = 1
             ORDER BY NombrePrograma ASC"
        );
        $stmt->bindParam(":gradoAcademico", $grado, PDO::PARAM_STR);
        $stmt->execute();
        $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<p>Encontrados: <strong>" . count($programas) . "</strong> programas</p>";

        if ($programas && count($programas) > 0) {
            echo "<ul>";
            foreach ($programas as $programa) {
                echo "<li>{$programa['NombrePrograma']} - {$programa['Sede']} (Bs. {$programa['Costo']})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>No se encontraron programas para este grado</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
    echo "<hr>";
}

// 3. Probar el modelo directamente
echo "<h3>3. Probar el modelo InscripcionModelos:</h3>";
require_once 'modelos/inscripcion.modelo.php';

foreach ($grados as $grado) {
    echo "<p>Probando con: <strong>$grado</strong></p>";
    $result = InscripcionModelos::ListarProgramasPorGradoModelo($grado);
    echo "<p>Resultado: " . (is_array($result) ? count($result) . " programas" : "false") . "</p>";
    if (is_array($result) && count($result) > 0) {
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
    echo "<br>";
}

echo "<hr>";
echo "<h3>4. Test de la petición AJAX simulada:</h3>";
echo "<p>Simular POST con action='obtenerProgramas' y gradoAcademico='DIPLOMADO':</p>";

// Simular POST
$_POST['action'] = 'obtenerProgramas';
$_POST['gradoAcademico'] = 'DIPLOMADO';

ob_start();
require_once 'controladores/inscripcion.controlador.php';
$output = ob_get_clean();

echo "<pre>";
echo htmlspecialchars($output);
echo "</pre>";
?>
