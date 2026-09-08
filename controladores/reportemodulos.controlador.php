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
     * Obtener reporte de pagos por programa (para el panel de reportes de pagos)
     */
    public static function ObtenerReportePagosPorProgramaControlador()
    {
        $grado = isset($_POST['grado']) && $_POST['grado'] !== ''
            ? htmlspecialchars(trim($_POST['grado']))
            : null;

        $reporte = ReporteModulosModelo::ObtenerReportePagosPorProgramaModelo($grado);
        echo json_encode($reporte);
    }

    /**
     * Obtener el detalle de pagos por estudiante de un programa específico
     */
    public static function ObtenerReportePagosPorEstudianteControlador()
    {
        if (isset($_POST['programaID'])) {
            $programaID = (int)$_POST['programaID'];
            $detalle = ReporteModulosModelo::ObtenerReportePagosPorEstudianteModelo($programaID);
            echo json_encode($detalle);
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
     * Mostrar panel de estadísticas (para la vista principal) - VERSIÓN COMPACTA
     */
    public static function MostrarPanelEstadisticasControlador()
    {
        $stats = ReporteModulosModelo::ObtenerEstadisticasGeneralesModelo();

        echo '
        <style>
        .stat-card-mini {
            background: #fff;
            border: 1px solid #EBEDF3;
            border-left: 3px solid #5867dd;
            border-radius: 4px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .stat-icon-mini {
            width: 34px;
            height: 34px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
        }

        .stat-content-mini h4 {
            font-size: 10px;
            color: #74788d;
            margin-bottom: 2px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .stat-content-mini .stat-number-mini {
            font-size: 18px;
            font-weight: 700;
            color: #2d3436;
        }

        .stat-card-mini:nth-child(1) { border-left-color: #5867dd; }
        .stat-card-mini:nth-child(2) { border-left-color: #34bfa3; }
        .stat-card-mini:nth-child(3) { border-left-color: #575962; }

        .stat-card-mini:nth-child(1) .stat-icon-mini { background: #5867dd; }
        .stat-card-mini:nth-child(2) .stat-icon-mini { background: #34bfa3; }
        .stat-card-mini:nth-child(3) .stat-icon-mini { background: #575962; }
        </style>

        <div class="row mb-2">
            <!-- Total Estudiantes -->
            <div class="col-xl-4 col-md-4">
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
            <div class="col-xl-4 col-md-4">
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

            <!-- Total Docentes -->
            <div class="col-xl-4 col-md-4">
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
     * Mostrar programas categorizados por grado académico en bloques
     */
    public static function MostrarTablaProgramasConModulosControlador()
    {
        $programas = ReporteModulosModelo::ObtenerConteoModulosPorProgramaModelo();

        if (empty($programas)) {
            echo '<div class="alert alert-warning text-center">No hay programas registrados</div>';
            return;
        }

        // Agrupar por grado académico
        $porGrado = [];
        foreach ($programas as $programa) {
            $grado = $programa['GradoAcademico'];
            if (!isset($porGrado[$grado])) {
                $porGrado[$grado] = [];
            }
            $porGrado[$grado][] = $programa;
        }

        // Colores por grado (paleta formal, un solo tono por categoría)
        $coloresGrado = [
            'MAESTRIA' => ['color' => '#5867dd', 'icon' => 'fas fa-user-graduate'],
            'DIPLOMADO' => ['color' => '#33c1ce', 'icon' => 'fas fa-certificate'],
            'ESPECIALIDAD' => ['color' => '#e14e7d', 'icon' => 'fas fa-graduation-cap'],
            'CURSO' => ['color' => '#34bfa3', 'icon' => 'fas fa-book-reader']
        ];

        // Mostrar cada categoría
        foreach ($porGrado as $grado => $programasGrado) {
            $color = isset($coloresGrado[$grado]) ? $coloresGrado[$grado] : $coloresGrado['MAESTRIA'];

            echo '
            <div class="categoria-grado mb-3">
                <div class="categoria-header" style="background: #f8f9fa; border-left: 3px solid ' . $color['color'] . '; padding: 8px 15px; margin-bottom: 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 style="color: #3b3f5c; margin: 0; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .3px;">
                            <i class="' . $color['icon'] . '" style="color: ' . $color['color'] . ';"></i> ' . htmlspecialchars($grado) . '
                        </h4>
                        <span class="badge badge-light" style="font-size: 11px; padding: 4px 10px; color: #74788d;">
                            ' . count($programasGrado) . ' programa(s)
                        </span>
                    </div>
                </div>
                <div class="programas-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 10px; padding: 15px; background: #fff; border: 1px solid #EBEDF3; border-top: none;">';

            foreach ($programasGrado as $programa) {
                echo '
                    <div class="programa-card" style="background: #fff; border-radius: 4px; padding: 12px; border: 1px solid #EBEDF3; border-left: 3px solid ' . $color['color'] . ';">
                        <div class="programa-codigo mb-2">
                            <span style="background: #f1f3f4; color: ' . $color['color'] . '; padding: 3px 9px; border-radius: 3px; font-size: 10px; font-weight: 700;">
                                ' . htmlspecialchars($programa['Codigo']) . '
                            </span>
                        </div>

                        <h5 class="programa-nombre mb-2" style="color: #3b3f5c; font-weight: 600; font-size: 13px; line-height: 1.35; min-height: 34px;">
                            ' . htmlspecialchars($programa['NombrePrograma']) . '
                        </h5>

                        <div class="programa-stats mb-2" style="display: flex; gap: 8px; justify-content: space-between;">
                            <div class="stat-item" style="flex: 1; text-align: center; background: #f8f9fa; padding: 6px; border-radius: 3px;">
                                <div style="color: ' . $color['color'] . '; font-size: 15px; font-weight: 700;">
                                    ' . $programa['TotalModulos'] . '
                                </div>
                                <div style="color: #B5B5C3; font-size: 9px; text-transform: uppercase; font-weight: 600;">
                                    Módulos
                                </div>
                            </div>
                            <div class="stat-item" style="flex: 1; text-align: center; background: #f8f9fa; padding: 6px; border-radius: 3px;">
                                <div style="color: #575962; font-size: 15px; font-weight: 700;">
                                    ' . $programa['TotalInscritos'] . '
                                </div>
                                <div style="color: #B5B5C3; font-size: 9px; text-transform: uppercase; font-weight: 600;">
                                    Inscritos
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-sm btn-block ver-modulos-programa"
                                data-programa-id="' . $programa['ProgramaID'] . '"
                                data-programa-nombre="' . htmlspecialchars($programa['NombrePrograma']) . '"
                                style="background: #fff; color: ' . $color['color'] . '; border: 1px solid ' . $color['color'] . '; border-radius: 3px; padding: 6px; font-size: 11px; font-weight: 600;">
                            <i class="flaticon2-eye"></i> Ver Módulos Detallados
                        </button>
                    </div>';
            }

            echo '
                </div>
            </div>';
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

        $colores = ['#5867dd', '#e14e7d', '#34bfa3', '#33c1ce'];
        $iconos = ['flaticon2-location', 'flaticon2-pin', 'flaticon2-map', 'flaticon2-placeholder'];

        foreach ($sedes as $index => $sede) {
            $color = $colores[$index % count($colores)];
            $icono = $iconos[$index % count($iconos)];

            echo '
            <div class="col-xl-' . (12 / max(count($sedes), 1)) . ' col-md-6 mb-3">
                <div class="card shadow-sm" style="border: 1px solid #EBEDF3; border-left: 3px solid ' . $color . '; border-radius: 4px;">
                    <div class="card-body" style="padding: 14px 16px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase mb-1" style="font-weight: 600; letter-spacing: .5px; font-size: 10px; color: #B5B5C3;">SEDE</h6>
                                <h3 class="mb-2" style="font-size: 1.2rem; font-weight: 700; color: #3b3f5c;">' . htmlspecialchars($sede['Sede']) . '</h3>
                                <div class="d-flex gap-3" style="font-size: 12px; color: #575962;">
                                    <div>
                                        <i class="fas fa-graduation-cap" style="color: ' . $color . ';"></i>
                                        <strong>' . $sede['TotalProgramas'] . '</strong> programas
                                    </div>
                                    <div>
                                        <i class="fas fa-layer-group" style="color: ' . $color . ';"></i>
                                        <strong>' . $sede['TiposGrado'] . '</strong> tipos
                                    </div>
                                </div>
                            </div>
                            <div>
                                <i class="' . $icono . '" style="color: ' . $color . '; opacity: 0.4; font-size: 1.8rem;"></i>
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
