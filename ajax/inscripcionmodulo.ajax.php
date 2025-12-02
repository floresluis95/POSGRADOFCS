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

// Cargar tabla HTML de estudiantes matriculados filtrada por programa
if (isset($_POST["accion"]) && $_POST["accion"] == "cargarTablaMatriculados") {
    $programaID = isset($_POST["programaIDFiltro"]) && $_POST["programaIDFiltro"] !== ''
        ? (int)$_POST["programaIDFiltro"]
        : null;

    // Obtener estudiantes del programa (filtrado o todos si es null)
    $estudiantes = InscripcionModuloModelos::ListarEstudiantesMatriculadosModelo($programaID);

    if (empty($estudiantes)) {
        $mensaje = $programaID ? ' para este programa' : '';
        echo '<tr>
                <td colspan="10" class="text-center text-muted">
                    <i class="fa fa-info-circle"></i> No hay estudiantes matriculados' . $mensaje . '
                </td>
              </tr>';
        exit();
    }

    foreach ($estudiantes as $key => $estudiante) {
        $nombreCompleto = $estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];
        $fechaFormateada = date('d/m/Y', strtotime($estudiante['FechaInscripcion']));

        // Determinar color del estado
        $colorEstado = $estudiante['Estado'] == 'ACTIVO' ? 'success' : 'danger';

        echo '<tr>
                <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                <td><strong>' . htmlspecialchars($nombreCompleto) . '</strong></td>
                <td class="text-center"><span class="badge badge-secondary">' . htmlspecialchars($estudiante['Ci']) . '</span></td>
                <td>' . htmlspecialchars($estudiante['NombrePrograma']) . '</td>
                <td class="text-center"><span class="badge badge-info">' . htmlspecialchars($estudiante['GradoAcademico']) . '</span></td>
                <td class="text-center"><span class="badge badge-primary">' . htmlspecialchars($estudiante['CodigoPrograma']) . '</span></td>
                <td class="text-center"><strong>Bs. ' . number_format($estudiante['costomatricula'], 2) . '</strong></td>
                <td class="text-center">' . htmlspecialchars($estudiante['nvauchermatricula']) . '</td>
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
                               data-estudiante-nombre="' . htmlspecialchars($nombreCompleto) . '">
                                <i class="fa fa-list text-success"></i> Ver Módulos Inscritos
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="vistas/componentes/recibo-pago-modulos.php?idinscripcion=' . $estudiante['idInscripcion'] . '" target="_blank">
                                <i class="fa fa-file-text text-success"></i> Ver Recibo de Pagos
                            </a>
                            <a class="dropdown-item" href="extensiones/tcpdf/pdf/pdfestudiante.php?id=' . $estudiante['EstudianteID'] . '" target="_blank">
                                <i class="fa fa-print text-warning"></i> Imprimir Información
                            </a>
                        </div>
                    </div>
                </td>
              </tr>';
    }
    exit();
}

// Si no hay acción válida
echo json_encode([
    'error' => true,
    'mensaje' => 'Acción no válida'
]);
?>
