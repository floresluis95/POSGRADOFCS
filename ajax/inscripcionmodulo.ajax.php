<?php
/**
 * AJAX para gestionar inscripciones a módulos
 * Obtiene módulos inscritos por estudiante
 */

require_once "../modelos/inscripcionmodulo.modelo.php";

// Obtener módulos inscritos por estudiante
if (isset($_POST["accion"]) && $_POST["accion"] === "obtenerModulosInscritos" && isset($_POST["estudianteID"])) {
    $estudianteID = (int)$_POST["estudianteID"];

    try {
        $modulos = InscripcionModuloModelos::ObtenerModulosInscritosEstudianteModelo($estudianteID);

        // Formatear los datos para la respuesta
        $resultado = array_map(function($modulo) {
            return [
                'NombreModulo' => $modulo['NombreModulo'],
                'Codigo' => $modulo['Codigo'],
                'Creditos' => $modulo['Creditos'],
                'Costo' => $modulo['costomodulo'],
                'NumeroVaucher' => $modulo['nvauchermodulo'],
                'FechaInscripcion' => $modulo['FechaInscripcion'],
                'Estado' => $modulo['Estado']
            ];
        }, $modulos);

        echo json_encode($resultado);
    } catch (Exception $e) {
        error_log("Error al obtener módulos inscritos: " . $e->getMessage());
        echo json_encode([]);
    }

    exit();
}

// Si no hay acción válida
echo json_encode([
    'error' => true,
    'mensaje' => 'Acción no válida'
]);
?>
