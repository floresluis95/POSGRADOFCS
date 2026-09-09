<?php
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
