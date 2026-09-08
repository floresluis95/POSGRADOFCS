<?php
/**
 * AJAX para gestión de pagos de módulos
 */

// Iniciar sesión si aún no se ha iniciado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controladores/pagomodulo.controlador.php';

// Configurar cabecera JSON
header('Content-Type: application/json; charset=utf-8');

// Validar que el usuario esté autenticado
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    echo json_encode([
        'success' => false,
        'error' => 'No tiene permisos para acceder a esta funcionalidad'
    ]);
    exit;
}

// Manejar las diferentes acciones
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $controlador = new PagoModuloControlador();

    switch ($accion) {
        case 'obtenerProgramasEstudiante':
            echo $controlador->ObtenerProgramasEstudianteControlador();
            break;

        case 'obtenerDetalleModulos':
            echo $controlador->ObtenerDetalleModulosEstudianteControlador();
            break;

        // Las siguientes acciones responden JSON y terminan la ejecución por su cuenta
        case 'registrarPagoModulo':
            $controlador->RegistrarPagoModuloAjaxControlador();
            break;

        case 'rechazarCuota':
            $controlador->RechazarCuotaAjaxControlador();
            break;

        case 'anularCuota':
            $controlador->AnularCuotaAjaxControlador();
            break;

        case 'obtenerResumenCuotas':
            $controlador->ObtenerResumenCuotasAjaxControlador();
            break;

        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No se especificó ninguna acción'
    ]);
}
?>
