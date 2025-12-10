<?php
/**
 * Script de Diagnóstico para Reporte de Calificaciones de Docentes
 * Verifica que los docentes tengan módulos asignados y calificaciones
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';
require_once 'modelos/calificacion.modelo.php';

echo "=== DIAGNÓSTICO DE REPORTE DE CALIFICACIONES PARA DOCENTES ===\n\n";

try {
    $conexion = Conexion::Conectar();

    // 1. Listar todos los docentes activos
    echo "1. DOCENTES ACTIVOS EN EL SISTEMA:\n";
    $stmt = $conexion->query("
        SELECT
            DocenteID,
            Ci,
            Complemento,
            Exp,
            Nombre,
            Apaterno,
            Amaterno,
            Especialidad,
            Estado
        FROM docente
        WHERE Estado = 1
        ORDER BY Apaterno, Amaterno, Nombre
    ");
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($docentes) > 0) {
        echo "   → Total de docentes activos: " . count($docentes) . "\n\n";

        foreach ($docentes as $docente) {
            $nombreCompleto = $docente['Nombre'] . ' ' . $docente['Apaterno'] . ' ' . $docente['Amaterno'];
            $ci = $docente['Ci'];
            if (!empty($docente['Complemento'])) {
                $ci .= '-' . $docente['Complemento'];
            }
            $ci .= ' ' . $docente['Exp'];

            echo "   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "   Docente ID: " . $docente['DocenteID'] . "\n";
            echo "   Nombre: " . $nombreCompleto . "\n";
            echo "   CI: " . $ci . " (Usuario de login)\n";
            echo "   Especialidad: " . ($docente['Especialidad'] ?: 'No especificada') . "\n\n";

            // Obtener módulos asignados
            $asignaciones = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docente['DocenteID']);

            if (count($asignaciones) > 0) {
                echo "   ✅ Módulos asignados: " . count($asignaciones) . "\n\n";

                foreach ($asignaciones as $asig) {
                    echo "      • Módulo: " . $asig['nombremodulo'] . " (" . $asig['codigomodulo'] . ")\n";
                    echo "        Programa: " . $asig['NombrePrograma'] . " (" . $asig['GradoAcademico'] . ")\n";
                    echo "        Estudiantes: " . $asig['TotalEstudiantes'] . " | Calificados: " . $asig['TotalCalificados'] . "\n";

                    if ($asig['TotalCalificados'] > 0) {
                        echo "        🖨️  PUEDE IMPRIMIR PLANILLA\n";
                    } else {
                        echo "        ⚠️  Sin calificaciones - NO puede imprimir\n";
                    }
                    echo "\n";
                }
            } else {
                echo "   ❌ NO tiene módulos asignados\n";
                echo "      → El botón 'IMPRIMIR PLANILLA' NO aparecerá\n";
                echo "      → El botón 'IMPRIMIR REPORTE COMPLETO' NO generará datos\n\n";
            }
        }
    } else {
        echo "   ⚠️  NO HAY DOCENTES ACTIVOS EN EL SISTEMA\n\n";
    }

    // 2. Verificar módulos sin docente asignado
    echo "\n2. MÓDULOS SIN DOCENTE ASIGNADO:\n";
    $stmt = $conexion->query("
        SELECT
            m.Idmodulo,
            m.nombremodulo,
            m.codigomodulo,
            p.NombrePrograma
        FROM modulos m
        LEFT JOIN programa p ON m.ProgramaId = p.ProgramaID
        WHERE m.DocenteID IS NULL
        AND m.estadomodulo = 'ACTIVO'
    ");
    $modulosSinDocente = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($modulosSinDocente) > 0) {
        echo "   ⚠️  Hay " . count($modulosSinDocente) . " módulos sin docente asignado:\n\n";
        foreach ($modulosSinDocente as $mod) {
            echo "   - " . $mod['nombremodulo'] . " (" . $mod['codigomodulo'] . ")\n";
            echo "     Programa: " . $mod['NombrePrograma'] . "\n\n";
        }
    } else {
        echo "   ✅ Todos los módulos tienen docente asignado\n\n";
    }

    // 3. Resumen general
    echo "\n3. RESUMEN GENERAL:\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM docente WHERE Estado = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Docentes activos: " . $result['total'] . "\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM modulos WHERE estadomodulo = 'ACTIVO' AND DocenteID IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Módulos con docente: " . $result['total'] . "\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM modulos WHERE estadomodulo = 'ACTIVO' AND DocenteID IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Módulos sin docente: " . $result['total'] . "\n";

    $stmt = $conexion->query("SELECT COUNT(*) as total FROM calificacion");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   → Total calificaciones: " . $result['total'] . "\n";

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>
