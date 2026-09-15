<?php
/**
 * AJAX del Plazo de Cuota por Programa (Calendario Académico)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controladores/plazocuotaprograma.controlador.php';

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
    exit();
}

$accion = $_POST['accion'] ?? '';
$controlador = new PlazoCuotaProgramaControlador();

switch ($accion) {
    case 'listarPlazos':
        $controlador->ListarPlazosAjaxControlador();
        break;

    case 'registrarPlazo':
        $controlador->RegistrarPlazoAjaxControlador();
        break;

    case 'actualizarPlazo':
        $controlador->ActualizarPlazoAjaxControlador();
        break;

    case 'anularPlazo':
        $controlador->AnularPlazoAjaxControlador();
        break;

    case 'avisoEstudiante':
        $controlador->AvisoEstudianteAjaxControlador();
        break;

    default:
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}
