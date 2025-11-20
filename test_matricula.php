<?php
// Test de carga de archivos de matrícula
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test del Sistema de Matrícula</h2>";

// Incluir archivos necesarios
try {
    require_once 'modelos/conexion.modelo.php';
    echo "✓ Conexión modelo cargado<br>";

    require_once 'modelos/matricula.modelo.php';
    echo "✓ Matrícula modelo cargado<br>";

    require_once 'controladores/matricula.controlador.php';
    echo "✓ Matrícula controlador cargado<br>";

    echo "<hr>";
    echo "<h3>Verificando clases:</h3>";

    if (class_exists('MatriculaModelos')) {
        echo "✓ Clase MatriculaModelos existe<br>";

        // Listar métodos
        $methods = get_class_methods('MatriculaModelos');
        echo "<strong>Métodos disponibles:</strong><br>";
        foreach ($methods as $method) {
            echo "&nbsp;&nbsp;- " . $method . "<br>";
        }
    } else {
        echo "✗ Clase MatriculaModelos NO existe<br>";
    }

    echo "<br>";

    if (class_exists('MatriculaControladores')) {
        echo "✓ Clase MatriculaControladores existe<br>";

        // Listar métodos
        $methods = get_class_methods('MatriculaControladores');
        echo "<strong>Métodos disponibles:</strong><br>";
        foreach ($methods as $method) {
            echo "&nbsp;&nbsp;- " . $method . "<br>";
        }
    } else {
        echo "✗ Clase MatriculaControladores NO existe<br>";
    }

    echo "<hr>";
    echo "<h3>Test de Conexión a Base de Datos:</h3>";

    try {
        $conexion = Conexion::Conectar();
        echo "✓ Conexión a BD exitosa<br>";

        // Verificar tabla inscripcion
        $stmt = $conexion->query("SHOW TABLES LIKE 'inscripcion'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Tabla 'inscripcion' existe<br>";

            // Mostrar estructura
            $stmt = $conexion->query("DESCRIBE inscripcion");
            echo "<strong>Estructura de la tabla:</strong><br>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "✗ Tabla 'inscripcion' NO existe<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error de conexión: " . $e->getMessage() . "<br>";
    }

    echo "<hr>";
    echo "<h3 style='color: green;'>✓ Todos los archivos se cargaron correctamente</h3>";
    echo "<p><a href='index.php?ruta=inscripcion'>Ir a la página de inscripción</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>✗ Error al cargar archivos:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
