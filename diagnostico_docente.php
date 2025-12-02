<?php
/**
 * Script de diagnóstico para verificar asignaciones de docente
 */

require_once 'modelos/conexion.modelo.php';

$docenteID = 2; // Cambiar según necesites

echo "=== DIAGNÓSTICO DE ASIGNACIONES DEL DOCENTE ID: $docenteID ===\n\n";

try {
    $pdo = Conexion::Conectar();

    // 1. Verificar si el docente existe
    echo "1. VERIFICANDO DOCENTE...\n";
    $stmt = $pdo->prepare("SELECT * FROM docente WHERE DocenteID = :id");
    $stmt->bindParam(':id', $docenteID);
    $stmt->execute();
    $docente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($docente) {
        echo "   ✓ Docente encontrado: {$docente['Nombre']} {$docente['Apaterno']}\n\n";
    } else {
        echo "   ✗ Docente NO encontrado\n\n";
        exit;
    }

    // 2. Verificar módulos en tabla 'modulos' (con s)
    echo "2. VERIFICANDO MÓDULOS EN TABLA 'modulos' (con s)...\n";
    $stmt = $pdo->prepare("
        SELECT
            m.Idmodulo,
            m.nombremodulo,
            m.codigomodulo,
            m.estadomodulo,
            m.ProgramaId,
            m.DocenteID
        FROM modulos m
        WHERE m.DocenteID = :docenteID
    ");
    $stmt->bindParam(':docenteID', $docenteID);
    $stmt->execute();
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($modulos) > 0) {
        echo "   ✓ Encontrados " . count($modulos) . " módulos\n";
        foreach ($modulos as $mod) {
            echo "     - ID: {$mod['Idmodulo']}, Nombre: {$mod['nombremodulo']}, Estado: {$mod['estadomodulo']}, ProgramaID: {$mod['ProgramaId']}\n";
        }
    } else {
        echo "   ✗ No hay módulos en tabla 'modulos'\n";
    }
    echo "\n";

    // 3. Verificar módulos en tabla 'modulo' (sin s)
    echo "3. VERIFICANDO MÓDULOS EN TABLA 'modulo' (sin s)...\n";
    try {
        $stmt = $pdo->prepare("
            SELECT
                m.ModuloID,
                m.NombreModulo,
                m.Codigo,
                m.Estado,
                m.ProgramaID,
                m.DocenteID
            FROM modulo m
            WHERE m.DocenteID = :docenteID
        ");
        $stmt->bindParam(':docenteID', $docenteID);
        $stmt->execute();
        $modulos2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($modulos2) > 0) {
            echo "   ✓ Encontrados " . count($modulos2) . " módulos\n";
            foreach ($modulos2 as $mod) {
                echo "     - ID: {$mod['ModuloID']}, Nombre: {$mod['NombreModulo']}, Estado: {$mod['Estado']}, ProgramaID: {$mod['ProgramaID']}\n";
            }
        } else {
            echo "   ✗ No hay módulos en tabla 'modulo'\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Tabla 'modulo' no existe o error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 4. Probar consulta completa con subconsultas
    echo "4. PROBANDO CONSULTA COMPLETA CON SUBCONSULTAS...\n";
    try {
        $stmt = $pdo->prepare("
            SELECT
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
                 AND Estado = 'ACTIVO') as TotalEstudiantes,
                (SELECT COUNT(*)
                 FROM calificacion c
                 WHERE c.Idmodulo = m.Idmodulo
                 AND c.ProgramaId = p.ProgramaID
                 AND c.Nota IS NOT NULL) as TotalCalificados,
                (SELECT MAX(c.FechaRegistro)
                 FROM calificacion c
                 WHERE c.Idmodulo = m.Idmodulo
                 AND c.ProgramaId = p.ProgramaID) as UltimaCalificacion,
                (SELECT c.estado
                 FROM calificacion c
                 WHERE c.Idmodulo = m.Idmodulo
                 AND c.ProgramaId = p.ProgramaID
                 LIMIT 1) as EstadoCalificacion,
                (SELECT
                    CASE
                        WHEN u.DocenteID IS NOT NULL THEN CONCAT(d.Nombre, ' ', d.Apaterno)
                        WHEN u.EstudianteID IS NOT NULL THEN CONCAT(e.Nombre, ' ', e.Apaterno)
                        ELSE u.Usuario
                    END
                 FROM calificacion c
                 LEFT JOIN usuario u ON c.UsuarioRegistroID = u.ID
                 LEFT JOIN docente d ON u.DocenteID = d.DocenteID
                 LEFT JOIN estudiante e ON u.EstudianteID = e.EstudianteID
                 WHERE c.Idmodulo = m.Idmodulo
                 AND c.ProgramaId = p.ProgramaID
                 AND c.UsuarioRegistroID IS NOT NULL
                 LIMIT 1) as RegistradoPor,
                (SELECT u.Tipo
                 FROM calificacion c
                 LEFT JOIN usuario u ON c.UsuarioRegistroID = u.ID
                 WHERE c.Idmodulo = m.Idmodulo
                 AND c.ProgramaId = p.ProgramaID
                 AND c.UsuarioRegistroID IS NOT NULL
                 LIMIT 1) as TipoUsuarioRegistro
            FROM modulos m
            INNER JOIN programa p ON m.ProgramaId = p.ProgramaID
            WHERE m.DocenteID = :docenteID
            AND m.estadomodulo = 'ACTIVO'
            ORDER BY p.GradoAcademico, p.NombrePrograma, m.codigomodulo
        ");
        $stmt->bindParam(':docenteID', $docenteID);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultado) > 0) {
            echo "   ✓ Consulta exitosa, retornó " . count($resultado) . " registros\n";
            foreach ($resultado as $r) {
                echo "     - {$r['nombremodulo']} | Programa: {$r['NombrePrograma']} | Estudiantes: {$r['TotalEstudiantes']} | Calificados: {$r['TotalCalificados']}\n";
            }
        } else {
            echo "   ✗ Consulta exitosa pero sin resultados\n";
        }
    } catch (Exception $e) {
        echo "   ✗ ERROR en consulta: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 5. Verificar programas
    echo "5. VERIFICANDO PROGRAMAS...\n";
    if (count($modulos) > 0) {
        $programaIds = array_unique(array_column($modulos, 'ProgramaId'));
        foreach ($programaIds as $pid) {
            $stmt = $pdo->prepare("SELECT * FROM programa WHERE ProgramaID = :id");
            $stmt->bindParam(':id', $pid);
            $stmt->execute();
            $prog = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($prog) {
                echo "   ✓ Programa ID {$pid}: {$prog['NombrePrograma']}\n";
            } else {
                echo "   ✗ Programa ID {$pid}: NO ENCONTRADO (esto causa el problema)\n";
            }
        }
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
