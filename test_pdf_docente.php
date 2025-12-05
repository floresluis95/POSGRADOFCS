<?php
/**
 * Script de diagnóstico para generación de PDF
 */

session_start();

echo "<h2>Diagnóstico de Generación de PDF</h2>";
echo "<hr>";

// 1. Verificar sesión
echo "<h3>1. Verificación de Sesión</h3>";
if (isset($_SESSION['Validar']) && $_SESSION['Validar'] === true) {
    echo "✓ Sesión válida<br>";
    echo "Usuario: " . ($_SESSION['Usuario'] ?? 'No definido') . "<br>";
} else {
    echo "✗ Sesión no válida<br>";
    die("Debes iniciar sesión como docente");
}

echo "<hr>";

// 2. Verificar conexión y datos
require_once 'modelos/conexion.modelo.php';
require_once 'modelos/calificacion.modelo.php';

echo "<h3>2. Obtener Datos del Docente</h3>";

try {
    $pdo = Conexion::Conectar();
    $usuario = $_SESSION['Usuario'] ?? '';

    echo "Buscando docente con CI: $usuario<br><br>";

    $stmt = $pdo->prepare("
        SELECT
            DocenteID,
            Ci,
            Complemento,
            Exp,
            Nombre,
            Apaterno,
            Amaterno,
            Especialidad
        FROM docente
        WHERE Ci = :ci
        AND Estado = 1
        LIMIT 1
    ");
    $stmt->bindParam(":ci", $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $docente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($docente) {
        echo "✓ Docente encontrado:<br>";
        echo "<pre>";
        print_r($docente);
        echo "</pre>";

        $docenteID = $docente['DocenteID'];
        $docenteNombre = $docente['Nombre'] . ' ' . $docente['Apaterno'] . ' ' . $docente['Amaterno'];

        echo "<hr>";
        echo "<h3>3. Obtener Asignaciones del Docente</h3>";

        $modulos = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docenteID);

        echo "Número de módulos encontrados: " . count($modulos) . "<br><br>";

        if (count($modulos) > 0) {
            echo "✓ Módulos encontrados:<br>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Idmodulo</th><th>Nombre</th><th>Código</th><th>ProgramaID</th><th>Programa</th><th>Inscritos</th><th>Calificados</th></tr>";
            foreach ($modulos as $mod) {
                echo "<tr>";
                echo "<td>" . $mod['Idmodulo'] . "</td>";
                echo "<td>" . $mod['nombremodulo'] . "</td>";
                echo "<td>" . $mod['codigomodulo'] . "</td>";
                echo "<td>" . ($mod['ProgramaID'] ?? 'NULL') . "</td>";
                echo "<td>" . ($mod['NombrePrograma'] ?? 'NULL') . "</td>";
                echo "<td>" . ($mod['TotalEstudiantes'] ?? '0') . "</td>";
                echo "<td>" . ($mod['TotalCalificados'] ?? '0') . "</td>";
                echo "</tr>";
            }
            echo "</table>";

            echo "<hr>";
            echo "<h3>4. Verificar Estudiantes del Primer Módulo</h3>";

            $primerModulo = $modulos[0];
            $moduloID = $primerModulo['Idmodulo'];
            $programaID = $primerModulo['ProgramaID'] ?? null;

            if ($programaID) {
                $estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

                echo "Estudiantes en módulo '" . $primerModulo['nombremodulo'] . "': " . count($estudiantes) . "<br><br>";

                if (count($estudiantes) > 0) {
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr><th>Estudiante</th><th>CI</th><th>Nota</th></tr>";
                    foreach (array_slice($estudiantes, 0, 5) as $est) {
                        echo "<tr>";
                        echo "<td>" . $est['Nombre'] . " " . $est['Apaterno'] . "</td>";
                        echo "<td>" . $est['Ci'] . "</td>";
                        echo "<td>" . ($est['Nota'] ?? 'Sin calificar') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    if (count($estudiantes) > 5) {
                        echo "<p><em>... y " . (count($estudiantes) - 5) . " más</em></p>";
                    }
                } else {
                    echo "⚠ No hay estudiantes en este módulo<br>";
                }
            } else {
                echo "✗ ProgramaID es NULL para el primer módulo<br>";
            }

            echo "<hr>";
            echo "<h3>5. Probar Generación de PDF</h3>";
            echo "<p>Datos disponibles para generar PDF:</p>";
            echo "<ul>";
            echo "<li>Docente: $docenteNombre</li>";
            echo "<li>Módulos: " . count($modulos) . "</li>";
            echo "<li>Estudiantes en primer módulo: " . (isset($estudiantes) ? count($estudiantes) : 0) . "</li>";
            echo "</ul>";

            echo "<br>";
            echo "<a href='tcpdf/pdf/reporte-completo-docente.php' target='_blank' class='btn btn-primary'>";
            echo "Intentar Generar PDF Completo</a>";
            echo "<br><br>";
            echo "<a href='tcpdf/pdf/generar-calificaciones-pdf.php?moduloID=$moduloID&programaID=$programaID' target='_blank' class='btn btn-primary'>";
            echo "Intentar Generar PDF del Primer Módulo (GET)</a>";

        } else {
            echo "✗ No se encontraron módulos asignados<br>";
            echo "<br><strong>PROBLEMA:</strong> El docente no tiene módulos asignados o hay un problema con la consulta SQL.";
        }

    } else {
        echo "✗ No se encontró docente con CI: $usuario<br>";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}

echo "<hr>";
echo "<p><a href='index.php?ruta=reportecalificaciones'>Volver a Reporte Calificaciones</a></p>";
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; margin: 10px 0; }
    th { background: #667eea; color: white; }
    .btn-primary {
        background: #667eea;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
        margin: 5px;
    }
</style>
