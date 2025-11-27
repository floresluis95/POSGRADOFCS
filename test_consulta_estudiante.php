<?php
/**
 * Script de prueba para verificar consulta de estudiante
 */

require_once 'modelos/inscripcionmodulo.modelo.php';

echo "=== Test de Consulta de Estudiante ===\n\n";

// Primero, listar algunos estudiantes matriculados
echo "1. Listando estudiantes matriculados:\n";
$estudiantes = InscripcionModuloModelos::ListarEstudiantesMatriculadosModelo();

if (!empty($estudiantes)) {
    echo "✓ Se encontraron " . count($estudiantes) . " estudiantes\n\n";

    // Mostrar los primeros 3 estudiantes
    $contador = 0;
    foreach ($estudiantes as $est) {
        if ($contador >= 3) break;
        echo "Estudiante #" . ($contador + 1) . ":\n";
        echo "  - ID: " . $est['EstudianteID'] . "\n";
        echo "  - Nombre: " . $est['Nombre'] . " " . $est['Apaterno'] . " " . $est['Amaterno'] . "\n";
        echo "  - CI: " . $est['Ci'] . "\n";
        echo "  - Programa: " . $est['NombrePrograma'] . "\n";
        echo "\n";

        // Probar la consulta completa con el primer estudiante
        if ($contador == 0) {
            $testID = $est['EstudianteID'];
            echo "2. Probando consulta completa con EstudianteID = $testID:\n";
            $datosCompletos = InscripcionModuloModelos::ObtenerDatosCompletosEstudianteModelo($testID);

            if ($datosCompletos) {
                echo "✓ Consulta exitosa!\n";
                echo "  - Campos obtenidos: " . count($datosCompletos) . "\n";
                echo "  - Nombre completo: " . $datosCompletos['Nombre'] . " " . $datosCompletos['Apaterno'] . "\n";
                echo "  - Programa: " . $datosCompletos['NombrePrograma'] . "\n";
            } else {
                echo "✗ Error: No se obtuvieron datos\n";
            }
            echo "\n";

            // Probar módulos inscritos
            echo "3. Probando módulos inscritos del estudiante:\n";
            $modulos = InscripcionModuloModelos::ObtenerModulosInscritosEstudianteModelo($testID);
            echo "  - Módulos encontrados: " . count($modulos) . "\n";
        }

        $contador++;
    }
} else {
    echo "✗ No se encontraron estudiantes matriculados\n";
}

echo "\n=== Fin del Test ===\n";
?>
