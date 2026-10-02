<?php
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC', 'AUX']); // Solo el personal (no estudiantes ni docentes)
/**
 * AJAX para el módulo de Historial de Pagos (búsqueda por C.I.)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/historialpagos.modelo.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
    exit;
}

if (!isset($_POST['accion'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No se especificó ninguna acción']);
    exit;
}

switch ($_POST['accion']) {
    case 'buscarPorCi':
        buscarPorCi();
        break;

    case 'buscarPorNombre':
        buscarPorNombre();
        break;

    case 'buscarPorEstudianteId':
        buscarPorEstudianteId();
        break;

    case 'actualizarMatricula':
        actualizarMatricula();
        break;

    case 'actualizarCuota':
        actualizarCuota();
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}

function buscarPorCi()
{
    $ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';

    if ($ci === '') {
        echo json_encode(['success' => false, 'mensaje' => 'Ingrese un C.I. para buscar']);
        return;
    }

    $resultado = HistorialPagosModelos::BuscarHistorialPorCiModelo($ci);

    if ($resultado === null) {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró ningún estudiante con ese C.I.']);
        return;
    }

    echo json_encode(['success' => true] + $resultado);
}

/**
 * Buscar estudiantes por nombre y/o apellidos. Si el término coincide con un
 * único estudiante se devuelve directamente su historial completo (mismo
 * formato que buscarPorCi); si coinciden varios, se devuelve la lista para
 * que el usuario elija a cuál ver.
 */
function buscarPorNombre()
{
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

    if (mb_strlen($nombre) < 3) {
        echo json_encode(['success' => false, 'mensaje' => 'Ingrese al menos 3 caracteres para buscar']);
        return;
    }

    $estudiantes = HistorialPagosModelos::BuscarEstudiantesPorNombreModelo($nombre);

    if (empty($estudiantes)) {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró ningún estudiante con ese nombre']);
        return;
    }

    if (count($estudiantes) === 1) {
        $resultado = HistorialPagosModelos::BuscarHistorialPorEstudianteIdModelo($estudiantes[0]['EstudianteID']);
        echo json_encode(['success' => true, 'tipo' => 'unico'] + $resultado);
        return;
    }

    echo json_encode(['success' => true, 'tipo' => 'lista', 'estudiantes' => $estudiantes]);
}

/**
 * Obtener el historial completo de un estudiante elegido desde la lista de
 * resultados de la búsqueda por nombre.
 */
function buscarPorEstudianteId()
{
    if (empty($_POST['estudianteId']) || !is_numeric($_POST['estudianteId'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Estudiante no válido']);
        return;
    }

    $resultado = HistorialPagosModelos::BuscarHistorialPorEstudianteIdModelo((int)$_POST['estudianteId']);

    if ($resultado === null) {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró al estudiante']);
        return;
    }

    echo json_encode(['success' => true, 'tipo' => 'unico'] + $resultado);
}

/**
 * Leer y validar los campos comunes del formulario de edición de un pago
 * (matrícula o cuota): N° de voucher, fecha, monto y, opcionalmente, una foto
 * nueva (reemplaza a la guardada; si no se envía, se conserva la actual).
 * @return array|null null si falta algún dato obligatorio (ya responde el error)
 */
function leerDatosEdicionPago()
{
    $numeroVoucher = isset($_POST['numeroVoucher']) ? trim($_POST['numeroVoucher']) : '';
    $fechaPago = isset($_POST['fechaPago']) ? trim($_POST['fechaPago']) : '';
    $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;

    if ($numeroVoucher === '' || $fechaPago === '' || $monto <= 0) {
        echo json_encode(['success' => false, 'mensaje' => 'Complete el N° de voucher, la fecha y un monto válido']);
        return null;
    }

    $foto = null;
    if (isset($_FILES['fotoVoucher']) && $_FILES['fotoVoucher']['error'] === UPLOAD_ERR_OK) {
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $tipoArchivo = $_FILES['fotoVoucher']['type'];
        $tamanioMaximo = 5 * 1024 * 1024; // 5MB

        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            echo json_encode(['success' => false, 'mensaje' => 'Formato de imagen no permitido. Solo JPG, PNG, GIF o WEBP']);
            return null;
        }
        if ($_FILES['fotoVoucher']['size'] > $tamanioMaximo) {
            echo json_encode(['success' => false, 'mensaje' => 'La imagen es muy grande. Máximo 5MB']);
            return null;
        }

        $foto = file_get_contents($_FILES['fotoVoucher']['tmp_name']);
    }

    return [
        'numeroVoucher' => htmlspecialchars($numeroVoucher),
        'fechaPago' => $fechaPago,
        'monto' => $monto,
        'foto' => $foto
    ];
}

/**
 * Corregir el voucher de matrícula de una inscripción (fecha/voucher/monto y,
 * opcionalmente, la foto). No revalida ni vuelve a confirmar la inscripción.
 */
function actualizarMatricula()
{
    if (empty($_POST['idInscripcion']) || !is_numeric($_POST['idInscripcion'])) {
        echo json_encode(['success' => false, 'mensaje' => 'La inscripción es requerida']);
        return;
    }

    $datos = leerDatosEdicionPago();
    if ($datos === null) {
        return;
    }

    $resultado = HistorialPagosModelos::ActualizarMatriculaModelo((int)$_POST['idInscripcion'], $datos);

    echo json_encode([
        'success' => $resultado === 'exitoso',
        'mensaje' => $resultado === 'exitoso' ? 'Matrícula actualizada correctamente' : 'No se pudo actualizar la matrícula'
    ]);
}

/**
 * Corregir una cuota ya registrada (fecha/voucher/monto y, opcionalmente, la
 * foto). No cambia su Estado (Pagado/Pendiente/Rechazado/Anulado).
 */
function actualizarCuota()
{
    if (empty($_POST['idPagoModulo']) || !is_numeric($_POST['idPagoModulo'])) {
        echo json_encode(['success' => false, 'mensaje' => 'La cuota es requerida']);
        return;
    }

    $datos = leerDatosEdicionPago();
    if ($datos === null) {
        return;
    }

    $resultado = HistorialPagosModelos::ActualizarCuotaModelo((int)$_POST['idPagoModulo'], $datos);

    echo json_encode([
        'success' => $resultado === 'exitoso',
        'mensaje' => $resultado === 'exitoso' ? 'Cuota actualizada correctamente' : 'No se pudo actualizar la cuota'
    ]);
}
