<?php
/**
 * AJAX para Calificaciones
 * Maneja las peticiones AJAX del módulo de calificaciones
 */

require_once '../controladores/calificacion.controlador.php';
require_once '../modelos/calificacion.modelo.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida'
    ]);
    exit;
}

// Determinar la acción solicitada
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $controlador = new CalificacionControlador();

    switch ($accion) {
        case 'buscarDocente':
            $controlador->BuscarDocenteControlador();
            break;

        case 'obtenerAsignaciones':
            $controlador->ObtenerAsignacionesDocenteControlador();
            break;

        case 'obtenerProgramas':
            $controlador->ObtenerProgramasPorGradoControlador();
            break;

        case 'obtenerModulos':
            $controlador->ObtenerModulosDocenteControlador();
            break;

        case 'obtenerEstudiantes':
            $controlador->ObtenerEstudiantesPorModuloControlador();
            break;

        case 'guardarCalificaciones':
            $controlador->GuardarCalificacionesControlador();
            break;

        case 'obtenerDocente':
            $controlador->ObtenerDocenteLogueadoControlador();
            break;

        case 'buscarCalificaciones':
            $controlador->BuscarCalificacionesControlador();
            break;

        case 'obtenerProgramasConCalificaciones':
            $controlador->ObtenerProgramasConCalificacionesControlador();
            break;

        case 'obtenerEstudianteLogueado':
            $controlador->ObtenerEstudianteLogueadoControlador();
            break;

        case 'obtenerProgramasEstudiante':
            $controlador->ObtenerProgramasEstudianteControlador();
            break;

        case 'obtenerCalificacionesEstudiantePrograma':
            $controlador->ObtenerCalificacionesEstudianteProgramaControlador();
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
        'message' => 'Acción no especificada'
    ]);
}
?>
