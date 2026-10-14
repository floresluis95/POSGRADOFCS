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
     * Datos de la pestaña "Gráficos": cobrado e inscritos por mes y órdenes por cobrar
     */
    public static function ObtenerDatosGraficosControlador()
    {
        $grado = isset($_POST['grado']) && $_POST['grado'] !== ''
            ? htmlspecialchars(trim($_POST['grado']))
            : null;
        $meses = (int)($_POST['meses'] ?? 12);
        if (!in_array($meses, [6, 12, 24], true)) {
            $meses = 12;
        }

        echo json_encode(ReporteModulosModelo::ObtenerDatosGraficosModelo($grado, $meses));
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
     * Datos de la cabecera y de la pestaña "Programas y módulos" del Reporte General:
     * indicadores generales y programas agrupados por grado académico.
     * La vista (reportemodulos.php) solo los dibuja.
     * @return array
     */
    public static function DatosResumenControlador()
    {
        $stats = ReporteModulosModelo::ObtenerEstadisticasGeneralesModelo();
        $programas = ReporteModulosModelo::ObtenerConteoModulosPorProgramaModelo();

        $porGrado = [];
        $sedes = [];
        $programasConInscritos = 0;
        foreach ($programas as $programa) {
            $porGrado[$programa['GradoAcademico']][] = $programa;
            if (!empty($programa['Sede'])) {
                $sedes[$programa['Sede']] = ($sedes[$programa['Sede']] ?? 0) + 1;
            }
            if ((int)$programa['TotalInscritos'] > 0) {
                $programasConInscritos++;
            }
        }

        // Detalle para la franja de indicadores: "1 Especialidad · 1 Maestría"
        $detalleGrados = [];
        foreach ($porGrado as $grado => $lista) {
            $detalleGrados[] = count($lista) . ' ' . mb_convert_case($grado, MB_CASE_TITLE, 'UTF-8');
        }

        return [
            'stats' => $stats,
            'totalSedes' => count($sedes),
            'sedes' => $sedes,
            'detalleGrados' => implode(' · ', $detalleGrados),
            'programasConInscritos' => $programasConInscritos,
            'programasPorGrado' => $porGrado,
        ];
    }
}
?>
