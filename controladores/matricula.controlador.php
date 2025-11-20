<?php
/**
 * Controlador de Matrícula/Inscripción
 * Gestiona el registro de matriculaciones
 */

require_once __DIR__ . '/../modelos/matricula.modelo.php';
require_once __DIR__ . '/../modelos/conexion.modelo.php';

class MatriculaControladores
{
    /**
     * Registrar nueva matrícula/inscripción
     */
    public function RegistrarMatriculaControlador()
    {
        // DEBUG: Ver si se está ejecutando
        error_log("=== RegistrarMatriculaControlador ejecutado ===");
        error_log("POST data: " . print_r($_POST, true));

        if (isset($_POST["registrarMatricula"])) {
            error_log("POST registrarMatricula detectado");

            // Validar datos requeridos
            if (empty($_POST['idcliente']) || empty($_POST['programa']) ||
                empty($_POST['montoMatricula']) || empty($_POST['numeroVaucher']) ||
                empty($_POST['fechaInscripcion'])) {

                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "Todos los campos son obligatorios", "error")
                .then(function () {
                    location.href="inscripcion";
                });
                </script>';
                return;
            }

            // Procesar imagen (opcional)
            $imagenBlob = null;
            if (isset($_FILES['comprobanteImagen']) && $_FILES['comprobanteImagen']['error'] == UPLOAD_ERR_OK) {
                // Validar tipo de archivo
                $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
                $tipoArchivo = $_FILES['comprobanteImagen']['type'];

                if (!in_array($tipoArchivo, $tiposPermitidos)) {
                    echo '
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ERROR!", "Formato de archivo no permitido. Solo JPG, PNG, GIF o PDF", "error")
                    .then(function () {
                        location.href="inscripcion";
                    });
                    </script>';
                    return;
                }

                // Validar tamaño (máximo 5MB)
                $tamañoMaximo = 5 * 1024 * 1024; // 5MB
                if ($_FILES['comprobanteImagen']['size'] > $tamañoMaximo) {
                    echo '
                    <script src="vistas/recursos/sweetalert.min.js"></script>
                    <script>
                    swal("ERROR!", "El archivo es muy grande. Máximo 5MB", "error")
                    .then(function () {
                        location.href="inscripcion";
                    });
                    </script>';
                    return;
                }

                // Leer el archivo como BLOB
                $imagenBlob = file_get_contents($_FILES['comprobanteImagen']['tmp_name']);
            }

            // Preparar datos para insertar
            $datosMatricula = array(
                "EstudianteID" => (int)$_POST['idcliente'],
                "ProgramaID" => (int)$_POST['programa'],
                "costomatricula" => (int)$_POST['montoMatricula'],
                "nvauchermatricula" => (int)$_POST['numeroVaucher'],
                "FechaInscripcion" => htmlspecialchars(trim($_POST['fechaInscripcion'])),
                "foto" => $imagenBlob
            );

            // Registrar en la base de datos
            $resultado = MatriculaModelos::RegistrarMatriculaModelo($datosMatricula);

            if ($resultado == 'exitoso') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("EXITOSO!", "La matrícula se registró correctamente", "success")
                .then(function () {
                    location.href="matriculados";
                });
                </script>';
            } elseif ($resultado == 'duplicado') {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "El estudiante ya está inscrito en este programa", "error")
                .then(function () {
                    location.href="inscripcion";
                });
                </script>';
            } else {
                echo '
                <script src="vistas/recursos/sweetalert.min.js"></script>
                <script>
                swal("ERROR!", "No se pudo registrar la matrícula", "error")
                .then(function () {
                    location.href="inscripcion";
                });
                </script>';
            }
        }
    }

    /**
     * Listar todas las inscripciones
     */
    public function ListarInscripcionesControlador()
    {
        $inscripciones = MatriculaModelos::ListarInscripcionesModelo();

        if (empty($inscripciones)) {
            echo '<tr>
                    <td colspan="8" class="text-center">No hay inscripciones registradas</td>
                  </tr>';
            return;
        }

        foreach ($inscripciones as $key => $inscripcion) {
            $nombreCompleto = $inscripcion['Nombre'] . ' ' . $inscripcion['Apaterno'] . ' ' . $inscripcion['Amaterno'];
            $fechaFormateada = date('d/m/Y', strtotime($inscripcion['FechaInscripcion']));

            // Determinar color del estado
            $colorEstado = $inscripcion['Estado'] == 'ACTIVO' ? 'success' : 'danger';

            echo '<tr>
                    <td class="text-center">' . $inscripcion['idInscripcion'] . '</td>
                    <td>' . $nombreCompleto . '</td>
                    <td class="text-center">' . $inscripcion['Ci'] . '</td>
                    <td>' . $inscripcion['NombrePrograma'] . '</td>
                    <td class="text-center">' . $inscripcion['GradoAcademico'] . '</td>
                    <td class="text-center">Bs. ' . number_format($inscripcion['costomatricula'], 2) . '</td>
                    <td class="text-center">' . $fechaFormateada . '</td>
                    <td class="text-center">
                        <span class="badge badge-' . $colorEstado . '">' . $inscripcion['Estado'] . '</span>
                    </td>
                  </tr>';
        }
    }

    /**
     * Obtener inscripciones por estudiante
     */
    public function ObtenerInscripcionesPorEstudianteControlador($estudianteID)
    {
        $inscripciones = MatriculaModelos::ObtenerInscripcionesPorEstudianteModelo($estudianteID);
        return $inscripciones;
    }
}
