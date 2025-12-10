<?php
/**
 * AJAX: Reporte de Notas
 * Maneja las peticiones AJAX para el reporte de calificaciones
 */

require_once '../controladores/reportenotas.controlador.php';
require_once '../modelos/reportenotas.modelo.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'obtenerModulos':
            $controller = new ReporteNotasControlador();
            $controller->ObtenerModulosProgramaControlador();
            break;

        case 'obtenerEstudiantes':
            $controller = new ReporteNotasControlador();
            $controller->ObtenerEstudiantesConNotasControlador();
            break;

        case 'obtenerAuditoria':
            $controller = new ReporteNotasControlador();
            $controller->ObtenerAuditoriaModuloControlador();
            break;

        case 'obtenerResumenAuditoria':
            $controller = new ReporteNotasControlador();
            $controller->ObtenerResumenAuditoriaControlador();
            break;

        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Acción no válida'
            ]);
            break;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se especificó ninguna acción'
    ]);
}
?>
