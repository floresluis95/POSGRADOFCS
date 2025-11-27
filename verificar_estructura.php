<?php
/**
 * Script para verificar estructura de tablas
 */

require_once 'modelos/conexion.modelo.php';

echo "=== Verificación de Estructura de Tablas ===\n\n";

try {
    $pdo = Conexion::Conectar();

    // Ver columnas de la tabla estudiante
    echo "1. Columnas de la tabla 'estudiante':\n";
    $stmt = $pdo->query("DESCRIBE estudiante");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columnas as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "\n";

    // Ver qué tablas existen relacionadas con módulos
    echo "2. Tablas relacionadas con módulos/pagos:\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '%modulo%'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tablas as $tabla) {
        echo "  - " . $tabla . "\n";
    }
    echo "\n";

    // Ver tablas relacionadas con pagos
    echo "3. Tablas relacionadas con pagos:\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '%pago%'");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tablas as $tabla) {
        echo "  - " . $tabla . "\n";
    }
    echo "\n";

    // Ver columnas de pagomodulo si existe
    echo "4. Columnas de la tabla 'pagomodulo':\n";
    $stmt = $pdo->query("DESCRIBE pagomodulo");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columnas as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Verificar tabla programa

// Verificar si existe tabla modulo
echo "<h2>Tabla MODULO</h2>";
$stmt = $pdo->query("SHOW TABLES LIKE 'modulo'");
if ($stmt->rowCount() > 0) {
    echo "<p style='color: green;'>✅ Tabla modulo EXISTE</p>";

    $stmt = $pdo->query("DESCRIBE modulo");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Contar módulos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM modulo");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>Total de módulos en BD:</strong> $total</p>";

} else {
    echo "<p style='color: red;'>❌ Tabla modulo NO EXISTE</p>";
}

// Mostrar programas
echo "<h2>Programas Registrados</h2>";
$stmt = $pdo->query("SELECT * FROM programa LIMIT 5");
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($programas)) {
    echo "<p><strong>Columnas de la tabla programa:</strong> " . implode(", ", array_keys($programas[0])) . "</p>";

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Código</th>";

    // Verificar si existe columna nmodulos
    if (isset($programas[0]['nmodulos'])) {
        echo "<th>N° Módulos</th>";
    }

    echo "</tr>";

    foreach ($programas as $prog) {
        echo "<tr>";
        echo "<td>{$prog['ProgramaID']}</td>";
        echo "<td>{$prog['NombrePrograma']}</td>";
        echo "<td>{$prog['Codigo']}</td>";

        if (isset($prog['nmodulos'])) {
            echo "<td><strong>{$prog['nmodulos']}</strong></td>";
        }

        echo "</tr>";
    }
    echo "</table>";
}

// Verificar inscripciones
echo "<h2>Inscripciones de Estudiantes</h2>";
$stmt = $pdo->query("SELECT * FROM estudianteprograma LIMIT 5");
$inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($inscripciones)) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>idInscripcion</th><th>EstudianteID</th><th>ProgramaID</th><th>Estado</th></tr>";
    foreach ($inscripciones as $insc) {
        echo "<tr>";
        echo "<td>{$insc['idInscripcion']}</td>";
        echo "<td>{$insc['EstudianteID']}</td>";
        echo "<td>{$insc['ProgramaID']}</td>";
        echo "<td>{$insc['Estado']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>
