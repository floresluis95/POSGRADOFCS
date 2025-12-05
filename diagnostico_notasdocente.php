<?php
/**
 * Script de diagnóstico para verificar datos de notasdocente
 */

session_start();

echo "<h2>Diagnóstico de Notas Docente</h2>";
echo "<hr>";

// 1. Verificar sesión
echo "<h3>1. Verificación de Sesión</h3>";
if (isset($_SESSION['Validar']) && $_SESSION['Validar'] === true) {
    echo "✓ Sesión válida<br>";
    echo "Usuario: " . ($_SESSION['Usuario'] ?? 'No definido') . "<br>";
    echo "Rol: " . ($_SESSION['Rol'] ?? 'No definido') . "<br>";
    echo "ID Usuario: " . ($_SESSION['ID'] ?? 'No definido') . "<br>";
} else {
    echo "✗ Sesión no válida<br>";
    die("Debes iniciar sesión como docente");
}

echo "<hr>";

// 2. Verificar estructura de las tablas
echo "<h3>2. Estructura de las Tablas</h3>";

require_once 'modelos/conexion.modelo.php';

try {
    $pdo = Conexion::Conectar();

    // Verificar estructura de tabla docente
    echo "<strong>Estructura de la tabla 'docente':</strong><br>";
    $stmt = $pdo->query("DESCRIBE docente");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";

    // Verificar estructura de tabla usuario
    echo "<strong>Estructura de la tabla 'usuario':</strong><br>";
    $stmt = $pdo->query("DESCRIBE usuario");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 3. Obtener ID del docente
echo "<h3>3. Obtención de ID del Docente</h3>";

try {
    $usuario = $_SESSION['Usuario'] ?? '';
    echo "Buscando con usuario: $usuario<br><br>";

    // Buscar en tabla usuario primero
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE Usuario = :usuario LIMIT 1");
    $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuarioData) {
        echo "✓ Usuario encontrado en tabla 'usuario'<br>";
        echo "<pre>";
        print_r($usuarioData);
        echo "</pre>";
    } else {
        echo "✗ Usuario no encontrado en tabla 'usuario'<br>";
    }

    // Ahora buscar en tabla docente
    echo "<br><strong>Buscando en tabla 'docente':</strong><br>";

    // Primero listar todos los docentes activos
    $stmt = $pdo->prepare("SELECT * FROM docente WHERE Estado = 1 LIMIT 10");
    $stmt->execute();
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($docentes) > 0) {
        echo "Docentes activos encontrados:<br>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>";
        foreach (array_keys($docentes[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";

        foreach ($docentes as $d) {
            echo "<tr>";
            foreach ($d as $value) {
                echo "<td>" . ($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "✗ No hay docentes activos en la base de datos<br>";
    }

    // Ahora buscar el docente específico
    $docenteID = null;
    if ($usuarioData && isset($usuarioData['DocenteID']) && $usuarioData['DocenteID'] > 0) {
        $docenteID = $usuarioData['DocenteID'];
        echo "<br><strong>Buscando docente con DocenteID = $docenteID:</strong><br>";

        $stmt = $pdo->prepare("SELECT * FROM docente WHERE DocenteID = :docenteID LIMIT 1");
        $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
        $stmt->execute();
        $docenteData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($docenteData) {
            echo "✓ Docente encontrado:<br>";
            echo "<pre>";
            print_r($docenteData);
            echo "</pre>";
        } else {
            echo "✗ No se encontró el docente con ID: $docenteID<br>";
            $docenteID = null;
        }
    } else {
        echo "<br>✗ El usuario no tiene un DocenteID asociado<br>";
    }

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// 4. Verificar asignaciones del docente
echo "<h3>4. Verificar Asignaciones del Docente</h3>";

if ($docenteID) {
    try {
        // Verificar en la tabla modulo
        $stmt = $pdo->prepare("
            SELECT
                m.ModuloID,
                m.NombreModulo,
                m.Codigo,
                m.DocenteID,
                m.ProgramaID,
                m.Estado
            FROM modulo m
            WHERE m.DocenteID = :docenteID
            AND m.Estado = 1
        ");
        $stmt->bindParam(":docenteID", $docenteID, PDO::PARAM_INT);
        $stmt->execute();
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($asignaciones) > 0) {
            echo "✓ Se encontraron " . count($asignaciones) . " asignación(es):<br><br>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr>";
            foreach (array_keys($asignaciones[0]) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";

            foreach ($asignaciones as $asig) {
                echo "<tr>";
                foreach ($asig as $value) {
                    echo "<td>" . ($value ?? 'NULL') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";

            // Para cada asignación, contar estudiantes
            echo "<br><strong>Estudiantes por módulo:</strong><br>";
            foreach ($asignaciones as $asig) {
                $moduloID = $asig['ModuloID'];
                $programaID = $asig['ProgramaID'];

                $stmt2 = $pdo->prepare("
                    SELECT COUNT(*) as total
                    FROM estudiante e
                    INNER JOIN estudianteprograma ep ON e.EstudianteID = ep.EstudianteID
                    WHERE ep.ProgramaID = :programaID
                    AND ep.Estado = 'ACTIVO'
                ");
                $stmt2->bindParam(":programaID", $programaID, PDO::PARAM_INT);
                $stmt2->execute();
                $result = $stmt2->fetch(PDO::FETCH_ASSOC);

                echo "- Módulo '" . $asig['NombreModulo'] . "': " . $result['total'] . " estudiante(s)<br>";
            }

        } else {
            echo "✗ No se encontraron asignaciones para DocenteID: $docenteID<br>";
            echo "<br><strong>Nota:</strong> El docente no tiene módulos asignados.<br>";
        }

    } catch (PDOException $e) {
        echo "✗ Error al verificar asignaciones: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ No se puede verificar asignaciones sin un DocenteID válido<br>";
}

echo "<p><strong><a href='index.php?ruta=notasdocente'>Volver a Notas Docente</a></strong></p>";
?>
