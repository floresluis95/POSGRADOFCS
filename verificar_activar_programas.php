<?php
/**
 * Script para verificar y activar programas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'modelos/conexion.modelo.php';

echo "=== VERIFICACIÓN Y ACTIVACIÓN DE PROGRAMAS ===\n\n";

try {
    $conexion = Conexion::Conectar();

    // Verificar todos los programas
    echo "1. Verificando TODOS los programas en la base de datos...\n";
    $stmt = $conexion->query("SELECT ProgramaID, NombrePrograma, GradoAcademico, Estado FROM programa");
    $programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($programas) > 0) {
        echo "   → Total de programas encontrados: " . count($programas) . "\n\n";

        foreach ($programas as $programa) {
            $estadoTexto = $programa['Estado'] == 1 ? 'ACTIVO' : 'INACTIVO';
            echo "   - ID: " . $programa['ProgramaID'] . "\n";
            echo "     Nombre: " . $programa['NombrePrograma'] . "\n";
            echo "     Grado: " . $programa['GradoAcademico'] . "\n";
            echo "     Estado actual: " . $programa['Estado'] . " ($estadoTexto)\n\n";
        }

        // Contar activos vs inactivos
        $activos = 0;
        $inactivos = 0;
        foreach ($programas as $programa) {
            if ($programa['Estado'] == 1) {
                $activos++;
            } else {
                $inactivos++;
            }
        }

        echo "\n2. Resumen:\n";
        echo "   → Programas ACTIVOS (Estado=1): $activos\n";
        echo "   → Programas INACTIVOS (Estado≠1): $inactivos\n\n";

        if ($inactivos > 0) {
            echo "3. ACTIVANDO todos los programas...\n";
            $stmt = $conexion->prepare("UPDATE programa SET Estado = 1");
            $stmt->execute();
            $afectados = $stmt->rowCount();
            echo "   ✓ Se actualizaron $afectados registros\n";
            echo "   ✓ Todos los programas ahora están ACTIVOS\n\n";

            // Verificar resultado
            $stmt = $conexion->query("SELECT COUNT(*) as total FROM programa WHERE Estado = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "4. Verificación final:\n";
            echo "   ✓ Programas activos: " . $result['total'] . "\n";
        } else {
            echo "3. ✓ Todos los programas ya están activos. No se requiere acción.\n";
        }

    } else {
        echo "   ⚠ NO HAY PROGRAMAS EN LA BASE DE DATOS\n";
        echo "   Necesitas crear programas primero en el módulo de programas.\n";
    }

} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN ===\n";
?>
