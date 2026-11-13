<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * Corregir el C.I. de un estudiante, docente o personal (botón "Corregir C.I." de los
 * formularios de edición; scripts/corregir-ci.js). Solo el ADM.
 */

require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM']);

require_once __DIR__ . '/../modelos/correccionci.modelo.php';

header('Content-Type: application/json; charset=utf-8');

if (($_POST['accion'] ?? '') !== 'corregir') {
    echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
    exit;
}

echo json_encode(CorreccionCiModelo::CorregirModelo($_POST['tipo'] ?? '', (int) ($_POST['id'] ?? 0), $_POST['ci'] ?? ''));
