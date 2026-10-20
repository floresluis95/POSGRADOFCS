<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX del Cronograma de Defensa (Tesis/Monografía) del Panel principal
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controladores/cronogramadefensa.controlador.php';

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
    exit();
}

$accion = $_POST['accion'] ?? '';
$controlador = new CronogramaDefensaControlador();

switch ($accion) {
    case 'listar':
        $controlador->ListarAjaxControlador();
        break;

    case 'registrar':
        $controlador->RegistrarAjaxControlador();
        break;

    case 'actualizar':
        $controlador->ActualizarAjaxControlador();
        break;

    case 'anular':
        $controlador->AnularAjaxControlador();
        break;

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
