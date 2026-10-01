<?php
/**
 * AJAX de Recuperación de Contraseña por correo (página de ingreso).
 * Es el único endpoint de ajax/ que NO exige sesión: lo usa quien no puede
 * ingresar. Por eso limita los envíos y nunca revela si un usuario existe
 * (ver RecuperacionPasswordControlador).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controladores/recuperacionpassword.controlador.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '';

switch ($_POST['accion'] ?? '') {
    case 'solicitarCodigo':
        echo json_encode(RecuperacionPasswordControlador::SolicitarCodigoControlador($_POST['identificador'] ?? '', $ip));
        break;

    case 'cambiarPassword':
        echo json_encode(RecuperacionPasswordControlador::CambiarPasswordControlador(
            $_POST['identificador'] ?? '',
            $_POST['codigo'] ?? '',
            $_POST['password'] ?? '',
            $_POST['confirmacion'] ?? ''
        ));
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
