<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC']); // Pagos recibidos: Administración y Secretaría (solo consulta)

/**
 * AJAX de la página "Pagos recibidos": lista todos los pagos (matrícula, cuotas,
 * clínicas y certificados) con los filtros de la pantalla. Solo lee.
 */
require_once __DIR__ . '/../modelos/pagos.modelo.php';

header('Content-Type: application/json; charset=utf-8');

if (($_POST['accion'] ?? '') !== 'listar') {
    echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
    exit();
}

$fecha = function ($campo) {
    $valor = trim($_POST[$campo] ?? '');
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : null;
};

try {
    $resultado = PagosModelo::Listar([
        'desde' => $fecha('desde'),
        'hasta' => $fecha('hasta'),
        'concepto' => trim($_POST['concepto'] ?? ''),
        'sede' => trim($_POST['sede'] ?? ''),
        'programaID' => (int)($_POST['programaID'] ?? 0),
        'estudiante' => mb_substr(trim($_POST['estudiante'] ?? ''), 0, 80),
    ]);
    echo json_encode([
        'success' => true,
        'pagos' => $resultado['pagos'],
        'recortado' => $resultado['recortado'],
        'limite' => PagosModelo::LIMITE,
    ]);
} catch (Exception $e) {
    error_log('Error en pagos.ajax.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'mensaje' => 'No se pudieron cargar los pagos']);
}
