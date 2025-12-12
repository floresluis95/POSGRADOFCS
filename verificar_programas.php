<?php
/**
 * Verificar programas y su estado en la base de datos
 */

require_once 'modelos/conexion.modelo.php';

try {
    $conexion = Conexion::Conectar();

    echo "===== VERIFICACIÓN DE PROGRAMAS EN LA BASE DE DATOS =====\n\n";

    // Consulta 1: Todos los programas
    echo "1. TODOS LOS PROGRAMAS (sin filtro):\n";
    echo str_repeat("-", 80) . "\n";
    $stmt1 = $conexion->prepare("SELECT ProgramaID, NombrePrograma, Codigo, Estado FROM programa ORDER BY ProgramaID");
    $stmt1->execute();
    $todosProgramas = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    foreach ($todosProgramas as $prog) {
        $estado = $prog['Estado'] == 1 ? 'ACTIVO' : 'INACTIVO';
        echo sprintf("ID: %-3s | %-50s | Código: %-15s | Estado: %s\n",
            $prog['ProgramaID'],
            $prog['NombrePrograma'],
            $prog['Codigo'],
            $estado
        );
    }
    echo "\nTotal de programas: " . count($todosProgramas) . "\n\n";

    // Consulta 2: Solo programas activos
    echo "2. PROGRAMAS ACTIVOS (Estado = 1):\n";
    echo str_repeat("-", 80) . "\n";
    $stmt2 = $conexion->prepare("SELECT ProgramaID, NombrePrograma, Codigo, Estado FROM programa WHERE Estado = 1 ORDER BY ProgramaID");
    $stmt2->execute();
    $programasActivos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programasActivos as $prog) {
        echo sprintf("ID: %-3s | %-50s | Código: %-15s\n",
            $prog['ProgramaID'],
            $prog['NombrePrograma'],
            $prog['Codigo']
        );
    }
    echo "\nTotal de programas activos: " . count($programasActivos) . "\n\n";

    // Consulta 3: Programas con módulos
    echo "3. PROGRAMAS QUE TIENEN MÓDULOS REGISTRADOS:\n";
    echo str_repeat("-", 80) . "\n";
    $stmt3 = $conexion->prepare("
        SELECT p.ProgramaID, p.NombrePrograma, p.Codigo, p.Estado, COUNT(m.idmodulo) as TotalModulos
        FROM programa p
        LEFT JOIN modulos m ON p.ProgramaID = m.ProgramaId AND m.estadomodulo = 'ACTIVO'
        GROUP BY p.ProgramaID
        ORDER BY p.ProgramaID
    ");
    $stmt3->execute();
    $programasConModulos = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    foreach ($programasConModulos as $prog) {
        $estado = $prog['Estado'] == 1 ? 'ACTIVO' : 'INACTIVO';
        echo sprintf("ID: %-3s | %-40s | Módulos: %-3s | Estado: %s\n",
            $prog['ProgramaID'],
            $prog['NombrePrograma'],
            $prog['TotalModulos'],
            $estado
        );
    }

    echo "\n\n===== RESUMEN =====\n";
    echo "Total programas en BD: " . count($todosProgramas) . "\n";
    echo "Programas activos: " . count($programasActivos) . "\n";
    echo "Programas inactivos: " . (count($todosProgramas) - count($programasActivos)) . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
