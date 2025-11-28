<?php
/**
 * Diagnóstico: Verificar asignaciones de docentes en la tabla modulos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "=== DIAGNÓSTICO DE ASIGNACIONES DE DOCENTES ===\n\n";

// Obtener un ID de docente de ejemplo
$docenteID = isset($_GET['id']) ? intval($_GET['id']) : 1;

echo "Buscando asignaciones del Docente ID: $docenteID\n\n";

try {
    // Verificar estructura de la tabla modulos
    echo "--- ESTRUCTURA DE LA TABLA 'modulos' ---\n";
    $stmt = Conexion::Conectar()->query("DESCRIBE modulos");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($columnas as $col) {
        echo "  - {$col['Field']}: {$col['Type']}\n";
    }

    echo "\n--- VERIFICAR SI EXISTE DocenteID EN LA TABLA ---\n";
    $tieneDocenteID = false;
    foreach ($columnas as $col) {
        if ($col['Field'] === 'DocenteID') {
            $tieneDocenteID = true;
            echo "  ✓ Campo DocenteID encontrado\n";
            break;
        }
    }

    if (!$tieneDocenteID) {
        echo "  ✗ Campo DocenteID NO encontrado en la tabla modulos\n";
    }

    echo "\n--- TODOS LOS REGISTROS DE LA TABLA modulos ---\n";
    $stmt2 = Conexion::Conectar()->query("SELECT * FROM modulos LIMIT 10");
    $modulos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($modulos as $i => $mod) {
        echo "\nMódulo #" . ($i + 1) . ":\n";
        foreach ($mod as $key => $value) {
            echo "  - $key: $value\n";
        }
    }

    echo "\n--- BUSCAR MÓDULOS DEL DOCENTE ID: $docenteID ---\n";

    if ($tieneDocenteID) {
        $stmt3 = Conexion::Conectar()->prepare(
            "SELECT * FROM modulos WHERE DocenteID = :docenteID"
        );
        $stmt3->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
        $stmt3->execute();
        $modulosDocente = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        if (count($modulosDocente) > 0) {
            echo "  ✓ Se encontraron " . count($modulosDocente) . " módulos asignados:\n";
            foreach ($modulosDocente as $i => $mod) {
                echo "\n  Módulo #" . ($i + 1) . ":\n";
                echo "    - ID: {$mod['Idmodulo']}\n";
                echo "    - Nombre: {$mod['nombremodulo']}\n";
                echo "    - Código: {$mod['codigomodulo']}\n";
                echo "    - DocenteID: {$mod['DocenteID']}\n";
                echo "    - Estado: {$mod['estadomodulo']}\n";
            }
        } else {
            echo "  ✗ No se encontraron módulos para este docente\n";
        }
    }

    echo "\n--- CONSULTA CON JOIN (como en el modelo) ---\n";
    $stmt4 = Conexion::Conectar()->prepare(
        "SELECT DISTINCT
            m.Idmodulo,
            m.nombremodulo,
            m.codigomodulo,
            m.DocenteID,
            p.ProgramaID,
            p.NombrePrograma,
            p.GradoAcademico,
            p.Codigo as CodigoPrograma,
            COUNT(DISTINCT ep.EstudianteID) as TotalEstudiantes
        FROM modulos m
        INNER JOIN estudianteprograma ep ON m.idinscripcion = ep.idInscripcion
        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        WHERE m.DocenteID = :docenteID
        AND m.estadomodulo = 'ACTIVO'
        AND ep.Estado = 'ACTIVO'
        GROUP BY m.Idmodulo, m.nombremodulo, m.codigomodulo, m.DocenteID, p.ProgramaID, p.NombrePrograma, p.GradoAcademico, p.Codigo
        ORDER BY p.GradoAcademico, p.NombrePrograma, m.codigomodulo"
    );
    $stmt4->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
    $stmt4->execute();
    $resultadoJoin = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultadoJoin) > 0) {
        echo "  ✓ Consulta con JOIN devolvió " . count($resultadoJoin) . " resultados:\n";
        foreach ($resultadoJoin as $i => $res) {
            echo "\n  Resultado #" . ($i + 1) . ":\n";
            echo "    - Módulo: {$res['nombremodulo']}\n";
            echo "    - Código: {$res['codigomodulo']}\n";
            echo "    - Programa: {$res['NombrePrograma']}\n";
            echo "    - Grado: {$res['GradoAcademico']}\n";
            echo "    - Estudiantes: {$res['TotalEstudiantes']}\n";
        }
    } else {
        echo "  ✗ Consulta con JOIN no devolvió resultados\n";
        echo "  Posibles causas:\n";
        echo "    1. El módulo no tiene inscripciones asociadas\n";
        echo "    2. El campo idinscripcion en modulos no coincide con idInscripcion en estudianteprograma\n";
        echo "    3. El estado del módulo no es 'ACTIVO'\n";
        echo "    4. El estado de estudianteprograma no es 'ACTIVO'\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
