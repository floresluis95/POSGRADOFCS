<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Crear Tablas de Módulos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #1976D2; border-bottom: 3px solid #1976D2; padding-bottom: 10px; }
        .success { background: #e8f5e9; border-left: 5px solid #4CAF50; padding: 15px; margin: 10px 0; }
        .error { background: #ffebee; border-left: 5px solid #f44336; padding: 15px; margin: 10px 0; }
        .info { background: #e3f2fd; border-left: 5px solid #2196F3; padding: 15px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        .btn { padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px; }
    </style>
</head>
<body>";

echo "<h1>🛠️ Crear/Verificar Tablas de Módulos</h1>";

try {
    $pdo = Conexion::Conectar();

    // 1. Verificar si existe tabla modulo
    echo "<div class='info'><h2>1. Verificando tabla 'modulo'</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'modulo'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ La tabla 'modulo' ya existe.</p>";
    } else {
        echo "<p>⚠️ La tabla 'modulo' no existe. Creando...</p>";

        $sqlModulo = "CREATE TABLE `modulo` (
          `ModuloID` int(11) NOT NULL AUTO_INCREMENT,
          `ProgramaID` int(11) NOT NULL,
          `NombreModulo` varchar(255) NOT NULL,
          `Codigo` varchar(50) NOT NULL,
          `Descripcion` text DEFAULT NULL,
          `Creditos` int(11) DEFAULT 0,
          `HorasTeoricas` int(11) DEFAULT 0,
          `HorasPracticas` int(11) DEFAULT 0,
          `Costo` decimal(10,2) DEFAULT 0.00,
          `Estado` tinyint(1) DEFAULT 1,
          `FechaCreacion` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`ModuloID`),
          KEY `ProgramaID` (`ProgramaID`),
          CONSTRAINT `fk_modulo_programa` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $pdo->exec($sqlModulo);
        echo "<p class='success'>✓ Tabla 'modulo' creada exitosamente.</p>";
    }
    echo "</div>";

    // 2. Verificar si existe tabla estudiantemodulo
    echo "<div class='info'><h2>2. Verificando tabla 'estudiantemodulo'</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'estudiantemodulo'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ La tabla 'estudiantemodulo' ya existe.</p>";
    } else {
        echo "<p>⚠️ La tabla 'estudiantemodulo' no existe. Creando...</p>";

        $sqlEstudianteModulo = "CREATE TABLE `estudiantemodulo` (
          `idEstudianteModulo` int(11) NOT NULL AUTO_INCREMENT,
          `EstudianteID` int(11) NOT NULL,
          `ModuloID` int(11) NOT NULL,
          `costomodulo` decimal(10,2) NOT NULL,
          `nvauchermodulo` varchar(100) NOT NULL,
          `FechaInscripcion` date NOT NULL,
          `Estado` enum('ACTIVO','INACTIVO','RETIRADO') DEFAULT 'ACTIVO',
          `FechaRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`idEstudianteModulo`),
          KEY `EstudianteID` (`EstudianteID`),
          KEY `ModuloID` (`ModuloID`),
          CONSTRAINT `fk_estudiantemodulo_estudiante` FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante` (`EstudianteID`) ON DELETE CASCADE ON UPDATE CASCADE,
          CONSTRAINT `fk_estudiantemodulo_modulo` FOREIGN KEY (`ModuloID`) REFERENCES `modulo` (`ModuloID`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $pdo->exec($sqlEstudianteModulo);
        echo "<p class='success'>✓ Tabla 'estudiantemodulo' creada exitosamente.</p>";

        // Crear índices
        try {
            $pdo->exec("CREATE INDEX idx_estudiantemodulo_estado ON estudiantemodulo(Estado)");
            $pdo->exec("CREATE INDEX idx_estudiantemodulo_fecha ON estudiantemodulo(FechaInscripcion)");
            echo "<p class='success'>✓ Índices creados exitosamente.</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Los índices ya existen o no se pudieron crear.</p>";
        }
    }
    echo "</div>";

    // 3. Mostrar estructura de las tablas
    echo "<div class='success'><h2>3. Estructura de las Tablas</h2>";

    echo "<h3>Tabla: modulo</h3>";
    $stmt = $pdo->query("DESCRIBE modulo");
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h3>Tabla: estudiantemodulo</h3>";
    $stmt = $pdo->query("DESCRIBE estudiantemodulo");
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // 4. Verificar registros
    echo "<div class='info'><h2>4. Resumen de Datos</h2>";

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM modulo");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>📚 <strong>Módulos registrados:</strong> " . $row['total'] . "</p>";

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM estudiantemodulo");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>📝 <strong>Inscripciones a módulos:</strong> " . $row['total'] . "</p>";

    echo "</div>";

    echo "<div class='success'>";
    echo "<h2>✅ PROCESO COMPLETADO EXITOSAMENTE</h2>";
    echo "<p>Las tablas necesarias para el sistema de inscripción a módulos han sido creadas/verificadas correctamente.</p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ ERROR</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align: center;'>";
echo "<a href='index.php?ruta=matriculados' class='btn'>📋 Ver Estudiantes Matriculados</a>";
echo "<a href='index.php?ruta=inscripcion' class='btn'>➕ Nueva Inscripción</a>";
echo "</p>";

echo "</body></html>";
?>
