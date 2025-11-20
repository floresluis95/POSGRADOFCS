<?php
/**
 * Controlador de Inscripción a Módulos
 * Gestiona el registro de estudiantes en módulos
 */

require_once __DIR__ . '/../modelos/inscripcionmodulo.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

class InscripcionModuloControladores
{
    /**
     * Listar estudiantes matriculados en programas
     */
    public function ListarEstudiantesMatriculadosControlador()
    {
        $estudiantes = InscripcionModuloModelos::ListarEstudiantesMatriculadosModelo();

        if (empty($estudiantes)) {
            echo '<tr>
                    <td colspan="10" class="text-center">No hay estudiantes matriculados</td>
                  </tr>';
            return;
        }

        foreach ($estudiantes as $key => $estudiante) {
            $nombreCompleto = $estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];
            $fechaFormateada = date('d/m/Y', strtotime($estudiante['FechaInscripcion']));

            // Determinar color del estado
            $colorEstado = $estudiante['Estado'] == 'ACTIVO' ? 'success' : 'danger';

            echo '<tr>
                    <td class="text-center">' . ($key + 1) . '</td>
                    <td>' . $nombreCompleto . '</td>
                    <td class="text-center">' . $estudiante['Ci'] . '</td>
                    <td>' . $estudiante['NombrePrograma'] . '</td>
                    <td class="text-center">' . $estudiante['GradoAcademico'] . '</td>
                    <td class="text-center">' . $estudiante['CodigoPrograma'] . '</td>
                    <td class="text-center">Bs. ' . number_format($estudiante['costomatricula'], 2) . '</td>
                    <td class="text-center">' . $estudiante['nvauchermatricula'] . '</td>
                    <td class="text-center">' . $fechaFormateada . '</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-primary btn-sm btn-inscribir-modulo"
                                data-estudiante-id="' . $estudiante['EstudianteID'] . '"
                                data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                data-programa-id="' . $estudiante['ProgramaID'] . '"
                                data-programa-nombre="' . htmlspecialchars($estudiante['NombrePrograma']) . '"
                                title="Inscribir a Módulo">
                            <i class="fa fa-book"></i> Inscribir a Módulo
                        </button>
                    </td>
                  </tr>';
        }
    }

    /**
     * Registrar inscripción a módulo
     */
    public function RegistrarInscripcionModuloControlador()
    {
        error_log("=== RegistrarInscripcionModuloControlador ejecutado ===");
        error_log("POST data: " . print_r($_POST, true));

        if (isset($_POST["registrarInscripcionModulo"])) {
            error_log("POST registrarInscripcionModulo detectado");

            // Validar datos requeridos
            if (empty($_POST['estudianteID']) || empty($_POST['moduloID']) ||
                empty($_POST['costoModulo']) || empty($_POST['numeroVaucherModulo']) ||
                empty($_POST['fechaInscripcionModulo'])) {

                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Todos los campos son obligatorios", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
                return;
            }

            // Preparar datos para insertar
            $datosInscripcion = array(
                "EstudianteID" => (int)$_POST['estudianteID'],
                "ModuloID" => (int)$_POST['moduloID'],
                "costo" => $_POST['costoModulo'],
                "nvaucher" => $_POST['numeroVaucherModulo'],
                "fecha" => htmlspecialchars(trim($_POST['fechaInscripcionModulo']))
            );

            // Registrar en la base de datos
            $resultado = InscripcionModuloModelos::RegistrarInscripcionModuloModelo($datosInscripcion);

            if ($resultado == 'exitoso') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "La inscripción al módulo se registró correctamente", "success")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            } elseif ($resultado == 'duplicado') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "El estudiante ya está inscrito en este módulo", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo registrar la inscripción al módulo", "error")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            }
        }
    }
}
?>
