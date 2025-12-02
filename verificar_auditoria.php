<?php
/**
 * Script para verificar el funcionamiento de la auditoría de calificaciones
 */

require_once 'modelos/conexion.modelo.php';

echo "=== VERIFICACIÓN DE AUDITORÍA DE CALIFICACIONES ===\n\n";

try {
    $pdo = Conexion::Conectar();

    // 1. Verificar estructura de tabla calificacion
    echo "1. ESTRUCTURA DE TABLA CALIFICACION:\n";
    $stmt = $pdo->query("DESCRIBE calificacion");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tieneUsuarioRegistroID = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'UsuarioRegistroID') {
            $tieneUsuarioRegistroID = true;
            echo "   ✓ Campo UsuarioRegistroID encontrado: {$column['Type']}, Null: {$column['Null']}\n";
        }
    }

    if (!$tieneUsuarioRegistroID) {
        echo "   ✗ Campo UsuarioRegistroID NO EXISTE - DEBE AGREGARSE\n";
    }
    echo "\n";

    // 2. Verificar datos actuales en calificacion
    echo "2. VERIFICAR DATOS DE CALIFICACIONES:\n";
    $stmt = $pdo->query("
        SELECT
            CalificacionID,
            EstudianteID,
            ProgramaId,
            Idmodulo,
            Nota,
            estado,
            FechaRegistro,
            UsuarioRegistroID
        FROM calificacion
        ORDER BY CalificacionID DESC
        LIMIT 5
    ");
    $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($calificaciones) > 0) {
        echo "   Últimas 5 calificaciones:\n";
        foreach ($calificaciones as $cal) {
            echo "     ID: {$cal['CalificacionID']} | Estudiante: {$cal['EstudianteID']} | Nota: {$cal['Nota']} | ";
            echo "UsuarioRegistroID: " . ($cal['UsuarioRegistroID'] ?? 'NULL') . " | Fecha: {$cal['FechaRegistro']}\n";
        }
    } else {
        echo "   No hay calificaciones registradas\n";
    }
    echo "\n";

    // 3. Verificar sesión actual
    echo "3. VERIFICAR VARIABLES DE SESIÓN:\n";
    session_start();

    echo "   Variables de sesión disponibles:\n";
    foreach ($_SESSION as $key => $value) {
        if (!is_array($value) && !is_object($value)) {
            echo "     - \$_SESSION['$key'] = $value\n";
        }
    }

    if (isset($_SESSION['ID'])) {
        echo "\n   ✓ \$_SESSION['ID'] existe: {$_SESSION['ID']}\n";

        // Verificar que el usuario existe
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE ID = :id");
        $stmt->bindParam(':id', $_SESSION['ID']);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            echo "   ✓ Usuario encontrado en BD:\n";
            echo "     - ID: {$usuario['ID']}\n";
            echo "     - Usuario: {$usuario['Usuario']}\n";
            echo "     - Tipo: {$usuario['Tipo']}\n";
            echo "     - DocenteID: " . ($usuario['DocenteID'] ?? 'NULL') . "\n";
            echo "     - EstudianteID: " . ($usuario['EstudianteID'] ?? 'NULL') . "\n";
        } else {
            echo "   ✗ Usuario NO encontrado en BD con ID: {$_SESSION['ID']}\n";
        }
    } else {
        echo "\n   ✗ \$_SESSION['ID'] NO EXISTE\n";
        echo "   Intentando con otras variables de sesión:\n";

        if (isset($_SESSION['IdUsuario'])) {
            echo "     - Encontrado \$_SESSION['IdUsuario']: {$_SESSION['IdUsuario']}\n";
        }
        if (isset($_SESSION['Usuario'])) {
            echo "     - Encontrado \$_SESSION['Usuario']: {$_SESSION['Usuario']}\n";
        }
    }
    echo "\n";

    // 4. Verificar query de INSERT
    echo "4. SIMULAR INSERT DE CALIFICACIÓN:\n";
    if (isset($_SESSION['ID'])) {
        $usuarioID = $_SESSION['ID'];
        echo "   Se usaría UsuarioRegistroID: $usuarioID\n";
    } else {
        echo "   ✗ No se puede simular - no hay ID en sesión\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
?>
