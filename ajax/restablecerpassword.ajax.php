<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX para restablecer contraseñas desde la administración.
 * El usuario que olvidó su contraseña la solicita al administrador, quien
 * genera una nueva desde el detalle del estudiante/docente, o la escribe a mano
 * desde la lista de usuarios del personal (ADM, SEC, AUX).
 *
 * Permisos:
 *  - Estudiantes y docentes: ADM, SEC y AUX (son quienes gestionan esas páginas).
 *  - Personal administrativo: solo ADM (evita que una secretaria restablezca
 *    la contraseña del administrador y pueda ingresar con su cuenta).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/usuario.modelo.php';
require_once __DIR__ . '/../controladores/usuario.controlador.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
    exit;
}

$tipoSesion = $_SESSION['Tipo'] ?? '';
$accion = $_POST['accion'] ?? '';
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'mensaje' => 'El identificador es requerido']);
    exit;
}

try {
    switch ($accion) {
        case 'estudiante':
            if (!in_array($tipoSesion, ['ADM', 'SEC', 'AUX'])) {
                echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para esta acción']);
                exit;
            }
            if (!UsuarioModelos::VerificarUsuarioEstudianteModelo($id)) {
                echo json_encode(['success' => false, 'mensaje' => 'Este estudiante no tiene un usuario asignado']);
                exit;
            }
            $passwordTexto = UsuarioControladores::GenerarPasswordEstudiante();
            $passwordHash = password_hash($passwordTexto, PASSWORD_BCRYPT, ['cost' => 12]);
            $resultado = UsuarioModelos::RestablecerPasswordEstudianteModelo($id, $passwordHash);
            break;

        case 'docente':
            if (!in_array($tipoSesion, ['ADM', 'SEC', 'AUX'])) {
                echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para esta acción']);
                exit;
            }
            if (!UsuarioModelos::VerificarUsuarioDocenteModelo($id)) {
                echo json_encode(['success' => false, 'mensaje' => 'Este docente no tiene un usuario asignado']);
                exit;
            }
            $passwordTexto = UsuarioControladores::GenerarPasswordCompleja();
            $passwordHash = password_hash($passwordTexto, PASSWORD_BCRYPT, ['cost' => 12]);
            $resultado = UsuarioModelos::RestablecerPasswordDocenteModelo($id, $passwordHash);
            break;

        case 'personal':
            if ($tipoSesion !== 'ADM') {
                echo json_encode(['success' => false, 'mensaje' => 'Solo el administrador puede restablecer contraseñas del personal']);
                exit;
            }
            if (!UsuarioModelos::VerificarUsuarioPersonalModelo($id)) {
                echo json_encode(['success' => false, 'mensaje' => 'Este personal no tiene un usuario asignado']);
                exit;
            }
            // Al personal la contraseña nueva la escribe el administrador (no se genera)
            $errorPassword = UsuarioControladores::ValidarPasswordPersonal($_POST['password'] ?? '', $_POST['confirmacion'] ?? '');
            if ($errorPassword !== null) {
                echo json_encode(['success' => false, 'mensaje' => $errorPassword]);
                exit;
            }
            $passwordTexto = null; // ya la conoce quien la escribió: no se devuelve
            $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $resultado = UsuarioModelos::RestablecerPasswordPersonalModelo($id, $passwordHash);
            break;

        default:
            echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida']);
            exit;
    }

    if ($resultado === 'exitoso') {
        $respuesta = ['success' => true, 'mensaje' => 'Contraseña restablecida correctamente'];
        if ($passwordTexto !== null) {
            $respuesta['password'] = $passwordTexto;
        }
        echo json_encode($respuesta);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'No se pudo restablecer la contraseña']);
    }
} catch (Exception $e) {
    error_log("Error en restablecerpassword.ajax.php (" . $accion . "): " . $e->getMessage());
    echo json_encode(['success' => false, 'mensaje' => 'Error al restablecer la contraseña']);
}
