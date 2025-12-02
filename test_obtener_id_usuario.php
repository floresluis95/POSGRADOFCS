<?php
/**
 * Script de prueba para verificar obtención de ID de usuario
 */

require_once 'modelos/conexion.modelo.php';

echo "=== PRUEBA DE OBTENCIÓN DE ID DE USUARIO ===\n\n";

session_start();

// Simular sesión (usa el usuario que estés usando actualmente)
$_SESSION['Usuario'] = 'luis123'; // Cambia esto por tu usuario actual
$_SESSION['Tipo'] = 'ADM';

echo "1. SESIÓN SIMULADA:\n";
echo "   \$_SESSION['Usuario'] = {$_SESSION['Usuario']}\n";
echo "   \$_SESSION['Tipo'] = {$_SESSION['Tipo']}\n\n";

try {
    $pdo = Conexion::Conectar();

    echo "2. OBTENER ID DEL USUARIO:\n";
    $stmtUser = $pdo->prepare("SELECT ID, Usuario, Tipo FROM usuario WHERE Usuario = :usuario LIMIT 1");
    $stmtUser->bindParam(":usuario", $_SESSION['Usuario'], PDO::PARAM_STR);
    $stmtUser->execute();
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($userRow) {
        echo "   ✓ Usuario encontrado en BD:\n";
        echo "     - ID: {$userRow['ID']}\n";
        echo "     - Usuario: {$userRow['Usuario']}\n";
        echo "     - Tipo: {$userRow['Tipo']}\n\n";

        $usuarioRegistroID = $userRow['ID'];

        echo "3. VERIFICAR QUE ESTE ID SE GUARDARÁ:\n";
        echo "   UsuarioRegistroID que se usará: $usuarioRegistroID\n\n";

        echo "4. VERIFICAR SI YA EXISTEN CALIFICACIONES:\n";
        $stmt = $pdo->query("
            SELECT
                c.CalificacionID,
                c.EstudianteID,
                c.Nota,
                c.UsuarioRegistroID,
                u.Usuario,
                u.Tipo
            FROM calificacion c
            LEFT JOIN usuario u ON c.UsuarioRegistroID = u.ID
            ORDER BY c.CalificacionID DESC
            LIMIT 5
        ");
        $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($calificaciones) > 0) {
            echo "   Últimas calificaciones:\n";
            foreach ($calificaciones as $cal) {
                echo "     - Calif ID: {$cal['CalificacionID']} | Est ID: {$cal['EstudianteID']} | Nota: {$cal['Nota']} | ";
                echo "RegID: " . ($cal['UsuarioRegistroID'] ?? 'NULL') . " | ";
                echo "Por: " . ($cal['Usuario'] ?? 'Sin registrar') . "\n";
            }
        } else {
            echo "   No hay calificaciones\n";
        }

    } else {
        echo "   ✗ Usuario NO encontrado en BD\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE PRUEBA ===\n";
echo "\nAhora intenta guardar calificaciones y verifica que se guarde el UsuarioRegistroID\n";
?>
