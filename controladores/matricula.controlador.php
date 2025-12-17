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

            // Verificar si es pago completo
            $pagoCompleto = isset($_POST['pagoCompleto']) && $_POST['pagoCompleto'] == '1' ? 1 : 0;
            $costoTotalPrograma = isset($_POST['costoTotalPrograma']) ? floatval($_POST['costoTotalPrograma']) : 0;

            // Si es pago completo, el monto de matrícula es 0 (no se cobra)
            // El monto total pagado es el costo del programa
            if ($pagoCompleto) {
                $montoMatricula = 0; // No se cobra matrícula
                $montoPagado = $costoTotalPrograma; // Se paga el programa completo
            } else {
                $montoMatricula = floatval($_POST['montoMatricula']);
                $montoPagado = $montoMatricula; // Solo paga la matrícula
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
                "costomatricula" => $montoMatricula,
                "montoPagado" => $montoPagado,
                "pagoCompleto" => $pagoCompleto,
                "nvauchermatricula" => htmlspecialchars(trim($_POST['numeroVaucher'])),
                "FechaInscripcion" => htmlspecialchars(trim($_POST['fechaInscripcion'])),
                "foto" => $imagenBlob
            );

            error_log("Datos de matrícula preparados: " . print_r($datosMatricula, true));

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

            // Badge de pago completo
            $badgePagoCompleto = '';
            if (isset($inscripcion['pagoCompleto']) && $inscripcion['pagoCompleto'] == 1) {
                $badgePagoCompleto = '<br><span class="badge badge-success" style="font-size: 10px;"><i class="fa fa-check-circle"></i> PAGO COMPLETO</span>';
            }

            // Mostrar monto: si es pago completo, mostrar montoPagado, sino costomatricula
            $montoMostrar = isset($inscripcion['pagoCompleto']) && $inscripcion['pagoCompleto'] == 1
                ? $inscripcion['montoPagado']
                : $inscripcion['costomatricula'];

            echo '<tr>
                    <td class="text-center">' . $inscripcion['idInscripcion'] . '</td>
                    <td>' . $nombreCompleto . '</td>
                    <td class="text-center">' . $inscripcion['Ci'] . '</td>
                    <td>' . $inscripcion['NombrePrograma'] . $badgePagoCompleto . '</td>
                    <td class="text-center">' . $inscripcion['GradoAcademico'] . '</td>
                    <td class="text-center">Bs. ' . number_format($montoMostrar, 2) . '</td>
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
