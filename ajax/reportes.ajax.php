<?php
/**
 * AJAX Handler para Reportes PDF
 * Procesa las solicitudes de generación de reportes
 */

require_once '../controladores/reportes.controlador.php';

if (isset($_POST['tipo_reporte'])) {
    $tipoReporte = $_POST['tipo_reporte'];

    switch ($tipoReporte) {
        case 'estudiantes':
            // Reporte completo de estudiantes
            ReportesControladores::GenerarReporteEstudiantes();
            break;

        case 'por_programa':
            // Reporte de estudiantes por programa
            $programaID = isset($_POST['programa_id']) && !empty($_POST['programa_id'])
                ? intval($_POST['programa_id'])
                : null;
            ReportesControladores::GenerarReportePorPrograma($programaID);
            break;

        case 'modulos':
            // Reporte de módulos y matriculados
            ReportesControladores::GenerarReporteModulos();
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
