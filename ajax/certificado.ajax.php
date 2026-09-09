<?php
/**
 * AJAX para Certificados (módulos calificados de un estudiante en un programa)
 */

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Sesión inválida']);
    exit();
}

require_once '../modelos/certificado.modelo.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['accion']) && $_POST['accion'] === 'buscarPorCI') {
    $ci = trim($_POST['ci'] ?? '');

    if ($ci === '') {
        echo json_encode(['error' => 'Ingrese un número de carnet']);
        exit();
    }

    $resultados = CertificadoModelo::BuscarInscripcionesPorCIModelo($ci);
    echo json_encode(['success' => true, 'resultados' => $resultados]);
    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'modulosCalificados') {
    $estudianteID = intval($_POST['estudianteID'] ?? 0);
    $programaID = intval($_POST['programaID'] ?? 0);

    if (!$estudianteID || !$programaID) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit();
    }

    $modulos = CertificadoModelo::ObtenerModulosCalificadosModelo($estudianteID, $programaID);
    echo json_encode(['success' => true, 'modulos' => $modulos]);
    exit();
}

echo json_encode(['error' => 'Acción no reconocida']);
exit();
