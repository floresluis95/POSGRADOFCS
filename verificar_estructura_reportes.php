<?php
require_once 'modelos/conexion.modelo.php';

echo "<h2>Verificación de Estructura para Reportes de Notas</h2>";

try {
    $conexion = Conexion::Conectar();

    // 1. Verificar tabla programa
    echo "<h3>1. Tabla PROGRAMA</h3>";
    $stmt = $conexion->prepare("DESCRIBE programa");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'><tr><th>Campo</th><th>Tipo</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table>";

    // Contar programas
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM programa");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<p><strong>Total programas:</strong> " . $result['total'] . "</p>";

    // 2. Verificar tabla modulo/modulos
    echo "<h3>2. Tabla MODULO/MODULOS</h3>";
    try {
        $stmt = $conexion->prepare("DESCRIBE modulo");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p><strong>Tabla: modulo (singular)</strong></p>";
        echo "<table border='1' cellpadding='5'><tr><th>Campo</th><th>Tipo</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p style='color:orange;'>Tabla 'modulo' no existe</p>";
    }

    try {
        $stmt = $conexion->prepare("DESCRIBE modulos");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p><strong>Tabla: modulos (plural)</strong></p>";
        echo "<table border='1' cellpadding='5'><tr><th>Campo</th><th>Tipo</th></tr>";
        foreach ($columnas as $col) {
            echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
        }
        echo "</table>";

        // Contar módulos
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM modulos");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p><strong>Total módulos:</strong> " . $result['total'] . "</p>";
    } catch (Exception $e) {
        echo "<p style='color:orange;'>Tabla 'modulos' no existe</p>";
    }

    // 3. Verificar tabla calificacion
    echo "<h3>3. Tabla CALIFICACION</h3>";
    $stmt = $conexion->prepare("DESCRIBE calificacion");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'><tr><th>Campo</th><th>Tipo</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table>";

    // Contar calificaciones
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM calificacion");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<p><strong>Total calificaciones:</strong> " . $result['total'] . "</p>";

    // 4. Verificar tabla docente
    echo "<h3>4. Tabla DOCENTE</h3>";
    $stmt = $conexion->prepare("DESCRIBE docente");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' cellpadding='5'><tr><th>Campo</th><th>Tipo</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr><td>" . $col['Field'] . "</td><td>" . $col['Type'] . "</td></tr>";
    }
    echo "</table>";

    // 5. Muestra de datos
    echo "<h3>5. Muestra de Datos</h3>";

    echo "<h4>Programas activos:</h4>";
    $stmt = $conexion->prepare("SELECT * FROM programa LIMIT 5");
    $stmt->execute();
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($programas) > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nombre</th><th>Grado</th></tr>";
        foreach ($programas as $p) {
            echo "<tr><td>" . $p['ProgramaID'] . "</td><td>" . $p['NombrePrograma'] . "</td><td>" . $p['GradoAcademico'] . "</td></tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
