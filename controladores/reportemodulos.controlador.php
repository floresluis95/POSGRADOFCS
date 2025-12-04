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
     * Mostrar panel de estadísticas (para la vista principal) - VERSIÓN COMPACTA
     */
    public static function MostrarPanelEstadisticasControlador()
    {
        $stats = ReporteModulosModelo::ObtenerEstadisticasGeneralesModelo();

        echo '
        <style>
        .stat-card-mini {
            background: white;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .stat-card-mini::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            transition: width 0.3s ease;
        }

        .stat-card-mini:hover::before {
            width: 100%;
            opacity: 0.08;
        }

        .stat-card-mini:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        }

        .stat-icon-mini {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }

        .stat-content-mini h4 {
            font-size: 11px;
            color: #636e72;
            margin-bottom: 4px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-content-mini .stat-number-mini {
            font-size: 22px;
            font-weight: 700;
            color: #2d3436;
        }

        .stat-card-mini:nth-child(1)::before { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card-mini:nth-child(2)::before { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card-mini:nth-child(3)::before { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stat-card-mini:nth-child(4)::before { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

        .stat-card-mini:nth-child(1) .stat-icon-mini { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card-mini:nth-child(2) .stat-icon-mini { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card-mini:nth-child(3) .stat-icon-mini { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stat-card-mini:nth-child(4) .stat-icon-mini { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        </style>

        <div class="row mb-3">
            <!-- Total Estudiantes -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-mini">
                    <div class="stat-icon-mini">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content-mini">
                        <h4>Estudiantes</h4>
                        <div class="stat-number-mini">' . $stats['totalEstudiantes'] . '</div>
                    </div>
                </div>
            </div>

            <!-- Total Programas -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-mini">
                    <div class="stat-icon-mini">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content-mini">
                        <h4>Programas</h4>
                        <div class="stat-number-mini">' . $stats['totalProgramas'] . '</div>
                    </div>
                </div>
            </div>

            <!-- Total Módulos -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-mini">
                    <div class="stat-icon-mini">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content-mini">
                        <h4>Módulos</h4>
                        <div class="stat-number-mini">' . $stats['totalModulos'] . '</div>
                    </div>
                </div>
            </div>

            <!-- Total Docentes -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card-mini">
                    <div class="stat-icon-mini">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content-mini">
                        <h4>Docentes</h4>
                        <div class="stat-number-mini">' . $stats['totalDocentes'] . '</div>
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

    /**
     * Mostrar estadística de programas por sede
     */
    public static function MostrarEstadisticaSedesControlador()
    {
        $sedes = ReporteModulosModelo::ObtenerProgramasPorSedeModelo();

        if (empty($sedes)) {
            echo '<div class="alert alert-warning">No hay sedes registradas</div>';
            return;
        }

        echo '<div class="row">';

        $colores = [
            ['bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 'icon' => 'flaticon2-location'],
            ['bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', 'icon' => 'flaticon2-pin'],
            ['bg' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)', 'icon' => 'flaticon2-map'],
            ['bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', 'icon' => 'flaticon2-placeholder']
        ];

        foreach ($sedes as $index => $sede) {
            $color = $colores[$index % count($colores)];

            echo '
            <div class="col-xl-' . (12 / max(count($sedes), 1)) . ' col-md-6 mb-4">
                <div class="card text-white shadow-sm" style="background: ' . $color['bg'] . '; border-radius: 15px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-weight: 600; letter-spacing: 1px; font-size: 11px; opacity: 0.9;">SEDE</h6>
                                <h3 class="mb-2" style="font-size: 1.8rem; font-weight: 700;">' . htmlspecialchars($sede['Sede']) . '</h3>
                                <div class="d-flex gap-3">
                                    <div>
                                        <i class="fas fa-graduation-cap"></i>
                                        <strong>' . $sede['TotalProgramas'] . '</strong> programas
                                    </div>
                                    <div>
                                        <i class="fas fa-layer-group"></i>
                                        <strong>' . $sede['TiposGrado'] . '</strong> tipos
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="' . $color['icon'] . ' fa-3x" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }

        echo '</div>';
    }

    /**
     * Mostrar estadísticas por categoría (Grado Académico) - COMPACTO
     */
    public static function MostrarEstadisticasCategoriasControlador()
    {
        $categorias = ReporteModulosModelo::ObtenerEstadisticasPorCategoriaModelo();

        if (empty($categorias)) {
            echo '<div class="alert alert-info">No hay categorías registradas</div>';
            return;
        }

        echo '<div class="row">';

        $colores = [
            'DIPLOMADO' => [
                'bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'icon' => 'fas fa-certificate'
            ],
            'MAESTRIA' => [
                'bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'icon' => 'fas fa-user-graduate'
            ],
            'ESPECIALIDAD' => [
                'bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'icon' => 'fas fa-graduation-cap'
            ]
        ];

        foreach ($categorias as $cat) {
            $grado = $cat['GradoAcademico'];
            $color = isset($colores[$grado]) ? $colores[$grado] : $colores['MAESTRIA'];

            echo '
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card text-white shadow-sm" style="background: ' . $color['bg'] . '; border-radius: 12px;">
                    <div class="card-body" style="padding: 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="flex: 1;">
                                <h6 class="text-uppercase mb-2" style="font-weight: 600; letter-spacing: 1px; font-size: 10px; opacity: 0.9;">CATEGORÍA</h6>
                                <h4 class="mb-2" style="font-size: 1.3rem; font-weight: 700;">' . htmlspecialchars($grado) . '</h4>
                                <div style="font-size: 12px;">
                                    <div class="mb-1">
                                        <i class="fas fa-list-alt"></i> <strong>' . $cat['TotalProgramas'] . '</strong> programas
                                    </div>
                                    <div class="mb-1">
                                        <i class="fas fa-users"></i> <strong>' . $cat['TotalEstudiantes'] . '</strong> estudiantes
                                    </div>
                                    <div>
                                        <i class="fas fa-book"></i> <strong>' . $cat['TotalModulos'] . '</strong> módulos
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="' . $color['icon'] . ' fa-2x" style="opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }

        echo '</div>';
    }
}
?>
