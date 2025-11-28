<?php
/**
 * Diagnóstico completo: Ver todos los docentes y sus módulos asignados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "=== DIAGNÓSTICO COMPLETO DE DOCENTES Y MÓDULOS ===\n\n";

try {
    // 1. Listar todos los docentes
    echo "--- LISTA DE DOCENTES EN LA TABLA ---\n";
    $stmt = Conexion::Conectar()->query("SELECT DocenteID, Ci, Nombre, Apaterno, Amaterno FROM docente");
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($docentes as $i => $doc) {
        echo "\nDocente #" . ($i + 1) . ":\n";
        echo "  - DocenteID: {$doc['DocenteID']}\n";
        echo "  - Nombre: {$doc['Nombre']} {$doc['Apaterno']} {$doc['Amaterno']}\n";
        echo "  - CI: {$doc['Ci']}\n";
    }

    // 2. Ver todos los módulos con sus docentes
    echo "\n\n--- TODOS LOS MÓDULOS Y SUS DOCENTES ASIGNADOS ---\n";
    $stmt2 = Conexion::Conectar()->query(
        "SELECT
            m.Idmodulo,
            m.nombremodulo,
            m.codigomodulo,
            m.DocenteID,
            m.ProgramaId,
            m.estadomodulo,
            d.Nombre as NombreDocente,
            d.Apaterno as ApaternoDocente,
            p.NombrePrograma,
            p.GradoAcademico
        FROM modulos m
        LEFT JOIN docente d ON m.DocenteID = d.DocenteID
        LEFT JOIN programa p ON m.ProgramaId = p.ProgramaID
        ORDER BY m.DocenteID, m.Idmodulo"
    );
    $modulos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $modulosPorDocente = [];
    foreach ($modulos as $mod) {
        $docenteID = $mod['DocenteID'];
        if (!isset($modulosPorDocente[$docenteID])) {
            $modulosPorDocente[$docenteID] = [];
        }
        $modulosPorDocente[$docenteID][] = $mod;
    }

    foreach ($modulosPorDocente as $docenteID => $mods) {
        $nombreDoc = $mods[0]['NombreDocente'] ? $mods[0]['NombreDocente'] . ' ' . $mods[0]['ApaternoDocente'] : 'SIN ASIGNAR';
        echo "\nDocenteID: $docenteID - $nombreDoc\n";
        echo "Total módulos asignados: " . count($mods) . "\n";

        foreach ($mods as $i => $m) {
            echo "  Módulo " . ($i + 1) . ":\n";
            echo "    - ID: {$m['Idmodulo']}\n";
            echo "    - Nombre: {$m['nombremodulo']}\n";
            echo "    - Código: {$m['codigomodulo']}\n";
            echo "    - Programa: {$m['NombrePrograma']}\n";
            echo "    - Grado: {$m['GradoAcademico']}\n";
            echo "    - Estado: {$m['estadomodulo']}\n";
        }
    }

    // 3. Verificar NULL en DocenteID
    echo "\n\n--- MÓDULOS SIN DOCENTE ASIGNADO (DocenteID NULL o 0) ---\n";
    $stmt3 = Conexion::Conectar()->query(
        "SELECT Idmodulo, nombremodulo, codigomodulo, DocenteID
         FROM modulos
         WHERE DocenteID IS NULL OR DocenteID = 0"
    );
    $sinDocente = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    if (count($sinDocente) > 0) {
        foreach ($sinDocente as $m) {
            echo "  - ID {$m['Idmodulo']}: {$m['nombremodulo']} (DocenteID: {$m['DocenteID']})\n";
        }
    } else {
        echo "  ✓ Todos los módulos tienen docente asignado\n";
    }

    // 4. Probar la consulta del modelo para cada docente
    echo "\n\n--- PRUEBA DE CONSULTA DEL MODELO PARA CADA DOCENTE ---\n";

    foreach ($docentes as $doc) {
        $docenteID = $doc['DocenteID'];
        $nombreCompleto = $doc['Nombre'] . ' ' . $doc['Apaterno'] . ' ' . $doc['Amaterno'];

        echo "\nDocente: $nombreCompleto (ID: $docenteID)\n";

        $stmt4 = Conexion::Conectar()->prepare(
            "SELECT
                m.Idmodulo,
                m.nombremodulo,
                m.codigomodulo,
                m.DocenteID,
                p.ProgramaID,
                p.NombrePrograma,
                p.GradoAcademico,
                p.Codigo as CodigoPrograma,
                (SELECT COUNT(DISTINCT EstudianteID)
                 FROM estudianteprograma
                 WHERE ProgramaID = p.ProgramaID
                 AND Estado = 'ACTIVO') as TotalEstudiantes
            FROM modulos m
            INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
            WHERE m.DocenteID = :docenteID
            AND m.estadomodulo = 'ACTIVO'
            ORDER BY p.GradoAcademico, p.NombrePrograma, m.codigomodulo"
        );
        $stmt4->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
        $stmt4->execute();
        $resultados = $stmt4->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados) > 0) {
            echo "  ✓ Encontrados " . count($resultados) . " módulos activos\n";
            foreach ($resultados as $r) {
                echo "    - {$r['nombremodulo']} ({$r['codigomodulo']}) - {$r['NombrePrograma']}\n";
            }
        } else {
            echo "  ✗ No se encontraron módulos activos\n";

            // Ver si tiene módulos con otro estado
            $stmt5 = Conexion::Conectar()->prepare(
                "SELECT Idmodulo, nombremodulo, estadomodulo
                 FROM modulos
                 WHERE DocenteID = :docenteID"
            );
            $stmt5->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
            $stmt5->execute();
            $todosModulos = $stmt5->fetchAll(PDO::FETCH_ASSOC);

            if (count($todosModulos) > 0) {
                echo "  Tiene " . count($todosModulos) . " módulos pero con estado diferente:\n";
                foreach ($todosModulos as $tm) {
                    echo "    - {$tm['nombremodulo']} (Estado: {$tm['estadomodulo']})\n";
                }
            }
        }
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
