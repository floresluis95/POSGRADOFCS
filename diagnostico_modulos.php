<?php
/**
 * Script de diagnóstico para el módulo de módulos
 */

require_once "modelos/modulo.modelo.php";
require_once "modelos/conexion.modelo.php";

echo "<h2>DIAGNÓSTICO DEL SISTEMA DE MÓDULOS</h2>";
echo "<hr>";

// 1. Verificar docentes activos
echo "<h3>1. Docentes Activos:</h3>";
try {
    $docentes = ModuloModelo::ListarDocentesActivosModelo();
    echo "<p><strong>Total de docentes activos:</strong> " . count($docentes) . "</p>";
    if (count($docentes) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre Completo</th><th>CI</th><th>Especialidad</th></tr>";
        foreach ($docentes as $doc) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($doc['DocenteID']) . "</td>";
            echo "<td>" . htmlspecialchars($doc['NombreCompleto']) . "</td>";
            echo "<td>" . htmlspecialchars($doc['Ci']) . "</td>";
            echo "<td>" . htmlspecialchars($doc['Especialidad'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>⚠ NO HAY DOCENTES ACTIVOS</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 2. Verificar programas
echo "<h3>2. Programas Disponibles:</h3>";
try {
    require_once "modelos/programa.modelo.php";
    $programas = ProgramasModelos::ListaProgramaModelo();
    echo "<p><strong>Total de programas:</strong> " . count($programas) . "</p>";
    if (count($programas) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Código</th><th>Grado</th><th>Módulos</th></tr>";
        foreach ($programas as $prog) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($prog['ProgramaID']) . "</td>";
            echo "<td>" . htmlspecialchars($prog['NombrePrograma']) . "</td>";
            echo "<td>" . htmlspecialchars($prog['Codigo']) . "</td>";
            echo "<td>" . htmlspecialchars($prog['GradoAcademico']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($prog['Modulos']) . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>⚠ NO HAY PROGRAMAS REGISTRADOS</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 3. Verificar módulos registrados por programa
echo "<h3>3. Módulos Registrados por Programa:</h3>";
try {
    $modulos = ModuloModelo::ListarModulosPorProgramaModelo();
    echo "<p><strong>Total de módulos registrados:</strong> " . count($modulos) . "</p>";
    if (count($modulos) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Programa</th><th>Docente</th><th>Estado</th></tr>";
        foreach ($modulos as $mod) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($mod['Idmodulo']) . "</td>";
            echo "<td>" . htmlspecialchars($mod['codigomodulo']) . "</td>";
            echo "<td>" . htmlspecialchars($mod['nombremodulo']) . "</td>";
            echo "<td>" . htmlspecialchars($mod['NombrePrograma']) . "</td>";
            echo "<td>" . htmlspecialchars($mod['NombreDocente'] ?? 'Sin asignar') . "</td>";
            echo "<td>" . htmlspecialchars($mod['estadomodulo']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>ℹ NO HAY MÓDULOS REGISTRADOS AÚN</p>";
        echo "<p>Esto es normal si acabas de instalar el sistema. Usa la interfaz para registrar módulos.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 4. Verificar estructura de la tabla modulos
echo "<h3>4. Estructura de la Tabla 'modulos':</h3>";
try {
    $pdo = Conexion::Conectar();
    $stmt = $pdo->query("DESCRIBE modulos");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php?ruta=modulos'>← Volver al sistema de módulos</a></p>";
?>
