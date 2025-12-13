<?php
/**
 * Controlador de Calificaciones
 * Gestiona las acciones relacionadas con las calificaciones finales
 */

require_once __DIR__ . '/../modelos/conexion.modelo.php';

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
        if (session_status() === PHP_SESSION_NONE) {
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

    /**
     * Buscar calificaciones de estudiantes (AJAX)
     * Para vista de búsqueda de calificaciones
     */
    public function BuscarCalificacionesControlador()
    {
        if (isset($_POST['tipoBusqueda']) && isset($_POST['valorBusqueda'])) {
            $tipoBusqueda = $_POST['tipoBusqueda'];
            $valorBusqueda = trim($_POST['valorBusqueda']);
            $programaID = isset($_POST['programaID']) ? $_POST['programaID'] : null;

            // Validar que el valor de búsqueda no esté vacío
            if (empty($valorBusqueda)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Debe ingresar un valor para buscar'
                ]);
                return;
            }

            // Validar tipo de búsqueda
            $tiposValidos = ['ci', 'nombre', 'apellido'];
            if (!in_array($tipoBusqueda, $tiposValidos)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Tipo de búsqueda no válido'
                ]);
                return;
            }

            $resultados = CalificacionModelo::BuscarCalificacionesEstudianteModelo(
                $tipoBusqueda,
                $valorBusqueda,
                $programaID
            );

            echo json_encode([
                'status' => 'success',
                'data' => $resultados,
                'total' => count($resultados)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetros incompletos'
            ]);
        }
    }

    /**
     * Obtener programas con calificaciones registradas (AJAX)
     */
    public function ObtenerProgramasConCalificacionesControlador()
    {
        $programas = CalificacionModelo::ObtenerProgramasConCalificacionesModelo();

        echo json_encode([
            'status' => 'success',
            'data' => $programas
        ]);
    }

    /**
     * Listar programas con calificaciones para SELECT
     */
    public function ListarProgramasCalificacionesSelectControlador()
    {
        $programas = CalificacionModelo::ObtenerProgramasConCalificacionesModelo();

        foreach ($programas as $programa) {
            echo '<option value="' . $programa['ProgramaID'] . '">' .
                 htmlspecialchars($programa['NombrePrograma']) . ' (' .
                 htmlspecialchars($programa['GradoAcademico']) . ')' .
                 '</option>';
        }
    }

    /**
     * Obtener información del estudiante logueado (AJAX)
     */
    public function ObtenerEstudianteLogueadoControlador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['Usuario'])) {
            $ci = $_SESSION['Usuario'];
            $estudiante = CalificacionModelo::ObtenerEstudiantePorCIModelo($ci);

            if ($estudiante) {
                // Agregar nombre completo
                $estudiante['NombreCompleto'] = $estudiante['Nombre'] . ' ' .
                                                 $estudiante['Apaterno'] . ' ' .
                                                 $estudiante['Amaterno'];
                $estudiante['CICompleto'] = $estudiante['Ci'];
                if (!empty($estudiante['Complemento'])) {
                    $estudiante['CICompleto'] .= '-' . $estudiante['Complemento'];
                }
                $estudiante['CICompleto'] .= ' ' . $estudiante['Exp'];

                echo json_encode([
                    'status' => 'success',
                    'data' => $estudiante
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se encontró un estudiante asociado a este usuario'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Sesión no válida'
            ]);
        }
    }

    /**
     * Obtener programas inscritos del estudiante logueado (AJAX)
     */
    public function ObtenerProgramasEstudianteControlador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar que la sesión tenga EstudianteID
        if (isset($_SESSION['EstudianteID']) && !empty($_SESSION['EstudianteID'])) {
            $estudianteID = $_SESSION['EstudianteID'];

            // Obtener datos completos del estudiante
            $estudiante = CalificacionModelo::ObtenerEstudiantePorIDModelo($estudianteID);

            if ($estudiante) {
                $programas = CalificacionModelo::ObtenerProgramasEstudianteModelo($estudianteID);

                echo json_encode([
                    'status' => 'success',
                    'data' => $programas,
                    'estudiante' => [
                        'EstudianteID' => $estudiante['EstudianteID'],
                        'NombreCompleto' => $estudiante['Nombre'] . ' ' .
                                           $estudiante['Apaterno'] . ' ' .
                                           $estudiante['Amaterno'],
                        'CI' => $estudiante['Ci']
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No se encontró un estudiante con ID: ' . $estudianteID
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No hay un estudiante asociado a esta sesión. Por favor, cierre sesión e inicie nuevamente.'
            ]);
        }
    }

    /**
     * Obtener calificaciones del estudiante por programa (AJAX)
     */
    public function ObtenerCalificacionesEstudianteProgramaControlador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_POST['programaID'])) {
            $programaID = intval($_POST['programaID']);

            // Verificar que la sesión tenga EstudianteID
            if (isset($_SESSION['EstudianteID']) && !empty($_SESSION['EstudianteID'])) {
                $estudianteID = $_SESSION['EstudianteID'];

                $calificaciones = CalificacionModelo::ObtenerCalificacionesEstudianteProgramaModelo(
                    $estudianteID,
                    $programaID
                );

                $resumen = CalificacionModelo::ObtenerResumenCalificacionesModelo(
                    $estudianteID,
                    $programaID
                );

                echo json_encode([
                    'status' => 'success',
                    'data' => $calificaciones,
                    'resumen' => $resumen
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No hay un estudiante asociado a esta sesión. Por favor, cierre sesión e inicie nuevamente.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de programa no especificado'
            ]);
        }
    }

    /**
     * Validar y cerrar un módulo (AJAX)
     */
    public function ValidarCerrarModuloControlador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_POST['moduloID'])) {
            $moduloID = intval($_POST['moduloID']);

            // Obtener el ID del usuario actual
            if (isset($_SESSION['Usuario'])) {
                $pdo = Conexion::Conectar();
                $stmt = $pdo->prepare("SELECT ID FROM usuario WHERE Usuario = :usuario LIMIT 1");
                $stmt->bindParam(":usuario", $_SESSION['Usuario'], PDO::PARAM_STR);
                $stmt->execute();
                $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userRow) {
                    $usuarioID = $userRow['ID'];
                    $resultado = CalificacionModelo::ValidarCerrarModuloModelo($moduloID, $usuarioID);
                    echo json_encode($resultado);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Usuario no encontrado'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Sesión no válida'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de módulo no especificado'
            ]);
        }
    }

    /**
     * Reabrir un módulo validado (AJAX)
     * Solo administradores
     */
    public function ReabrirModuloControlador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_POST['moduloID'])) {
            $moduloID = intval($_POST['moduloID']);

            // Obtener el ID del usuario actual
            if (isset($_SESSION['Usuario'])) {
                $pdo = Conexion::Conectar();
                $stmt = $pdo->prepare("SELECT ID FROM usuario WHERE Usuario = :usuario LIMIT 1");
                $stmt->bindParam(":usuario", $_SESSION['Usuario'], PDO::PARAM_STR);
                $stmt->execute();
                $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userRow) {
                    $usuarioID = $userRow['ID'];
                    $resultado = CalificacionModelo::ReabrirModuloModelo($moduloID, $usuarioID);
                    echo json_encode($resultado);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Usuario no encontrado'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Sesión no válida'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'ID de módulo no especificado'
            ]);
        }
    }

    /**
     * Verificar permiso de edición para un módulo (AJAX)
     */
    public function VerificarPermisoEdicionControlador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_POST['moduloID'])) {
            $moduloID = intval($_POST['moduloID']);

            // Obtener el ID del usuario actual
            if (isset($_SESSION['Usuario'])) {
                $pdo = Conexion::Conectar();
                $stmt = $pdo->prepare("SELECT ID, Tipo FROM usuario WHERE Usuario = :usuario LIMIT 1");
                $stmt->bindParam(":usuario", $_SESSION['Usuario'], PDO::PARAM_STR);
                $stmt->execute();
                $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userRow) {
                    $usuarioID = $userRow['ID'];
                    $resultado = CalificacionModelo::VerificarPermisoEdicionModelo($moduloID, $usuarioID);
                    $resultado['tipoUsuario'] = $userRow['Tipo'];
                    echo json_encode($resultado);
                } else {
                    echo json_encode([
                        'permitido' => false,
                        'mensaje' => 'Usuario no encontrado'
                    ]);
                }
            } else {
                echo json_encode([
                    'permitido' => false,
                    'mensaje' => 'Sesión no válida'
                ]);
            }
        } else {
            echo json_encode([
                'permitido' => false,
                'mensaje' => 'ID de módulo no especificado'
            ]);
        }
    }
}
?>
