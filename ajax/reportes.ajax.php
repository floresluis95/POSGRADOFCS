<?php
require_once __DIR__ . '/control-solicitud.php'; // corte de internet: sin envíos duplicados
require_once __DIR__ . '/validar-sesion.php';
ExigirSesionAjax(['ADM', 'SEC']); // Solo Administración y Secretaría (igual que su página)
/**
 * AJAX Handler para Reportes PDF
 * Procesa las solicitudes de generación de reportes
 */

require_once '../controladores/reportes.controlador.php';

if (isset($_POST['tipo_reporte'])) {
    $tipoReporte = $_POST['tipo_reporte'];

    $programaID = isset($_POST['programa_id']) && !empty($_POST['programa_id'])
        ? intval($_POST['programa_id'])
        : null;
    $sede = isset($_POST['sede']) && !empty($_POST['sede'])
        ? trim($_POST['sede'])
        : null;

    switch ($tipoReporte) {
        case 'estudiantes':
            // Reporte completo de estudiantes
            ReportesControladores::GenerarReporteEstudiantes();
            break;

        case 'por_programa':
            // Reporte de estudiantes por programa y/o sede
            ReportesControladores::GenerarReportePorPrograma($programaID, $sede);
            break;

        case 'modulos':
            // Reporte de módulos por programa y/o sede
            ReportesControladores::GenerarReporteModulos($programaID, $sede);
            break;

        case 'ejecutivo':
            // Reporte ejecutivo (KPIs para toma de decisiones) por sede y/o programa
            ReportesControladores::GenerarReporteEjecutivo($sede, $programaID);
            break;

        case 'recaudacion':
            // Recaudación por cuenta bancaria; las fechas se aceptan solo en formato AAAA-MM-DD
            $validarFecha = function ($campo) {
                $valor = trim($_POST[$campo] ?? '');
                $fecha = DateTime::createFromFormat('Y-m-d', $valor);
                return ($fecha && $fecha->format('Y-m-d') === $valor) ? $valor : null;
            };
            $fechaDesde = $validarFecha('fecha_desde');
            $fechaHasta = $validarFecha('fecha_hasta');

            // Si las pusieron al revés, se intercambian en lugar de mostrar un reporte vacío
            if ($fechaDesde && $fechaHasta && $fechaDesde > $fechaHasta) {
                list($fechaDesde, $fechaHasta) = [$fechaHasta, $fechaDesde];
            }

            ReportesControladores::GenerarReporteRecaudacion($sede, $programaID, $fechaDesde, $fechaHasta);
            break;

        default:
            echo '<script>
                swal("ERROR!", "Tipo de reporte no válido", "error")
                .then(function () { window.close(); });
            </script>';
            break;
    }
} else {
    echo '<script>
        swal("ERROR!", "No se especificó el tipo de reporte", "error")
        .then(function () { window.close(); });
    </script>';
}
?>
