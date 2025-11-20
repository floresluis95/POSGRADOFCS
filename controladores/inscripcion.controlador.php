<?php
/**
 * Controlador de Inscripción
 * Gestiona inscripciones, planes de pago y vouchers
 * Compatible con PHP 8 - MVC
 */

// Incluir modelos necesarios
require_once __DIR__ . '/../modelos/inscripcion.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class InscripcionControladores
{
    /**
     * Procesar solicitud AJAX para obtener programas por grado académico
     */
    public function ObtenerProgramasPorGradoControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Log de depuración
            error_log("ObtenerProgramasPorGradoControlador - POST recibido: " . print_r($_POST, true));

            if (!isset($_POST['gradoAcademico']) || empty($_POST['gradoAcademico'])) {
                error_log("ObtenerProgramasPorGradoControlador - ERROR: gradoAcademico no proporcionado");
                echo json_encode([
                    'success' => false,
                    'message' => 'Grado académico no proporcionado',
                    'debug' => $_POST
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $gradoAcademico = trim($_POST['gradoAcademico']);
            error_log("ObtenerProgramasPorGradoControlador - Buscando programas para: " . $gradoAcademico);

            // Validar que el grado académico sea válido
            $gradosValidos = ['DIPLOMADO', 'MAESTRIA', 'ESPECIALIDAD', 'MAESTRÍA'];
            if (!in_array($gradoAcademico, $gradosValidos)) {
                error_log("ObtenerProgramasPorGradoControlador - ERROR: Grado académico no válido: " . $gradoAcademico);
                echo json_encode([
                    'success' => false,
                    'message' => 'Grado académico no válido: ' . $gradoAcademico,
                    'gradosValidos' => $gradosValidos
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $programas = InscripcionModelos::ListarProgramasPorGradoModelo($gradoAcademico);
            error_log("ObtenerProgramasPorGradoControlador - Programas encontrados: " . count($programas));

            if ($programas && count($programas) > 0) {
                echo json_encode([
                    'success' => true,
                    'data' => $programas,
                    'count' => count($programas)
                ], JSON_UNESCAPED_UNICODE);
            } else {
                error_log("ObtenerProgramasPorGradoControlador - No se encontraron programas");
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron programas para: ' . $gradoAcademico,
                    'gradoAcademico' => $gradoAcademico
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerProgramasPorGradoControlador: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener programas: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Procesar solicitud AJAX para obtener detalles de un programa
     */
    public function ObtenerDetalleProgramaControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (!isset($_POST['programaID']) || empty($_POST['programaID'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de programa no proporcionado'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $programaID = intval($_POST['programaID']);

            if ($programaID <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de programa no válido'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $programa = InscripcionModelos::ObtenerDetalleProgramaModelo($programaID);

            if ($programa) {
                echo json_encode([
                    'success' => true,
                    'data' => $programa
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Programa no encontrado o inactivo'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            error_log("Error en ObtenerDetalleProgramaControlador: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener detalles: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

 
     
   
}

