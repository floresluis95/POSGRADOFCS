<?php
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC']); // Solo Administración y Secretaría (igual que las páginas de certificados)
/**
 * AJAX para Certificados (módulos calificados de un estudiante en un programa)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerTrabajoGrado') {
    $estudianteID = intval($_POST['estudianteID'] ?? 0);
    $programaID = intval($_POST['programaID'] ?? 0);

    if (!$estudianteID || !$programaID) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit();
    }

    $trabajo = CertificadoModelo::ObtenerTrabajoGradoModelo($estudianteID, $programaID);
    echo json_encode(['success' => true, 'trabajo' => $trabajo]);
    exit();
}

// Guardar solo el título del trabajo de grado (botón "Guardar título" de la
// Aprobación Programa de Posgrado). Lo usan después Medio Magnético y las defensas.
if (isset($_POST['accion']) && $_POST['accion'] === 'guardarTitulo') {
    if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC'], true)) {
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para esta acción']);
        exit();
    }
    $estudianteID = intval($_POST['estudianteID'] ?? 0);
    $programaID = intval($_POST['programaID'] ?? 0);
    $titulo = CertificadoModelo::NormalizarTitulo($_POST['titulo'] ?? '');

    if (!$estudianteID || !$programaID || $titulo === '') {
        echo json_encode(['success' => false, 'mensaje' => 'Seleccione el estudiante y escriba el título']);
        exit();
    }

    $ok = CertificadoModelo::GuardarTituloModelo($estudianteID, $programaID, $titulo, $_SESSION['IdUsuario'] ?? null);
    echo json_encode(['success' => $ok, 'titulo' => $titulo, 'mensaje' => $ok ? 'Título guardado' : 'No se pudo guardar el título']);
    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'estadoDocumentos') {
    $estudianteID = intval($_POST['estudianteID'] ?? 0);
    $programaID = intval($_POST['programaID'] ?? 0);

    if (!$estudianteID || !$programaID) {
        echo json_encode(['error' => 'Datos incompletos']);
        exit();
    }

    $estado = CertificadoModelo::EstadoDocumentosEstudianteModelo($estudianteID, $programaID);
    $trabajo = CertificadoModelo::ObtenerTrabajoGradoModelo($estudianteID, $programaID);
    echo json_encode([
        'success' => true,
        'tipos' => CertificadoModelo::TiposDocumento(),
        'estado' => $estado,
        'trabajo' => $trabajo,
    ]);
    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'actualizarTrabajoGrado') {
    $idTrabajo = intval($_POST['idTrabajo'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $calificacion = $_POST['calificacion'] ?? '';
    $tribunal = $_POST['tribunal'] ?? '';

    if (!$idTrabajo || $titulo === '') {
        echo json_encode(['error' => 'Datos incompletos']);
        exit();
    }

    $ok = CertificadoModelo::ActualizarTrabajoGradoModelo($idTrabajo, $titulo, $calificacion, $tribunal);
    echo json_encode(['success' => $ok]);
    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'listarDocumentosEmitidos') {
    $ci = trim($_POST['ci'] ?? '');
    $documentos = CertificadoModelo::ListarDocumentosEmitidosModelo($ci !== '' ? $ci : null);
    echo json_encode([
        'success' => true,
        'tipos' => CertificadoModelo::TiposDocumento(),
        'documentos' => $documentos,
    ]);
    exit();
}

echo json_encode(['error' => 'Acción no reconocida']);
exit();
