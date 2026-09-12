<?php
/**
 * AJAX del Calendario Académico (clases, actividades, clínicas)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controladores/calendarioevento.controlador.php';

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
    exit();
}

$accion = $_POST['accion'] ?? '';
$controlador = new CalendarioEventoControlador();

switch ($accion) {
    case 'listarEventos':
        $controlador->ListarEventosAjaxControlador();
        break;

    case 'listarDocentes':
        $controlador->ListarDocentesAjaxControlador();
        break;

    case 'registrarEvento':
        $controlador->RegistrarEventoAjaxControlador();
        break;

    case 'actualizarEvento':
        $controlador->ActualizarEventoAjaxControlador();
        break;

    case 'anularEvento':
        $controlador->AnularEventoAjaxControlador();
        break;

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
