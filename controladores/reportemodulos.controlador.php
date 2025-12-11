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

        // Colores por grado
        $coloresGrado = [
            'MAESTRIA' => [
                'bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'border' => '#667eea',
                'icon' => 'fas fa-user-graduate'
            ],
            'DIPLOMADO' => [
                'bg' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'border' => '#4facfe',
                'icon' => 'fas fa-certificate'
            ],
            'ESPECIALIDAD' => [
                'bg' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'border' => '#f093fb',
                'icon' => 'fas fa-graduation-cap'
            ],
            'CURSO' => [
                'bg' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                'border' => '#43e97b',
                'icon' => 'fas fa-book-reader'
            ]
        ];

        // Mostrar cada categoría
        foreach ($porGrado as $grado => $programasGrado) {
            $color = isset($coloresGrado[$grado]) ? $coloresGrado[$grado] : $coloresGrado['MAESTRIA'];

            echo '
            <div class="categoria-grado mb-4">
                <div class="categoria-header" style="background: ' . $color['bg'] . '; padding: 12px 20px; border-radius: 10px 10px 0 0; margin-bottom: 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 style="color: white; margin: 0; font-weight: 700; font-size: 16px;">
                            <i class="' . $color['icon'] . '"></i> ' . htmlspecialchars($grado) . '
                        </h4>
                        <span class="badge badge-light" style="font-size: 13px; padding: 6px 12px;">
                            ' . count($programasGrado) . ' programa(s)
                        </span>
                    </div>
                </div>
                <div class="programas-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 15px; padding: 20px; background: #f8f9fa; border-radius: 0 0 10px 10px;">';

            foreach ($programasGrado as $programa) {
                echo '
                    <div class="programa-card" style="background: white; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid ' . $color['border'] . '; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.transform=\'translateY(-3px)\'; this.style.boxShadow=\'0 5px 15px rgba(102,126,234,0.2)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.08)\';">
                        <div class="programa-codigo mb-2">
                            <span style="background: ' . $color['bg'] . '; color: white; padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: 700;">
                                ' . htmlspecialchars($programa['Codigo']) . '
                            </span>
                        </div>

                        <h5 class="programa-nombre mb-3" style="color: #464E5F; font-weight: 600; font-size: 14px; line-height: 1.4; min-height: 40px;">
                            ' . htmlspecialchars($programa['NombrePrograma']) . '
                        </h5>

                        <div class="programa-stats mb-3" style="display: flex; gap: 10px; justify-content: space-between;">
                            <div class="stat-item" style="flex: 1; text-align: center; background: #f1f3f4; padding: 8px; border-radius: 8px;">
                                <div style="color: #667eea; font-size: 18px; font-weight: 700;">
                                    ' . $programa['TotalModulos'] . '
                                </div>
                                <div style="color: #B5B5C3; font-size: 10px; text-transform: uppercase; font-weight: 600;">
                                    Módulos
                                </div>
                            </div>
                            <div class="stat-item" style="flex: 1; text-align: center; background: #f1f3f4; padding: 8px; border-radius: 8px;">
                                <div style="color: #fd397a; font-size: 18px; font-weight: 700;">
                                    ' . $programa['TotalInscritos'] . '
                                </div>
                                <div style="color: #B5B5C3; font-size: 10px; text-transform: uppercase; font-weight: 600;">
                                    Inscritos
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-sm btn-block ver-modulos-programa"
                                data-programa-id="' . $programa['ProgramaID'] . '"
                                data-programa-nombre="' . htmlspecialchars($programa['NombrePrograma']) . '"
                                style="background: ' . $color['bg'] . '; color: white; border: none; border-radius: 8px; padding: 8px; font-size: 12px; font-weight: 600; transition: all 0.3s ease;">
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
