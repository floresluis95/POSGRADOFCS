<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC']); // Certificados: solo Administración
// La Secretaría no emite certificados: solo busca al estudiante y su trabajo de grado
// para programar defensas desde el panel (panel-admin.js)
if (($_SESSION['Tipo'] ?? '') === 'SEC' && !in_array($_POST['accion'] ?? '', ['buscarPorCI', 'obtenerTrabajoGrado'], true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para esta acción']);
    exit;
}
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

require_once __DIR__ . '/../modelos/certificado.modelo.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['accion']) && $_POST['accion'] === 'buscarPorCI') {
    $ci = trim($_POST['ci'] ?? '');

    if ($ci === '') {
        echo json_encode(['error' => 'Ingrese un número de carnet']);
        exit();
    }

    $resultados = CertificadoModelo::BuscarInscripcionesModelo($ci);
    echo json_encode(['success' => true, 'resultados' => $resultados]);
    exit();
}

// Buscador de los certificados: C.I. (solo números) o nombre y/o apellidos
if (isset($_POST['accion']) && $_POST['accion'] === 'buscarEstudiantes') {
    $termino = trim($_POST['termino'] ?? '');
    if (mb_strlen($termino) < 3 && !ctype_digit($termino)) {
        echo json_encode(['success' => false, 'mensaje' => 'Escriba el C.I. o al menos 3 letras del nombre']);
        exit();
    }
    $resultados = CertificadoModelo::BuscarInscripcionesModelo($termino, !empty($_POST['incluirAbandono']));
    foreach ($resultados as &$fila) {
        $fila['GradoLegible'] = CertificadoModelo::GradoLegible($fila['GradoAcademico']);
    }
    unset($fila);
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
    echo json_encode([
        'success' => true,
        'modulos' => $modulos,
        'totalModulos' => CertificadoModelo::ContarModulosProgramaModelo($programaID),
    ]);
    exit();
}

// ¿El N° de voucher ya se usó? (en otro certificado, matrícula, cuota o clínica).
// La pantalla lo pregunta antes de la vista previa; el PDF lo vuelve a revisar.
if (isset($_POST['accion']) && $_POST['accion'] === 'verificarVoucher') {
    $vaucher = CertificadoModelo::NormalizarVoucher($_POST['vaucher'] ?? '');
    $mensaje = $vaucher ? CertificadoModelo::VoucherRepetidoMensaje($vaucher, intval($_POST['idDocumento'] ?? 0)) : null;
    echo json_encode(['success' => true, 'repetido' => (bool)$mensaje, 'mensaje' => $mensaje]);
    exit();
}

// Docentes y personas de tribunales anteriores, para elegir el tribunal
if (isset($_POST['accion']) && $_POST['accion'] === 'sugerenciasTribunal') {
    echo json_encode(['success' => true, 'personas' => CertificadoModelo::SugerenciasTribunalModelo()]);
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

    // Todas las emisiones del estudiante en ese programa (si se emitieron 3 certificados de
    // calificaciones aparecen los 3), de la más reciente a la más antigua
    $documentos = CertificadoModelo::ListarDocumentosEmitidosModelo(null, 5000, ['estudianteID' => $estudianteID, 'programaID' => $programaID]);
    $trabajo = CertificadoModelo::ObtenerTrabajoGradoModelo($estudianteID, $programaID);
    echo json_encode([
        'success' => true,
        'tipos' => CertificadoModelo::TiposParaListado(),
        'documentos' => $documentos,
        // Certificados que exige su grado para titularse y cuáles ya tiene en este programa
        'titulacion' => CertificadoModelo::EstadoTitulacionModelo($estudianteID, $programaID),
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
    // Historial de pagos de certificados: fechas (Y-m-d) y tipo; con filtros se lista todo
    // el periodo (no solo los últimos 100)
    $fecha = function ($campo) {
        $valor = trim($_POST[$campo] ?? '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : null;
    };
    $tipo = trim($_POST['tipo'] ?? '');
    $filtros = [
        'desde' => $fecha('desde'),
        'hasta' => $fecha('hasta'),
        'tipo' => array_key_exists($tipo, CertificadoModelo::TiposParaListado()) ? $tipo : null,
        'programaID' => intval($_POST['programaID'] ?? 0) ?: null,
        'sede' => trim($_POST['sede'] ?? '') ?: null,
        'grado' => array_key_exists($_POST['grado'] ?? '', CertificadoModelo::GradosAcademicos()) ? $_POST['grado'] : null,
    ];
    $conFiltros = (bool)array_filter($filtros);
    $documentos = CertificadoModelo::ListarDocumentosEmitidosModelo($ci !== '' ? $ci : null, $conFiltros ? 5000 : 100, $filtros);
    echo json_encode([
        'success' => true,
        'tipos' => CertificadoModelo::TiposParaListado(),
        'documentos' => $documentos,
    ]);
    exit();
}

// Datos guardados de un documento emitido, para reimprimirlo (y corregirlo) desde
// Registro de documentos
if (isset($_POST['accion']) && $_POST['accion'] === 'obtenerDocumento') {
    $documento = CertificadoModelo::ObtenerDocumentoModelo(intval($_POST['idDocumento'] ?? 0));
    if (!$documento) {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró el documento']);
        exit();
    }
    $rutas = CertificadoModelo::RutasPdf();
    echo json_encode([
        'success' => true,
        'documento' => [
            'idDocumento' => (int)$documento['IdDocumento'],
            'tipo' => $documento['TipoDocumento'],
            'estado' => $documento['Estado'],
            'datos' => $documento['Datos'] ? json_decode($documento['Datos'], true) : null,
            'rutaPdf' => $rutas[$documento['TipoDocumento']] ?? null,
        ],
    ]);
    exit();
}

// Documento que registró un envío (marca tokenEmision): al generar un certificado la
// pantalla pasa a Registro de documentos, que lo pregunta hasta que el PDF termine de guardarlo
if (isset($_POST['accion']) && $_POST['accion'] === 'documentoPorEmision') {
    $idDocumento = CertificadoModelo::DocumentoDeEmision((string)($_POST['token'] ?? ''));
    $filas = $idDocumento ? CertificadoModelo::ListarDocumentosEmitidosModelo(null, 1, ['idDocumento' => $idDocumento]) : [];
    if (!$filas) {
        echo json_encode(['success' => false]);
        exit();
    }
    $doc = $filas[0];
    echo json_encode([
        'success' => true,
        'documento' => [
            'idDocumento' => (int)$doc['IdDocumento'],
            'estudianteID' => (int)$doc['EstudianteID'],
            'programaID' => (int)$doc['ProgramaID'],
            'ci' => $doc['Ci'],
        ],
    ]);
    exit();
}

// ===== Certificados exigidos para la titulación (se marcan en Costos de certificados) =====
if (isset($_POST['accion']) && $_POST['accion'] === 'requisitosTitulacion') {
    echo json_encode(['success' => true, 'requisitos' => CertificadoModelo::RequisitosTitulacionModelo(), 'grados' => CertificadoModelo::GradosAcademicos()]);
    exit();
}
if (isset($_POST['accion']) && $_POST['accion'] === 'guardarRequisitoTitulacion') {
    if (($_SESSION['Tipo'] ?? '') !== 'ADM') {
        echo json_encode(['success' => false, 'mensaje' => 'Solo el Administrador define los requisitos de titulación']);
        exit();
    }
    echo json_encode(CertificadoModelo::GuardarRequisitoTitulacionModelo($_POST['clave'] ?? '', $_POST['grado'] ?? '', !empty($_POST['exigir'])));
    exit();
}

// ===== Certificados sin documento (solo se registra el pago) =====
// Catálogo: todos lo leen; solo el ADM lo cambia
if (isset($_POST['accion']) && $_POST['accion'] === 'catalogoSinDocumento') {
    echo json_encode(['success' => true, 'certificados' => CertificadoModelo::ListarSinDocumentoModelo(empty($_POST['todos']))]);
    exit();
}
if (isset($_POST['accion']) && $_POST['accion'] === 'guardarSinDocumento') {
    if (($_SESSION['Tipo'] ?? '') !== 'ADM') {
        echo json_encode(['success' => false, 'mensaje' => 'Solo el Administrador cambia la lista de certificados']);
        exit();
    }
    echo json_encode(CertificadoModelo::GuardarSinDocumentoModelo(
        intval($_POST['id'] ?? 0), $_POST['nombre'] ?? '', $_POST['monto'] ?? '', !empty($_POST['activo'])
    ));
    exit();
}
if (isset($_POST['accion']) && $_POST['accion'] === 'registrarSinDocumento') {
    echo json_encode(CertificadoModelo::RegistrarSinDocumentoModelo(
        intval($_POST['estudianteID'] ?? 0), intval($_POST['programaID'] ?? 0), intval($_POST['idCertificadoSD'] ?? 0),
        $_POST['vaucher'] ?? '', $_POST['monto'] ?? '', $_POST['fechaPago'] ?? '', $_SESSION['IdUsuario'] ?? null
    ));
    exit();
}

// Corregir los datos guardados de un documento sin imprimirlo (Registro de documentos)
if (isset($_POST['accion']) && $_POST['accion'] === 'editarDocumento') {
    $idDocumento = intval($_POST['idDocumento'] ?? 0);
    $cambios = is_array($_POST['datos'] ?? null) ? $_POST['datos'] : [];
    echo json_encode($idDocumento ? CertificadoModelo::EditarDocumentoModelo($idDocumento, $cambios)
        : ['success' => false, 'mensaje' => 'Documento no válido']);
    exit();
}

// Aprobación del decano (solo control): APROBADO, o de vuelta a PENDIENTE si se marcó por error
if (isset($_POST['accion']) && in_array($_POST['accion'], ['aprobarDocumento', 'quitarAprobacion'], true)) {
    $idDocumento = intval($_POST['idDocumento'] ?? 0);
    $aprobar = $_POST['accion'] === 'aprobarDocumento';
    $ok = $idDocumento && CertificadoModelo::CambiarEstadoDocumentoModelo($idDocumento, $aprobar, $_SESSION['IdUsuario'] ?? null);
    echo json_encode([
        'success' => (bool)$ok,
        'mensaje' => $ok ? ($aprobar ? 'Documento aprobado' : 'El documento volvió a pendiente de aprobación') : 'No se pudo cambiar el estado del documento',
    ]);
    exit();
}

// Borrar un documento emitido: solo si aún no lo aprobó el decano
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminarDocumento') {
    $idDocumento = intval($_POST['idDocumento'] ?? 0);
    $ok = $idDocumento && CertificadoModelo::EliminarDocumentoPendienteModelo($idDocumento);
    echo json_encode([
        'success' => (bool)$ok,
        'mensaje' => $ok ? 'Documento borrado' : 'No se pudo borrar: el documento ya fue aprobado por el decano o ya no existe',
    ]);
    exit();
}

echo json_encode(['error' => 'Acción no reconocida']);
exit();
