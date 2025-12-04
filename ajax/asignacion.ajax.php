<?php
/**
 * AJAX para asignación de docentes a módulos
 */

session_start();

// Verificar sesión
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

require_once '../modelos/conexion.modelo.php';

// Verificar acción
if (!isset($_POST['accion'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;
}

$accion = $_POST['accion'];

try {
    switch ($accion) {
        case 'asignar':
            if (!isset($_POST['moduloID']) || !isset($_POST['docenteID'])) {
                echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
                exit;
            }

            $moduloID = (int)$_POST['moduloID'];
            $docenteID = (int)$_POST['docenteID'];

            // Validar que el módulo exista
            $stmtModulo = Conexion::Conectar()->prepare("SELECT Idmodulo FROM modulos WHERE Idmodulo = :moduloID AND estadomodulo = 'ACTIVO'");
            $stmtModulo->bindParam(':moduloID', $moduloID, PDO::PARAM_INT);
            $stmtModulo->execute();

            if ($stmtModulo->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Módulo no encontrado']);
                exit;
            }

            // Validar que el docente exista
            $stmtDocente = Conexion::Conectar()->prepare("SELECT DocenteID FROM docente WHERE DocenteID = :docenteID AND Estado = 1");
            $stmtDocente->bindParam(':docenteID', $docenteID, PDO::PARAM_INT);
            $stmtDocente->execute();

            if ($stmtDocente->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Docente no encontrado']);
                exit;
            }

            // Asignar docente al módulo
            $stmtUpdate = Conexion::Conectar()->prepare("UPDATE modulos SET DocenteID = :docenteID WHERE Idmodulo = :moduloID");
            $stmtUpdate->bindParam(':docenteID', $docenteID, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':moduloID', $moduloID, PDO::PARAM_INT);

            if ($stmtUpdate->execute()) {
                echo json_encode(['success' => true, 'message' => 'Docente asignado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la asignación']);
            }
            break;

        case 'desasignar':
            if (!isset($_POST['moduloID'])) {
                echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
                exit;
            }

            $moduloID = (int)$_POST['moduloID'];

            // Remover asignación
            $stmtUpdate = Conexion::Conectar()->prepare("UPDATE modulos SET DocenteID = NULL WHERE Idmodulo = :moduloID");
            $stmtUpdate->bindParam(':moduloID', $moduloID, PDO::PARAM_INT);

            if ($stmtUpdate->execute()) {
                echo json_encode(['success' => true, 'message' => 'Asignación removida correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al remover la asignación']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
            break;
    }
} catch (PDOException $e) {
    error_log('Error en asignacion.ajax.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('Error general en asignacion.ajax.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>
