<?php
/**
 * Controlador de Reportes de Módulos
 * Gestiona la generación de reportes de inscritos por módulo
 */

require_once __DIR__ . '/../modelos/reportemodulos.modelo.php';

class ReporteModulosControlador
{
    /**
     * Obtener grados académicos disponibles
     */
    public static function ObtenerGradosAcademicosControlador()
    {
        $grados = ReporteModulosModelo::ObtenerGradosAcademicosModelo();
        echo json_encode($grados);
    }

    /**
     * Obtener programas por grado académico
     */
    public static function ObtenerProgramasPorGradoControlador()
    {
        if (isset($_POST['grado'])) {
            $grado = htmlspecialchars(trim($_POST['grado']));
            $programas = ReporteModulosModelo::ObtenerProgramasPorGradoModelo($grado);
            echo json_encode($programas);
        } else {
            echo json_encode(['error' => 'Parámetro grado requerido']);
        }
    }

    /**
     * Obtener módulos por programa
     */
    public static function ObtenerModulosPorProgramaControlador()
    {
        if (isset($_POST['programaID'])) {
            $programaID = (int)$_POST['programaID'];
            $modulos = ReporteModulosModelo::ObtenerModulosPorProgramaModelo($programaID);
            echo json_encode($modulos);
        } else {
            echo json_encode(['error' => 'Parámetro programaID requerido']);
        }
    }

    /**
     * Obtener inscritos (reporte)
     */
    public static function ObtenerInscritosReporteControlador()
    {
        if (isset($_POST['programaID'])) {
            $programaID = (int)$_POST['programaID'];
            $moduloID = isset($_POST['moduloID']) && !empty($_POST['moduloID'])
                ? (int)$_POST['moduloID']
                : null;

            $inscritos = ReporteModulosModelo::ObtenerInscritosPorProgramaModelo($programaID, $moduloID);
            echo json_encode($inscritos);
        } else {
            echo json_encode(['error' => 'Parámetro programaID requerido']);
        }
    }

    /**
     * Obtener estadísticas generales
     */
    public static function ObtenerEstadisticasGeneralesControlador()
    {
        $estadisticas = ReporteModulosModelo::ObtenerEstadisticasGeneralesModelo();
        echo json_encode($estadisticas);
    }

    /**
     * Obtener conteo de módulos por programa
     */
    public static function ObtenerConteoModulosPorProgramaControlador()
    {
        $conteo = ReporteModulosModelo::ObtenerConteoModulosPorProgramaModelo();
        echo json_encode($conteo);
    }

    /**
     * Obtener conteo de inscritos por módulo
     */
    public static function ObtenerConteoInscritosPorModuloControlador()
    {
        if (isset($_POST['programaID'])) {
            $programaID = (int)$_POST['programaID'];
            $conteo = ReporteModulosModelo::ObtenerConteoInscritosPorModuloModelo($programaID);
            echo json_encode($conteo);
        } else {
            echo json_encode(['error' => 'Parámetro programaID requerido']);
        }
    }

    /**
     * Mostrar panel de estadísticas (para la vista principal)
     */
    public static function MostrarPanelEstadisticasControlador()
    {
        $stats = ReporteModulosModelo::ObtenerEstadisticasGeneralesModelo();

        echo '
        <div class="row mb-4">
            <!-- Total Programas -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Programas</h6>
                                <h2 class="mb-0">' . $stats['totalProgramas'] . '</h2>
                            </div>
                            <div>
                                <i class="fa fa-book fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Módulos -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Módulos</h6>
                                <h2 class="mb-0">' . $stats['totalModulos'] . '</h2>
                            </div>
                            <div>
                                <i class="fa fa-list fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Estudiantes -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Estudiantes</h6>
                                <h2 class="mb-0">' . $stats['totalEstudiantes'] . '</h2>
                            </div>
                            <div>
                                <i class="fa fa-users fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Pagos -->
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1">Pagos Registrados</h6>
                                <h2 class="mb-0">' . $stats['totalPagos'] . '</h2>
                            </div>
                            <div>
                                <i class="fa fa-dollar-sign fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     * Mostrar tabla de programas con conteo de módulos
     */
    public static function MostrarTablaProgramasConModulosControlador()
    {
        $programas = ReporteModulosModelo::ObtenerConteoModulosPorProgramaModelo();

        if (empty($programas)) {
            echo '<tr><td colspan="5" class="text-center">No hay programas registrados</td></tr>';
            return;
        }

        foreach ($programas as $programa) {
            echo '<tr>
                <td>' . htmlspecialchars($programa['Codigo']) . '</td>
                <td>' . htmlspecialchars($programa['NombrePrograma']) . '</td>
                <td class="text-center">' . htmlspecialchars($programa['GradoAcademico']) . '</td>
                <td class="text-center">
                    <span class="badge badge-primary" style="font-size: 14px; padding: 8px 12px;">
                        ' . $programa['TotalModulos'] . ' módulos
                    </span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info ver-modulos-programa"
                            data-programa-id="' . $programa['ProgramaID'] . '"
                            data-programa-nombre="' . htmlspecialchars($programa['NombrePrograma']) . '">
                        <i class="fa fa-eye"></i> Ver Módulos
                    </button>
                </td>
            </tr>';
        }
    }
}
?>
