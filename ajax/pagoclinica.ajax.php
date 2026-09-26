<?php
/**
 * AJAX para Pago de Clínicas (pestaña "Clínicas" del modal Gestión de Pagos).
 * La anulación de una orden pendiente reutiliza 'anularPreregistro' de ordenpago.ajax.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controladores/pagoclinica.controlador.php';

// Solo personal administrativo: un estudiante nunca gestiona pagos de clínica
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar'] || ($_SESSION['Tipo'] ?? '') === 'EST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'mensaje' => 'Sesión no válida']);
    exit;
}

$controlador = new PagoClinicaControlador();

// Ver la foto del voucher (GET, se abre como imagen/archivo, no JSON)
if (isset($_GET['verVoucher'])) {
    $controlador->MostrarFotoVoucherControlador($_GET['verVoucher']);
}

header('Content-Type: application/json; charset=utf-8');

switch ($_POST['accion'] ?? '') {
    case 'obtenerEstado':
        echo json_encode($controlador->ObtenerEstadoControlador());
        break;

    case 'registrarOrden':
        echo json_encode($controlador->RegistrarOrdenControlador());
        break;

    case 'registrarPago':
        echo json_encode($controlador->RegistrarPagoControlador());
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
}
