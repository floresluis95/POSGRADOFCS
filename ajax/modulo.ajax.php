<?php
/**
 * AJAX para obtener módulos por programa
 */

require_once "../modelos/modulopagos_core.php";
require_once "../modelos/modulo.modelo.php";

// Obtener módulos con estado de pago por inscripción
if (isset($_POST["programaID"]) && isset($_POST["idinscripcion"])) {
    $programaID = (int)$_POST["programaID"];
    $idinscripcion = (int)$_POST["idinscripcion"];

    $modulos = PagoModuloModelo::ObtenerModulosConEstadoPagoModelo($programaID, $idinscripcion);

    echo json_encode($modulos);
    exit();
}

// Obtener módulos directamente por ProgramaId (desde tabla modulos)
if (isset($_POST["accion"]) && $_POST["accion"] == "obtenerModulosPorProgramaId") {
    $programaID = (int)$_POST["programaID"];

    $modulos = ModuloModelo::ObtenerModulosPorProgramaIdModelo($programaID);

    echo json_encode($modulos);
    exit();
}

// Obtener módulos por programa (sin estado de pago) - para compatibilidad
if (isset($_POST["programaID"])) {
    $programaID = (int)$_POST["programaID"];
    $modulos = PagoModuloModelo::ObtenerModulosPorProgramaModelo($programaID);

    echo json_encode($modulos);
    exit();
}

// Cargar tabla HTML de módulos filtrada por programa
if (isset($_POST["accion"]) && $_POST["accion"] == "cargarTablaModulos") {
    $programaID = isset($_POST["programaIDFiltro"]) ? (int)$_POST["programaIDFiltro"] : null;

    // Obtener módulos del programa (filtrado o todos si es null)
    $modulos = ModuloModelo::ListarModulosPorProgramaModelo($programaID);

    if (empty($modulos)) {
        echo '<tr>
                <td colspan="9" class="text-center text-muted">
                    <i class="fa fa-info-circle"></i> No hay módulos registrados' . ($programaID ? ' para este programa' : '') . '
                </td>
              </tr>';
        exit();
    }

    $hoy = date('Y-m-d');

    foreach ($modulos as $key => $modulo) {
        $estadoBadge = $modulo['estadomodulo'] == 'ACTIVO' ? 'badge-success' : 'badge-secondary';

        // Información del docente
        $docenteInfo = '<span class="text-muted"><em>Sin asignar</em></span>';
        if (!empty($modulo['NombreDocente'])) {
            $docenteInfo = '<strong>' . htmlspecialchars($modulo['NombreDocente']) . '</strong>';
            if (!empty($modulo['EspecialidadDocente'])) {
                $docenteInfo .= '<br><small class="text-muted">' . htmlspecialchars($modulo['EspecialidadDocente']) . '</small>';
            }
        }

        // Columna de fechas del módulo
        if (!empty($modulo['FechaInicio']) && !empty($modulo['FechaFinal'])) {
            $fechaInicioFmt = date('d/m/Y', strtotime($modulo['FechaInicio']));
            $fechaFinFmt = date('d/m/Y', strtotime($modulo['FechaFinal']));
            $fechasInfo = '<i class="fa fa-calendar"></i> ' . $fechaInicioFmt .
                          '<br><i class="fa fa-calendar-check-o"></i> ' . $fechaFinFmt;
        } else {
            $fechasInfo = '<span class="text-muted"><em>Sin definir</em></span>';
        }

        // Estado del módulo según el rango de fechas (curso / cerrado / culminado)
        if (!empty($modulo['FechaInicio']) && !empty($modulo['FechaFinal'])) {
            if ($hoy < $modulo['FechaInicio']) {
                $estadoFechaTexto = 'CERRADO';
                $estadoFechaBadge = 'badge-secondary';
            } elseif ($hoy > $modulo['FechaFinal']) {
                $estadoFechaTexto = 'CULMINADO';
                $estadoFechaBadge = 'badge-info';
            } else {
                $estadoFechaTexto = 'EN CURSO';
                $estadoFechaBadge = 'badge-success';
            }
        } else {
            $estadoFechaTexto = 'SIN FECHAS';
            $estadoFechaBadge = 'badge-secondary';
        }

        // Datos para el botón de editar (en JSON para pasar al modal)
        $datosModulo = htmlspecialchars(json_encode([
            'idmodulo' => $modulo['Idmodulo'],
            'programaId' => $modulo['ProgramaId'],
            'nombremodulo' => $modulo['nombremodulo'],
            'codigomodulo' => $modulo['codigomodulo'],
            'costomodulo' => $modulo['costomodulo'],
            'docenteID' => $modulo['DocenteID'],
            'nombrePrograma' => $modulo['NombrePrograma'],
            'fechaInicio' => $modulo['FechaInicio'],
            'fechaFinal' => $modulo['FechaFinal']
        ]), ENT_QUOTES, 'UTF-8');

        echo '<tr>
                <td class="text-center"><strong>' . ($key + 1) . '</strong></td>
                <td class="text-center"><span class="badge badge-primary">' . htmlspecialchars($modulo['codigomodulo']) . '</span></td>
                <td><strong>' . htmlspecialchars($modulo['nombremodulo']) . '</strong></td>
                <td>' . htmlspecialchars($modulo['NombrePrograma']) . '</td>
                <td class="text-center">' . $fechasInfo . '</td>
                <td class="text-center">
                    <span class="badge ' . $estadoFechaBadge . '">' . $estadoFechaTexto . '</span>
                </td>
                <td>' . $docenteInfo . '</td>
                <td class="text-center">
                    <span class="badge ' . $estadoBadge . '">' . htmlspecialchars($modulo['estadomodulo']) . '</span>
                </td>
                <td class="text-center">
                    <button type="button"
                            class="btn btn-sm btn-warning btn-editar-modulo"
                            data-modulo=\'' . $datosModulo . '\'
                            title="Editar módulo">
                        <i class="fa fa-edit"></i>
                    </button>
                </td>
              </tr>';
    }
    exit();
}
?>
