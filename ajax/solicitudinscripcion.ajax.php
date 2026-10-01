<?php
/**
 * AJAX del formulario suelto de "Inscribirme" (Solicitudes de Inscripción).
 * 'crear' la usa el formulario público (cualquier usuario con sesión válida,
 * estudiante o docente). 'listar' y 'actualizarEstado' son solo para ADM/SEC.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/solicitudinscripcion.modelo.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
    exit;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'crear':
        crearSolicitud();
        break;

    case 'listar':
        listarSolicitudes();
        break;

    case 'actualizarEstado':
        actualizarEstado();
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}

function crearSolicitud()
{
    $nombre = trim($_POST['nombre'] ?? '');
    $apaterno = trim($_POST['apaterno'] ?? '');
    $amaterno = trim($_POST['amaterno'] ?? '');
    $ci = trim($_POST['ci'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $celular = trim($_POST['celular'] ?? '');
    $programaID = trim($_POST['programaID'] ?? '');
    $programaNombre = trim($_POST['programaNombre'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($nombre === '' || $apaterno === '' || $ci === '' || $celular === '') {
        echo json_encode(['success' => false, 'mensaje' => 'Complete los campos obligatorios: nombres, apellido paterno, C.I. y celular']);
        return;
    }

    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'mensaje' => 'El correo electrónico no es válido']);
        return;
    }

    $idSolicitud = SolicitudInscripcionModelos::CrearSolicitudModelo([
        'nombre' => $nombre,
        'apaterno' => $apaterno,
        'amaterno' => $amaterno,
        'ci' => $ci,
        'complemento' => $complemento,
        'correo' => $correo,
        'celular' => $celular,
        'programaID' => $programaID !== '' ? (int) $programaID : null,
        'programaNombre' => $programaNombre,
        'mensaje' => $mensaje,
        'usuarioID' => $_SESSION['IdUsuario'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'mensaje' => 'Su solicitud fue registrada. La Secretaría de Posgrado se pondrá en contacto con usted a la brevedad.',
        'idSolicitud' => $idSolicitud
    ]);
}

function validarPersonalAdministrativo()
{
    $tipo = $_SESSION['Tipo'] ?? '';
    return in_array($tipo, ['ADM', 'SEC', 'AUX'], true);
}

function listarSolicitudes()
{
    if (!validarPersonalAdministrativo()) {
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para ver las solicitudes']);
        return;
    }

    $estado = trim($_POST['estado'] ?? $_GET['estado'] ?? '');
    $solicitudes = SolicitudInscripcionModelos::ListarSolicitudesModelo($estado !== '' ? $estado : null);

    echo json_encode(['success' => true, 'solicitudes' => $solicitudes]);
}

function actualizarEstado()
{
    if (!validarPersonalAdministrativo()) {
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para esta acción']);
        return;
    }

    $id = (int) ($_POST['idSolicitud'] ?? 0);
    $estado = trim($_POST['estado'] ?? '');
    $estadosValidos = ['PENDIENTE', 'CONTACTADO', 'DESCARTADO'];

    if (!$id || !in_array($estado, $estadosValidos, true)) {
        echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
        return;
    }

    SolicitudInscripcionModelos::ActualizarEstadoSolicitudModelo($id, $estado);

    echo json_encode(['success' => true, 'mensaje' => 'Estado actualizado']);
}
