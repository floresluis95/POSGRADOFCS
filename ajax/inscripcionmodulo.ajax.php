<?php
/**
 * AJAX para gestionar inscripciones a módulos
 * Obtiene módulos inscritos por estudiante
 */

require_once "../modelos/inscripcionmodulo.modelo.php";
require_once "../controladores/inscripcionmodulo.controlador.php";

// Obtener módulos inscritos por estudiante (filtrado por inscripción específica)
if (isset($_POST["accion"]) && $_POST["accion"] === "obtenerModulosInscritos" && isset($_POST["estudianteID"])) {
    $estudianteID = (int)$_POST["estudianteID"];
    $idInscripcion = isset($_POST["idInscripcion"]) ? (int)$_POST["idInscripcion"] : null;
    $programaID = isset($_POST["programaID"]) ? (int)$_POST["programaID"] : null;

    try {
        $modulos = InscripcionModuloModelos::ObtenerModulosInscritosEstudianteModelo($estudianteID, $programaID, $idInscripcion);

        // Formatear los datos para la respuesta
        $resultado = array_map(function($modulo) {
            return [
                'NombreModulo' => $modulo['NombreModulo'],
                'Codigo' => $modulo['Codigo'],
                'Creditos' => $modulo['Creditos'],
                'Costo' => $modulo['costomodulo'],
                'NumeroVaucher' => $modulo['nvauchermodulo'],
                'FechaInscripcion' => $modulo['FechaInscripcion'],
                'Estado' => $modulo['Estado'],
                'NombreDocente' => $modulo['NombreDocente'] ?? 'Sin asignar',
                'EspecialidadDocente' => $modulo['EspecialidadDocente'] ?? '',
                'Nota' => $modulo['Nota'],
                'EstadoAprobacion' => $modulo['EstadoAprobacion']
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

    $listar = new InscripcionModuloControladores();
    $listar->ListarEstudiantesMatriculadosControlador($programaID);
    exit();
}

// Si no hay acción válida
echo json_encode([
    'error' => true,
    'mensaje' => 'Acción no válida'
]);
?>
