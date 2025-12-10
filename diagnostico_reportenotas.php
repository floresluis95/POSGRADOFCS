<?php
/**
 * Script de Diagnóstico para Reporte de Notas
 * Verifica la funcionalidad y datos de reportenotas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIAGNÓSTICO DE REPORTE DE NOTAS ===\n\n";

// 1. Verificar que existan los archivos necesarios
echo "1. Verificando archivos...\n";
$archivos = [
    'modelos/conexion.modelo.php',
    'modelos/reportenotas.modelo.php',
    'controladores/reportenotas.controlador.php',
    'vistas/componentes/reportenotas.php',
    'ajax/reportenotas.ajax.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "   ✓ $archivo - EXISTE\n";
    } else {
        echo "   ✗ $archivo - NO ENCONTRADO\n";
    }
}

echo "\n2. Probando conexión a base de datos...\n";
try {
    require_once 'modelos/conexion.modelo.php';
    $conexion = Conexion::Conectar();
    echo "   ✓ Conexión a base de datos EXITOSA\n";
} catch (Exception $e) {
    echo "   ✗ Error de conexión: " . $e->getMessage() . "\n";
    exit;
}

echo "\n3. Probando modelo ReporteNotasModelo...\n";
try {
    require_once 'modelos/reportenotas.modelo.php';
    echo "   ✓ Modelo cargado correctamente\n";

    // Obtener programas
    $programas = ReporteNotasModelo::ObtenerTodosProgramasConModulosModelo();
    echo "   ✓ Consulta ejecutada\n";
    echo "   → Total de programas encontrados: " . count($programas) . "\n";

    if (count($programas) > 0) {
        echo "\n   Programas encontrados:\n";
        foreach ($programas as $programa) {
            echo "   - " . $programa['NombrePrograma'] . " (" . $programa['GradoAcademico'] . ")\n";
            echo "     Módulos: " . $programa['TotalModulos'] . ", Estudiantes: " . $programa['TotalEstudiantes'] . "\n";
        }
    } else {
        echo "   ⚠ No hay programas en la base de datos\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error al obtener programas: " . $e->getMessage() . "\n";
}

echo "\n4. Verificando estructura de tablas...\n";
try {
    $stmt = $conexion->query("SHOW TABLES LIKE 'programa'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Tabla 'programa' existe\n";
    } else {
        echo "   ✗ Tabla 'programa' NO existe\n";
    }

    $stmt = $conexion->query("SHOW TABLES LIKE 'modulos'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Tabla 'modulos' existe\n";
    } else {
        echo "   ✗ Tabla 'modulos' NO existe\n";
    }

    $stmt = $conexion->query("SHOW TABLES LIKE 'estudianteprograma'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Tabla 'estudianteprograma' existe\n";
    } else {
        echo "   ✗ Tabla 'estudianteprograma' NO existe\n";
    }

    $stmt = $conexion->query("SHOW TABLES LIKE 'calificacion'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Tabla 'calificacion' existe\n";
    } else {
        echo "   ✗ Tabla 'calificacion' NO existe\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error al verificar tablas: " . $e->getMessage() . "\n";
}

echo "\n5. Contando registros en tablas clave...\n";
try {
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM programa WHERE Estado = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Programas activos: " . $result['total'] . "\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM modulos WHERE estadomodulo = 'ACTIVO'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Módulos activos: " . $result['total'] . "\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM estudianteprograma WHERE Estado = 'ACTIVO'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Estudiantes en programas: " . $result['total'] . "\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM calificacion");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Calificaciones registradas: " . $result['total'] . "\n";
} catch (Exception $e) {
    echo "   ✗ Error al contar registros: " . $e->getMessage() . "\n";
}

echo "\n6. Probando controlador...\n";
try {
    require_once 'controladores/funciones.controlador.php';
    require_once 'controladores/reportenotas.controlador.php';
    echo "   ✓ Controlador cargado correctamente\n";
} catch (Exception $e) {
    echo "   ✗ Error al cargar controlador: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
