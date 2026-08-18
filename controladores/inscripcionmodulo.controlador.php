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
                    <td colspan="12" class="text-center">No hay estudiantes matriculados</td>
                  </tr>';
            return;
        }

        foreach ($estudiantes as $key => $estudiante) {
            $nombreCompleto = $estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];
            $fechaFormateada = date('d/m/Y', strtotime($estudiante['FechaInscripcion']));

            // Determinar color del estado
            $colorEstado = $estudiante['Estado'] == 'ACTIVO' ? 'success' : 'danger';

            // Determinar tipo de pago y badge
            $pagoCompleto = isset($estudiante['pagoCompleto']) ? (int)$estudiante['pagoCompleto'] : 0;
            $montoPagado = isset($estudiante['montoPagado']) ? floatval($estudiante['montoPagado']) : 0;

            if ($pagoCompleto == 1) {
                $tipoPago = '<span class="badge badge-success" style="font-size: 11px; padding: 6px 12px;"><i class="fa fa-check-circle"></i> PAGO COMPLETO</span>';
            } else {
                $tipoPago = '<span class="badge badge-warning" style="font-size: 11px; padding: 6px 12px;"><i class="fa fa-money"></i> SOLO MATRÍCULA</span>';
            }

            // Mostrar monto: si es pago completo y tiene montoPagado válido, mostrarlo, sino costomatricula
            $montoMostrar = $estudiante['costomatricula'];
            if ($pagoCompleto == 1 && $montoPagado > 0) {
                $montoMostrar = $montoPagado;
            }

            // Mostrar descuento
            $montoDescuento = isset($estudiante['montoDescuento']) ? floatval($estudiante['montoDescuento']) : 0;
            if ($montoDescuento > 0) {
                $descuentoHTML = '<span style="color: #28a745; font-weight: bold;">Bs. ' . number_format($montoDescuento, 2) . '</span>';
            } else {
                $descuentoHTML = '<span style="color: #999;">-</span>';
            }

            echo '<tr>
                    <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                    <td><strong>' . $nombreCompleto . '</strong></td>
                    <td class="text-center"><span class="badge badge-secondary">' . $estudiante['Ci'] . '</span></td>
                    <td>' . $estudiante['NombrePrograma'] . '</td>
                    <td class="text-center"><span class="badge badge-info">' . $estudiante['GradoAcademico'] . '</span></td>
                    <td class="text-center"><span class="badge badge-primary">' . $estudiante['CodigoPrograma'] . '</span></td>
                    <td class="text-center"><strong>Bs. ' . number_format($montoMostrar, 2) . '</strong></td>
                    <td class="text-center">' . $descuentoHTML . '</td>
                    <td class="text-center">' . $tipoPago . '</td>
                    <td class="text-center">' . $estudiante['nvauchermatricula'] . '</td>
                    <td class="text-center">' . $fechaFormateada . '</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle btn-actions-dropdown"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-cog"></i> Acciones
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item btn-inscribir-modulo" href="#"
                                   data-estudiante-id="' . $estudiante['EstudianteID'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                   data-programa-id="' . $estudiante['ProgramaID'] . '"
                                   data-programa-nombre="' . htmlspecialchars($estudiante['NombrePrograma']) . '"
                                   data-idinscripcion="' . $estudiante['idInscripcion'] . '">
                                    <i class="fa fa-book text-primary"></i> Inscribir a Módulo
                                </a>
                                <a class="dropdown-item btn-plan-pago-programa" href="#"
                                   data-estudiante-id="' . $estudiante['EstudianteID'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                   data-estudiante-ci="' . $estudiante['Ci'] . '"
                                   data-programa-id="' . $estudiante['ProgramaID'] . '"
                                   data-programa-nombre="' . htmlspecialchars($estudiante['NombrePrograma']) . '"
                                   data-costo-programa="' . (float)($estudiante['CostoPrograma'] ?? 0) . '"
                                   data-idinscripcion="' . $estudiante['idInscripcion'] . '">
                                    <i class="fa fa-credit-card text-success"></i> Plan de Pago del Programa
                                </a>
                                <a class="dropdown-item btn-ver-detalles" href="#"
                                   data-estudiante-id="' . $estudiante['EstudianteID'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                   data-estudiante-ci="' . $estudiante['Ci'] . '"
                                   data-programa-nombre="' . htmlspecialchars($estudiante['NombrePrograma']) . '"
                                   data-grado="' . $estudiante['GradoAcademico'] . '"
                                   data-codigo="' . $estudiante['CodigoPrograma'] . '"
                                   data-costo="' . number_format($estudiante['costomatricula'], 2) . '"
                                   data-voucher="' . $estudiante['nvauchermatricula'] . '"
                                   data-fecha="' . $fechaFormateada . '">
                                    <i class="fa fa-eye text-info"></i> Ver Detalles
                                </a>
                                <a class="dropdown-item btn-ver-modulos" href="#"
                                   data-estudiante-id="' . $estudiante['EstudianteID'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                   data-idinscripcion="' . $estudiante['idInscripcion'] . '"
                                   data-programa-id="' . $estudiante['ProgramaID'] . '"
                                   data-programa-nombre="' . htmlspecialchars($estudiante['NombrePrograma']) . '">
                                    <i class="fa fa-list text-success"></i> Ver Módulos Inscritos
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="vistas/componentes/recibo-pago-modulos.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank">
                                    <i class="fa fa-file-text text-success"></i> Ver Recibo de Pagos
                                </a>
                                <a class="dropdown-item" href="extensiones/tcpdf/pdf/pdfestudiante.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank">
                                    <i class="fa fa-print text-warning"></i> Imprimir Ficha
                                </a>';

            // Agregar opción de comprobante de pago completo solo si pagoCompleto = 1
            if ($pagoCompleto == 1) {
                echo '
                                <a class="dropdown-item" href="extensiones/tcpdf/pdf/pdfpagocompletoprograma.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank" style="background-color: #e7f3ff;">
                                    <i class="fa fa-file-pdf-o text-primary"></i> <strong>Comprobante Pago Completo</strong>
                                </a>';
            }

            echo '
                            </div>
                        </div>
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
