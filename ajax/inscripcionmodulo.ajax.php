<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC', 'AUX']); // Solo el personal (no estudiantes ni docentes)
/**
 * AJAX para gestionar inscripciones a módulos
 * Obtiene módulos inscritos por estudiante
 */

require_once __DIR__ . "/../modelos/inscripcionmodulo.modelo.php";
require_once __DIR__ . "/../controladores/inscripcionmodulo.controlador.php";

// Obtener módulos inscritos por estudiante (filtrado por inscripción específica)
if (isset($_POST["accion"]) && $_POST["accion"] === "obtenerModulosInscritos" && isset($_POST["estudianteID"])) {
    $estudianteID = (int)$_POST["estudianteID"];
    $idInscripcion = isset($_POST["idInscripcion"]) ? (int)$_POST["idInscripcion"] : null;
    $programaID = isset($_POST["programaID"]) ? (int)$_POST["programaID"] : null;

    try {
        $modulos = InscripcionModuloModelos::ObtenerModulosInscritosEstudianteModelo($estudianteID, $programaID, $idInscripcion);

        // Formatear los datos para la respuesta. Los módulos son puramente académicos
        // (el pago ya no está ligado a un módulo específico, sino a cuotas del programa).
        $resultado = array_map(function($modulo) {
            return [
                'NombreModulo' => $modulo['NombreModulo'],
                'Codigo' => $modulo['Codigo'],
                'FechaInicio' => $modulo['FechaInicio'] ?? null,
                'FechaFinal' => $modulo['FechaFinal'] ?? null,
                'NombreDocente' => $modulo['NombreDocente'] ?? 'Sin asignar',
                'EspecialidadDocente' => $modulo['EspecialidadDocente'] ?? '',
                'Nota' => $modulo['Nota'],
                'EstadoAprobacion' => $modulo['EstadoAprobacion'],
                // Orden de cuota que cubre el módulo (solo control, ver ordenpagomodulo)
                'CuotaOrden' => $modulo['CuotaOrden'] ?? null,
                'CuotaEstado' => $modulo['CuotaEstado'] ?? null
            ];
        }, $modulos);

        echo json_encode($resultado);
    } catch (Exception $e) {
        error_log("Error al obtener módulos inscritos: " . $e->getMessage());
        echo json_encode([]);
    }

    exit();
}

// Cargar tabla HTML de estudiantes matriculados filtrada por programa
// (misma fuente que la carga inicial de matriculados.php, para que ambas
// coincidan siempre y no se pierdan acciones como "Plan de Pago del Programa"
// al cambiar el filtro).
if (isset($_POST["accion"]) && $_POST["accion"] == "cargarTablaMatriculados") {
    $programaID = isset($_POST["programaIDFiltro"]) && $_POST["programaIDFiltro"] !== ''
        ? (int)$_POST["programaIDFiltro"]
        : null;

    $sede = isset($_POST["sedeFiltro"]) && $_POST["sedeFiltro"] !== ''
        ? trim($_POST["sedeFiltro"])
        : null;

    $estado = isset($_POST["estadoFiltro"]) && $_POST["estadoFiltro"] !== ''
        ? trim($_POST["estadoFiltro"])
        : 'ACTIVO';

    $listar = new InscripcionModuloControladores();
    $requisitos = in_array($_POST["requisitosFiltro"] ?? "", ["INCOMPLETOS", "COMPLETOS"], true) ? $_POST["requisitosFiltro"] : null;
    // Grado académico del programa (filtro de Matriculados)
    $grado = isset($_POST["gradoFiltro"]) && trim($_POST["gradoFiltro"]) !== '' ? trim($_POST["gradoFiltro"]) : null;
    // Certificados para la titulación (solo programas concluidos)
    $titulacion = in_array($_POST["titulacionFiltro"] ?? "", ["LISTOS", "FALTAN"], true) ? $_POST["titulacionFiltro"] : null;
    $listar->ListarEstudiantesMatriculadosControlador($programaID, $sede, $estado, $requisitos, $grado, $titulacion);
    exit();
}

// Módulos del programa para elegir desde cuál abandona
if (isset($_POST["accion"]) && $_POST["accion"] == "modulosAbandono") {
    header('Content-Type: application/json; charset=utf-8');
    $datos = MatriculaModelos::ModulosParaAbandonoModelo((int)($_POST['idinscripcion'] ?? 0));
    echo json_encode($datos
        ? ['success' => true, 'modulos' => $datos['modulos']]
        : ['success' => false, 'mensaje' => 'Inscripción no encontrada']);
    exit();
}

// Marcar/revertir el abandono de una inscripción
if (isset($_POST["accion"]) && $_POST["accion"] == "actualizarEstadoInscripcion") {
    $controlador = new InscripcionModuloControladores();
    $controlador->ActualizarEstadoInscripcionAjaxControlador();
}

// Si no hay acción válida
echo json_encode([
    'error' => true,
    'mensaje' => 'Acción no válida'
]);
?>
