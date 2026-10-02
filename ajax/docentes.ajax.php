<?php
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC', 'AUX']); // Solo el personal (no estudiantes ni docentes)
/**
 * AJAX para buscar docentes en toda la base de datos (no solo los
 * ya cargados/paginados en la vista de la lista de docentes).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/docentes.modelo.php';
require_once __DIR__ . '/../controladores/docentes.controlador.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida'
    ]);
    exit;
}

if (isset($_POST['buscarPatron'])) {
    $docentesControlador = new DocentesControlador();
    $resultado = $docentesControlador->BuscarDocentesControlador($_POST['buscarPatron']);

    echo json_encode([
        'success' => true,
        'total' => $resultado['total'],
        'html' => $resultado['html'],
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'mensaje' => 'Acción no reconocida'
]);
