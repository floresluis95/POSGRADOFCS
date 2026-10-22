<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
/**
 * AJAX para Calificaciones
 * Maneja las peticiones AJAX del módulo de calificaciones
 */

require_once __DIR__ . '/../controladores/calificacion.controlador.php';
require_once __DIR__ . '/../modelos/calificacion.modelo.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida'
    ]);
    exit;
}

// Un Estudiante solo puede consultar SUS propias calificaciones (donde además se aplica
// el bloqueo por pago vencido). Buscar notas de otros, guardar, cerrar o reabrir módulos
// es exclusivo de Administración, Secretaría y Docentes.
$accionesEstudiante = ['obtenerEstudianteLogueado', 'obtenerProgramasEstudiante', 'obtenerCalificacionesEstudiantePrograma'];
$tipoSesion = $_SESSION['Tipo'] ?? '';
$accionPedida = $_POST['accion'] ?? '';
// La Secretaría solo consulta e imprime: no registra, cierra ni reabre notas
$accionesEdicion = ['guardarCalificaciones', 'validarCerrarModulo', 'reabrirModulo'];
$permitido = $tipoSesion === 'EST'
    ? in_array($accionPedida, $accionesEstudiante, true)
    : in_array($tipoSesion, ['ADM', 'SEC', 'AUX', 'DOC'], true)
        && !($tipoSesion === 'SEC' && in_array($accionPedida, $accionesEdicion, true));
if (!$permitido) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'No tiene permisos para esta acción'
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

        case 'listarSedesAsignaciones':
            $controlador->ListarSedesAsignacionesControlador();
            break;

        case 'listarProgramasAsignaciones':
            $controlador->ListarProgramasAsignacionesControlador();
            break;

        case 'listarDocentesFiltrados':
            $controlador->ListarDocentesFiltradosControlador();
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

        case 'validarCerrarModulo':
            $controlador->ValidarCerrarModuloControlador();
            break;

        case 'reabrirModulo':
            $controlador->ReabrirModuloControlador();
            break;

        case 'verificarPermisoEdicion':
            $controlador->VerificarPermisoEdicionControlador();
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
