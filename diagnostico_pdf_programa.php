<?php
/**
 * Diagnóstico: Verificar qué programa se está obteniendo para el PDF
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/inscripcionmodulo.modelo.php';

echo "=== DIAGNÓSTICO PDF PROGRAMA ===\n\n";

// Obtener ID del estudiante (puedes cambiar este valor)
$estudianteID = isset($_GET['id']) ? intval($_GET['id']) : 1;

echo "Buscando datos del estudiante ID: $estudianteID\n\n";

// Primero verificar todas las inscripciones del estudiante
try {
    $stmt = Conexion::Conectar()->prepare(
        "SELECT
            ep.idInscripcion,
            ep.EstudianteID,
            ep.ProgramaID,
            ep.FechaInscripcion,
            ep.Estado,
            e.Nombre,
            e.Apaterno,
            p.NombrePrograma,
            p.GradoAcademico
        FROM estudianteprograma ep
        INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        WHERE ep.EstudianteID = :estudianteID
        ORDER BY ep.FechaInscripcion DESC"
    );
    $stmt->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
    $stmt->execute();
    $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "--- TODAS LAS INSCRIPCIONES DEL ESTUDIANTE ---\n";
    foreach ($inscripciones as $i => $insc) {
        echo "\nInscripción #" . ($i + 1) . ":\n";
        echo "  - ID Inscripción: " . $insc['idInscripcion'] . "\n";
        echo "  - Estudiante: " . $insc['Nombre'] . " " . $insc['Apaterno'] . "\n";
        echo "  - Programa: " . $insc['NombrePrograma'] . "\n";
        echo "  - Grado: " . $insc['GradoAcademico'] . "\n";
        echo "  - Estado: " . $insc['Estado'] . "\n";
        echo "  - Fecha: " . $insc['FechaInscripcion'] . "\n";
    }

    echo "\n\n--- DATOS ACTIVOS ÚNICAMENTE ---\n";
    $stmt2 = Conexion::Conectar()->prepare(
        "SELECT
            ep.idInscripcion,
            ep.EstudianteID,
            ep.ProgramaID,
            ep.FechaInscripcion,
            ep.Estado,
            e.Nombre,
            e.Apaterno,
            p.NombrePrograma,
            p.GradoAcademico
        FROM estudianteprograma ep
        INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        WHERE ep.EstudianteID = :estudianteID
        AND ep.Estado = 'ACTIVO'
        ORDER BY ep.FechaInscripcion DESC"
    );
    $stmt2->bindParam(":estudianteID", $estudianteID, PDO::PARAM_INT);
    $stmt2->execute();
    $activos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($activos as $i => $act) {
        echo "\nInscripción Activa #" . ($i + 1) . ":\n";
        echo "  - ID Inscripción: " . $act['idInscripcion'] . "\n";
        echo "  - Estudiante: " . $act['Nombre'] . " " . $act['Apaterno'] . "\n";
        echo "  - Programa: " . $act['NombrePrograma'] . "\n";
        echo "  - Grado: " . $act['GradoAcademico'] . "\n";
        echo "  - Fecha: " . $act['FechaInscripcion'] . "\n";
    }

    echo "\n\n--- DATOS QUE OBTIENE EL MODELO (para PDF) ---\n";
    $datosModelo = InscripcionModuloModelos::ObtenerDatosCompletosEstudianteModelo($estudianteID);

    if ($datosModelo) {
        echo "\nDatos obtenidos:\n";
        echo "  - Estudiante: " . $datosModelo['Nombre'] . " " . $datosModelo['Apaterno'] . "\n";
        echo "  - Programa: " . $datosModelo['NombrePrograma'] . "\n";
        echo "  - Grado: " . $datosModelo['GradoAcademico'] . "\n";
        echo "  - Código: " . $datosModelo['CodigoPrograma'] . "\n";
        echo "  - Estado: " . $datosModelo['Estado'] . "\n";
        echo "  - Fecha Inscripción: " . $datosModelo['FechaInscripcion'] . "\n";
    } else {
        echo "No se encontraron datos\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
