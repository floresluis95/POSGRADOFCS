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
            <div class="col-xl-4 col-md-4">
                <div class="card text-white shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-weight: 600; letter-spacing: 1px;">Programas Activos</h6>
                                <h2 class="mb-0" style="font-size: 2.5rem; font-weight: 700;">' . $stats['totalProgramas'] . '</h2>
                            </div>
                            <div>
                                <i class="flaticon2-layers-1 fa-3x" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Módulos -->
            <div class="col-xl-4 col-md-4">
                <div class="card text-white shadow-sm" style="background: linear-gradient(135deg, #1dc9b7 0%, #0bb197 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-weight: 600; letter-spacing: 1px;">Módulos Disponibles</h6>
                                <h2 class="mb-0" style="font-size: 2.5rem; font-weight: 700;">' . $stats['totalModulos'] . '</h2>
                            </div>
                            <div>
                                <i class="flaticon2-file-1 fa-3x" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Estudiantes -->
            <div class="col-xl-4 col-md-4">
                <div class="card text-white shadow-sm" style="background: linear-gradient(135deg, #fd397a 0%, #e91e63 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-weight: 600; letter-spacing: 1px;">Estudiantes Inscritos</h6>
                                <h2 class="mb-0" style="font-size: 2.5rem; font-weight: 700;">' . $stats['totalEstudiantes'] . '</h2>
                            </div>
                            <div>
                                <i class="flaticon2-group fa-3x" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }

    /**
     * Mostrar tabla de programas con conteo de módulos e inscritos
     */
    public static function MostrarTablaProgramasConModulosControlador()
    {
        $programas = ReporteModulosModelo::ObtenerConteoModulosPorProgramaModelo();

        if (empty($programas)) {
            echo '<tr><td colspan="6" class="text-center">No hay programas registrados</td></tr>';
            return;
        }

        foreach ($programas as $programa) {
            echo '<tr>
                <td><strong>' . htmlspecialchars($programa['Codigo']) . '</strong></td>
                <td>' . htmlspecialchars($programa['NombrePrograma']) . '</td>
                <td class="text-center">
                    <span class="badge badge-secondary" style="font-size: 12px; padding: 6px 10px;">
                        ' . htmlspecialchars($programa['GradoAcademico']) . '
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 13px; padding: 8px 12px; border-radius: 20px;">
                        <i class="flaticon2-file-1"></i> ' . $programa['TotalModulos'] . ' módulos
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge" style="background: linear-gradient(135deg, #fd397a 0%, #e91e63 100%); color: white; font-size: 13px; padding: 8px 12px; border-radius: 20px;">
                        <i class="flaticon2-group"></i> ' . $programa['TotalInscritos'] . ' inscritos
                    </span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info ver-modulos-programa"
                            data-programa-id="' . $programa['ProgramaID'] . '"
                            data-programa-nombre="' . htmlspecialchars($programa['NombrePrograma']) . '"
                            style="border-radius: 20px; padding: 6px 15px;">
                        <i class="flaticon2-eye"></i> Ver Módulos
                    </button>
                </td>
            </tr>';
        }
    }
}
?>
