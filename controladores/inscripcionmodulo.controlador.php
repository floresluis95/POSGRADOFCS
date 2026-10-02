<?php
/**
 * Controlador de Inscripción a Módulos
 * Gestiona el registro de estudiantes en módulos
 */

require_once __DIR__ . '/../modelos/inscripcionmodulo.modelo.php';
require_once file_exists(__DIR__ . '/../modelos/conexion.modelo.php') ? __DIR__ . '/../modelos/conexion.modelo.php' : __DIR__ . '/../core/conexion.modelo.php';
require_once __DIR__ . '/../modelos/matricula.modelo.php';

class InscripcionModuloControladores
{
    /**
     * Listar estudiantes matriculados en programas
     * @param int|null $programaID
     * @param string|null $sede
     * @param string $estado 'ACTIVO' (por defecto), 'ABANDONO', o 'TODOS'
     */
    public function ListarEstudiantesMatriculadosControlador($programaID = null, $sede = null, $estado = 'ACTIVO', $requisitos = null)
    {
        $estudiantes = InscripcionModuloModelos::ListarEstudiantesMatriculadosModelo($programaID, $sede, $estado, $requisitos);

        if (empty($estudiantes)) {
            echo '<tr>
                    <td colspan="8" class="text-center">No hay estudiantes matriculados</td>
                  </tr>';
            return;
        }

        foreach ($estudiantes as $key => $estudiante) {
            $nombreCompleto = $estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];
            $fechaFormateada = date('d/m/Y', strtotime($estudiante['FechaInscripcion']));

            // Determinar color del estado
            $colorEstado = $estudiante['Estado'] == 'ACTIVO' ? 'success' : 'danger';

            // Badge de estado: solo se muestra si no está ACTIVO (evita ruido visual
            // en el caso normal, ya que por defecto la tabla solo lista activos)
            $badgeEstadoInscripcion = $estudiante['Estado'] !== 'ACTIVO'
                ? '<br><span class="badge badge-' . $colorEstado . '" style="font-size: 10px;">' . htmlspecialchars($estudiante['Estado']) . '</span>'
                : '';

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

            $montoDescuento = isset($estudiante['montoDescuento']) ? floatval($estudiante['montoDescuento']) : 0;
            $porcentajeDescuento = isset($estudiante['porcentajeDescuento']) ? floatval($estudiante['porcentajeDescuento']) : 0;

            // CI completo (con complemento y expedido), igual que en la ficha de inscripción
            $ciCompleto = $estudiante['Ci']
                . (!empty($estudiante['Complemento']) ? '-' . $estudiante['Complemento'] : '')
                . (!empty($estudiante['Exp']) ? ' ' . $estudiante['Exp'] : '');

            // La tabla solo muestra lo necesario para identificar al estudiante y su
            // situación de pago; el resto (contacto, montos, voucher, trámite...) va
            // en el modal "Ver Detalles", que lo recibe todo en este JSON.
            $detalle = [
                'nombre'        => $nombreCompleto,
                'ci'            => $ciCompleto,
                'celular'       => $estudiante['Celular'] ?? '',
                'correo'        => $estudiante['Correo'] ?? '',
                'profesion'     => $estudiante['NombreProfesion'] ?? '',
                'programa'      => $estudiante['NombrePrograma'],
                'grado'         => $estudiante['GradoAcademico'],
                'codigo'        => $estudiante['CodigoPrograma'],
                'version'       => $estudiante['Version'] ?? '',
                'sede'          => $estudiante['Sede'] ?? '',
                'tramite'       => $estudiante['NumeroTramite'] ?? '',
                'estado'        => $estudiante['Estado'],
                'fecha'         => $fechaFormateada,
                'costo'         => number_format((float)$estudiante['costomatricula'], 2),
                'descuento'     => $montoDescuento > 0 ? number_format($montoDescuento, 2) : '',
                'porcentaje'    => $porcentajeDescuento > 0 ? rtrim(rtrim(number_format($porcentajeDescuento, 2), '0'), '.') : '',
                'montoPagado'   => $montoPagado > 0 ? number_format($montoPagado, 2) : '',
                'pagoCompleto'  => $pagoCompleto,
                'voucher'       => $estudiante['nvauchermatricula'] ?? ''
            ];

            // Requisitos de matriculación entregados (obligatorios): abre la ventana "Requisitos"
            $reqTotal = (int)($estudiante['RequisitosTotal'] ?? 0);
            $reqEntregados = (int)($estudiante['RequisitosEntregados'] ?? 0);
            $reqCompleto = $reqEntregados >= $reqTotal;
            $celdaRequisitos = '<button type="button" class="mt-requisitos btn-requisitos ' . ($reqCompleto ? 'is-completo' : 'is-pendiente') . '"'
                . ' title="' . ($reqCompleto ? 'Requisitos completos' : 'Faltan ' . ($reqTotal - $reqEntregados) . ' requisito(s)') . '"'
                . ' data-idinscripcion="' . $estudiante['idInscripcion'] . '"'
                . ' data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '">'
                . '<i class="fa ' . ($reqCompleto ? 'fa-check-circle' : 'fa-clipboard-list') . '"></i> ' . $reqEntregados . '/' . $reqTotal
                . '</button>';

            echo '<tr>
                    <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                    <td>
                        <strong>' . $nombreCompleto . '</strong>' . $badgeEstadoInscripcion . '
                        <br><small class="text-muted"><i class="fa fa-id-card"></i> ' . htmlspecialchars($ciCompleto) . '</small>
                    </td>
                    <td>
                        ' . $estudiante['NombrePrograma'] . '
                        <br><span class="badge badge-info">' . $estudiante['GradoAcademico'] . '</span>
                        <span class="badge badge-primary">' . $estudiante['CodigoPrograma'] . '</span>
                    </td>
                    <td class="text-center">' . htmlspecialchars($estudiante['Sede'] ?? '') . '</td>
                    <td class="text-center">' . $tipoPago . '
                        <br><small><strong>Bs. ' . number_format($montoMostrar, 2) . '</strong></small>
                    </td>
                    <td class="text-center" data-order="' . $reqEntregados . '">' . $celdaRequisitos . '</td>
                    <td class="text-center" data-order="' . date('Y-m-d', strtotime($estudiante['FechaInscripcion'])) . '">' . $fechaFormateada . '</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success btn-sm btn-gestionar-pagos" title="Gestionar Pagos del Programa"
                                   data-estudiante-id="' . $estudiante['EstudianteID'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                   data-estudiante-ci="' . $estudiante['Ci'] . '"
                                   data-programa-id="' . $estudiante['ProgramaID'] . '"
                                   data-programa-nombre="' . htmlspecialchars($estudiante['NombrePrograma']) . '"
                                   data-costo-programa="' . (float)($estudiante['CostoPrograma'] ?? 0) . '"
                                   data-version="' . htmlspecialchars($estudiante['Version'] ?? '') . '"
                                   data-numero-tramite="' . htmlspecialchars($estudiante['NumeroTramite'] ?? '') . '"
                                   data-idinscripcion="' . $estudiante['idInscripcion'] . '">
                                <i class="fa fa-credit-card"></i> Pagos
                            </button>
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle btn-actions-dropdown"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-cog"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item btn-ver-detalles" href="#"
                                   data-detalle="' . htmlspecialchars(json_encode($detalle, JSON_UNESCAPED_UNICODE), ENT_QUOTES) . '">
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
                                <a class="dropdown-item btn-requisitos" href="#"
                                   data-idinscripcion="' . $estudiante['idInscripcion'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '">
                                    <i class="fa fa-clipboard-check"></i> Requisitos
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="vistas/componentes/recibo-pago-modulos.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank">
                                    <i class="fa fa-file-invoice-dollar text-success"></i> Ver Recibo de Pagos
                                </a>
                                <a class="dropdown-item" href="extensiones/tcpdf/pdf/pdfestudiante.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank">
                                    <i class="fa fa-print text-warning"></i> Imprimir Ficha
                                </a>
                                <a class="dropdown-item" href="extensiones/tcpdf/pdf/pdfcompromiso.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank">
                                    <i class="fa fa-file-signature text-info"></i> Documento de Compromiso
                                </a>';

            // Agregar opción de comprobante de pago completo solo si pagoCompleto = 1
            if ($pagoCompleto == 1) {
                echo '
                                <a class="dropdown-item" href="extensiones/tcpdf/pdf/pdfpagocompletoprograma.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank" style="background-color: #e7f3ff;">
                                    <i class="fa fa-file-pdf text-primary"></i> <strong>Comprobante Pago Completo</strong>
                                </a>';
            }

            echo '
                                <div class="dropdown-divider"></div>';

            // Marcar abandono / reactivar según el estado actual de la inscripción
            if ($estudiante['Estado'] == 'ACTIVO') {
                echo '
                                <a class="dropdown-item btn-marcar-abandono" href="#"
                                   data-idinscripcion="' . $estudiante['idInscripcion'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                   data-nuevo-estado="ABANDONO" style="color: #dc3545;">
                                    <i class="fa fa-user-times"></i> Marcar como Abandono
                                </a>';
            } else {
                echo '
                                <a class="dropdown-item btn-marcar-abandono" href="#"
                                   data-idinscripcion="' . $estudiante['idInscripcion'] . '"
                                   data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '"
                                   data-nuevo-estado="ACTIVO" style="color: #28a745;">
                                    <i class="fa fa-user-check"></i> Reactivar (marcar Activo)
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
     * Marcar/revertir el abandono de una inscripción (responde JSON).
     * Cambia estudianteprograma.Estado a 'ABANDONO' o de vuelta a 'ACTIVO'; el
     * historial de plan de pago y cuotas (pagoprograma/pagomodulo) no se toca.
     */
    public function ActualizarEstadoInscripcionAjaxControlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        $idInscripcion = isset($_POST['idinscripcion']) ? (int)$_POST['idinscripcion'] : 0;
        $nuevoEstado = isset($_POST['nuevoEstado']) ? trim($_POST['nuevoEstado']) : '';

        if (empty($idInscripcion) || !in_array($nuevoEstado, ['ACTIVO', 'ABANDONO'], true)) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
            exit();
        }

        $ok = MatriculaModelos::ActualizarEstadoInscripcionModelo($idInscripcion, $nuevoEstado);

        echo json_encode([
            'success' => $ok,
            'mensaje' => $ok ? 'Estado actualizado correctamente' : 'No se pudo actualizar el estado'
        ]);
        exit();
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
