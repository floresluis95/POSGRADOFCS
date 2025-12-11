<?php
/**
 * Diagnóstico de ReporteModulos
 */

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/reportemodulos.modelo.php';

echo "<h2>Diagnóstico de Reporte Módulos</h2>";

try {
    $conexion = Conexion::Conectar();
    echo "<p style='color: green;'>✓ Conexión a base de datos exitosa</p>";

    // Verificar programas activos
    $stmt = $conexion->query("SELECT COUNT(*) as total FROM programa WHERE Estado = 'ACTIVO'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total de programas ACTIVOS:</strong> {$result['total']}</p>";

    // Listar todos los programas
    $stmt = $conexion->query("SELECT ProgramaID, NombrePrograma, Codigo, GradoAcademico, Estado FROM programa ORDER BY ProgramaID");
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Todos los Programas en la Base de Datos:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Grado</th><th>Estado</th></tr>";
    foreach ($programas as $p) {
        $color = $p['Estado'] === 'ACTIVO' ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$p['ProgramaID']}</td>";
        echo "<td>{$p['Codigo']}</td>";
        echo "<td>{$p['NombrePrograma']}</td>";
        echo "<td>{$p['GradoAcademico']}</td>";
        echo "<td style='color: $color; font-weight: bold;'>{$p['Estado']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Probar la función del modelo
    echo "<hr><h3>Resultado de ObtenerConteoModulosPorProgramaModelo():</h3>";
    $resultado = ReporteModulosModelo::ObtenerConteoModulosPorProgramaModelo();
    echo "<pre>";
    print_r($resultado);
    echo "</pre>";

    echo "<p><strong>Total de programas retornados por el modelo:</strong> " . count($resultado) . "</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
