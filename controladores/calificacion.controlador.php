<?php
/**
 * Controlador de Calificaciones
 * Gestiona las acciones relacionadas con las calificaciones finales
 */

class CalificacionControlador
{
    /**
     * Listar grados académicos disponibles
     */
    public function ListarGradosAcademicosControlador()
    {
        $grados = CalificacionModelo::ObtenerGradosAcademicosModelo();

        foreach ($grados as $grado) {
            echo '<option value="' . htmlspecialchars($grado['GradoAcademico']) . '">' .
                 htmlspecialchars($grado['GradoAcademico']) . '</option>';
        }
    }

    /**
     * Obtener programas por grado académico (AJAX)
     */
    public function ObtenerProgramasPorGradoControlador()
    {
        if (isset($_POST['gradoAcademico'])) {
            $gradoAcademico = $_POST['gradoAcademico'];
            $programas = CalificacionModelo::ObtenerProgramasPorGradoModelo($gradoAcademico);

            echo json_encode([
                'status' => 'success',
                'data' => $programas
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Grado académico no especificado'
            ]);
        }
    }

    /**
     * Obtener módulos asignados al docente por programa (AJAX)
     */
    public function ObtenerModulosDocenteControlador()
    {
        if (isset($_POST['programaID']) && isset($_POST['docenteID'])) {
            $programaID = intval($_POST['programaID']);
            $docenteID = intval($_POST['docenteID']);

            $modulos = CalificacionModelo::ObtenerModulosDocentePorProgramaModelo($docenteID, $programaID);

            echo json_encode([
                'status' => 'success',
                'data' => $modulos
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetros incompletos'
            ]);
        }
    }

    /**
     * Obtener estudiantes inscritos en un módulo (AJAX)
     */
    public function ObtenerEstudiantesPorModuloControlador()
    {
        if (isset($_POST['moduloID']) && isset($_POST['programaID'])) {
            $moduloID = intval($_POST['moduloID']);
            $programaID = intval($_POST['programaID']);

            $estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

            echo json_encode([
                'status' => 'success',
                'data' => $estudiantes
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetros incompletos'
            ]);
        }
    }

    /**
     * Guardar calificaciones finales (AJAX)
     */
    public function GuardarCalificacionesControlador()
    {
        if (isset($_POST['programaID']) && isset($_POST['moduloID']) && isset($_POST['calificaciones'])) {
            $datos = [
                'programaID' => intval($_POST['programaID']),
                'moduloID' => intval($_POST['moduloID']),
                'calificaciones' => json_decode($_POST['calificaciones'], true)
            ];

            // Validar que hay calificaciones
            if (empty($datos['calificaciones'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No hay calificaciones para guardar'
                ]);
                return;
            }

            $resultado = CalificacionModelo::GuardarCalificacionesModelo($datos);

            if ($resultado === 'exitoso') {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Calificaciones guardadas exitosamente'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al guardar las calificaciones'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos incompletos'
            ]);
        }
    }

    /**
     * Listar todos los docentes para el SELECT
     */
    public function ListarDocentesSelectControlador()
    {
        $docentes = CalificacionModelo::ListarTodosLosDocentesModelo();

        foreach ($docentes as $docente) {
            $nombreCompleto = $docente['Nombre'] . ' ' . $docente['Apaterno'] . ' ' . $docente['Amaterno'];
            $ci = $docente['Ci'];
            if (!empty($docente['Complemento'])) {
                $ci .= '-' . $docente['Complemento'];
            }
            $ci .= ' ' . $docente['Exp'];

            $especialidad = !empty($docente['Especialidad']) ? $docente['Especialidad'] : 'No especificada';

            echo '<option value="' . $docente['DocenteID'] . '"
                         data-nombre="' . htmlspecialchars($nombreCompleto) . '"
                         data-ci="' . htmlspecialchars($ci) . '"
                         data-especialidad="' . htmlspecialchars($especialidad) . '">' .
                 htmlspecialchars($nombreCompleto) . ' - ' . htmlspecialchars($ci) .
                 '</option>';
        }
    }

    /**
     * Obtener asignaciones de un docente (AJAX)
     */
    public function ObtenerAsignacionesDocenteControlador()
    {
        if (isset($_POST['docenteID'])) {
            $docenteID = intval($_POST['docenteID']);
            $asignaciones = CalificacionModelo::ObtenerAsignacionesDocenteModelo($docenteID);

            echo json_encode([
                'status' => 'success',
                'data' => $asignaciones
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de docente no especificado'
            ]);
        }
    }

    /**
     * Obtener información del docente logueado (para vista de docentes)
     */
    public function ObtenerDocenteLogueadoControlador()
    {
        if (!session_id()) {
            session_start();
        }

        if (isset($_SESSION['Usuario'])) {
            $ci = $_SESSION['Usuario'];
            $docente = CalificacionModelo::ObtenerDocentePorCIModelo($ci);

            if ($docente) {
                // Agregar nombre completo
                $docente['NombreCompleto'] = $docente['Nombre'] . ' ' . $docente['Apaterno'] . ' ' . $docente['Amaterno'];
                $docente['CICompleto'] = $docente['Ci'];
                if (!empty($docente['Complemento'])) {
                    $docente['CICompleto'] .= '-' . $docente['Complemento'];
                }
                $docente['CICompleto'] .= ' ' . $docente['Exp'];

                echo json_encode([
                    'status' => 'success',
                    'data' => $docente
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se encontró un docente asociado a este usuario'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sesión no válida'
            ]);
        }
    }
}
?>
