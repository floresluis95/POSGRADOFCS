<?php
/**
 * Controlador de Reportes PDF
 * Utiliza TCPDF para generar reportes
 */

require_once 'vendor/autoload.php';
require_once 'modelos/reportes.modelo.php';

class ReportesControladores extends TCPDF
{
    // Header personalizado
    public function Header()
    {
        // Logo (opcional)
        if (file_exists('vistas/recursos/assets/media/logos/logo0.png')) {
            $this->Image('vistas/recursos/assets/media/logos/logo0.png', 15, 10, 20, '', 'PNG');
        }

        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(102, 126, 234);
        $this->Cell(0, 15, 'UNIVERSIDAD - POSGRADO FCS', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln();

        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(100);
        $this->Cell(0, 10, 'Fecha: ' . date('d/m/Y H:i'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        $this->Ln(12);
    }

    // Footer personalizado
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    // Reporte 1: Lista General de Estudiantes
    public static function GenerarReporteEstudiantes()
    {
        $pdf = new self();
        $pdf->SetCreator('Sistema Posgrado FCS');
        $pdf->SetAuthor('Posgrado FCS');
        $pdf->SetTitle('Lista de Estudiantes');
        $pdf->SetSubject('Reporte de Estudiantes Activos');

        $pdf->SetMargins(15, 40, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 25);

        $pdf->AddPage();

        // Título del reporte
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 10, 'LISTA GENERAL DE ESTUDIANTES', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Total de estudiantes activos en el sistema', 0, 1, 'C');
        $pdf->Ln(5);

        // Obtener datos
        $estudiantes = ReportesModelos::ListaEstudiantesCompleta();

        // Header de tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(102, 126, 234);

        $pdf->Cell(10, 8, 'Nº', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'C.I.', 1, 0, 'C', true);
        $pdf->Cell(60, 8, 'Nombre Completo', 1, 0, 'C', true);
        $pdf->Cell(45, 8, 'Profesión', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Celular', 1, 1, 'C', true);

        // Datos
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetDrawColor(200, 200, 200);
        $contador = 1;
        $fill = false;

        foreach ($estudiantes as $est) {
            $pdf->SetFillColor(245, 245, 245);

            $ci = $est['Ci'];
            if (!empty($est['Complemento'])) $ci .= '-' . $est['Complemento'];
            $ci .= ' ' . $est['Exp'];

            $nombreCompleto = $est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre'];

            $pdf->Cell(10, 6, $contador++, 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $ci, 1, 0, 'C', $fill);
            $pdf->Cell(60, 6, mb_strtoupper($nombreCompleto), 1, 0, 'L', $fill);
            $pdf->Cell(45, 6, substr($est['NombreProfesion'], 0, 30), 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, $est['Celular'], 1, 1, 'C', $fill);

            $fill = !$fill;
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255);
        $pdf->Cell(100, 7, 'TOTAL DE ESTUDIANTES:', 1, 0, 'R', true);
        $pdf->Cell(80, 7, count($estudiantes), 1, 1, 'C', true);

        $pdf->Output('Lista_Estudiantes_' . date('Ymd_His') . '.pdf', 'D');
    }

    // Reporte 2: Estudiantes por Programa
    public static function GenerarReportePorPrograma($programaID = null)
    {
        $pdf = new self();
        $pdf->SetCreator('Sistema Posgrado FCS');
        $pdf->SetTitle('Estudiantes por Programa');

        $pdf->SetMargins(15, 40, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 10, 'ESTUDIANTES POR PROGRAMA ACADÉMICO', 0, 1, 'C');
        $pdf->Ln(3);

        // Obtener datos
        $estudiantes = ReportesModelos::EstudiantesPorPrograma($programaID);

        if (empty($estudiantes)) {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No hay estudiantes inscritos en programas', 0, 1, 'C');
            $pdf->Output('Estudiantes_Por_Programa_' . date('Ymd') . '.pdf', 'D');
            return;
        }

        $programaActual = '';
        $contadorPrograma = 0;

        foreach ($estudiantes as $est) {
            // Nuevo programa
            if ($programaActual != $est['NombrePrograma']) {
                $programaActual = $est['NombrePrograma'];
                $contadorPrograma = 1;

                if ($contadorPrograma > 1) {
                    $pdf->Ln(5);
                }

                // Título del programa
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetFillColor(118, 75, 162);
                $pdf->SetTextColor(255);
                $pdf->Cell(0, 8, 'PROGRAMA: ' . mb_strtoupper($programaActual), 0, 1, 'L', true);
                $pdf->Ln(2);

                // Header de tabla
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetFillColor(102, 126, 234);
                $pdf->Cell(10, 7, 'Nº', 1, 0, 'C', true);
                $pdf->Cell(55, 7, 'Nombre Completo', 1, 0, 'C', true);
                $pdf->Cell(25, 7, 'C.I.', 1, 0, 'C', true);
                $pdf->Cell(40, 7, 'Profesión', 1, 0, 'C', true);
                $pdf->Cell(25, 7, 'F. Inscripción', 1, 0, 'C', true);
                $pdf->Cell(25, 7, 'Celular', 1, 1, 'C', true);

                $pdf->SetTextColor(51, 51, 51);
            }

            // Datos del estudiante
            $pdf->SetFont('helvetica', '', 7);
            $fill = ($contadorPrograma % 2 == 0);
            $pdf->SetFillColor(245, 245, 245);

            $ci = $est['Ci'];
            if (!empty($est['Complemento'])) $ci .= '-' . $est['Complemento'];
            $ci .= ' ' . $est['Exp'];

            $nombreCompleto = $est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre'];

            $pdf->Cell(10, 5, $contadorPrograma++, 1, 0, 'C', $fill);
            $pdf->Cell(55, 5, mb_strtoupper(substr($nombreCompleto, 0, 35)), 1, 0, 'L', $fill);
            $pdf->Cell(25, 5, $ci, 1, 0, 'C', $fill);
            $pdf->Cell(40, 5, substr($est['NombreProfesion'], 0, 25), 1, 0, 'L', $fill);
            $pdf->Cell(25, 5, date('d/m/Y', strtotime($est['FechaInscripcion'])), 1, 0, 'C', $fill);
            $pdf->Cell(25, 5, $est['Celular'], 1, 1, 'C', $fill);
        }

        $pdf->Output('Estudiantes_Por_Programa_' . date('Ymd_His') . '.pdf', 'D');
    }

    // Reporte 3: Módulos y Matriculados
    public static function GenerarReporteModulos()
    {
        $pdf = new self();
        $pdf->SetCreator('Sistema Posgrado FCS');
        $pdf->SetTitle('Módulos y Matriculados');

        $pdf->SetMargins(10, 40, 10);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage('L'); // Orientación horizontal

        // Título
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 10, 'REPORTE DE MÓDULOS Y ESTUDIANTES MATRICULADOS', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Resumen de módulos activos con información de matriculados y pagos', 0, 1, 'C');
        $pdf->Ln(5);

        // Obtener datos
        $modulos = ReportesModelos::ModulosConMatriculados();

        // Header de tabla
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(102, 126, 234);

        $pdf->Cell(25, 8, 'Código', 1, 0, 'C', true);
        $pdf->Cell(95, 8, 'Nombre del Módulo', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Horas', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Costo (Bs.)', 1, 0, 'C', true);
        $pdf->Cell(28, 8, 'Matriculados', 1, 0, 'C', true);
        $pdf->Cell(22, 8, 'Pagados', 1, 0, 'C', true);
        $pdf->Cell(28, 8, 'Pendientes', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Recaudado (Bs.)', 1, 1, 'C', true);

        // Datos
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetDrawColor(200, 200, 200);

        $totalMatriculados = 0;
        $totalRecaudado = 0;
        $fill = false;

        foreach ($modulos as $mod) {
            $pdf->SetFillColor(245, 245, 245);

            $pdf->Cell(25, 6, $mod['CodigoModulo'], 1, 0, 'C', $fill);
            $pdf->Cell(95, 6, mb_strtoupper(substr($mod['NombreModulo'], 0, 60)), 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, $mod['HorasAcademicas'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, number_format($mod['Costo'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(28, 6, $mod['TotalMatriculados'], 1, 0, 'C', $fill);
            $pdf->Cell(22, 6, $mod['TotalPagados'], 1, 0, 'C', $fill);
            $pdf->Cell(28, 6, $mod['TotalPendientes'], 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, number_format($mod['TotalRecaudado'], 2), 1, 1, 'R', $fill);

            $totalMatriculados += $mod['TotalMatriculados'];
            $totalRecaudado += $mod['TotalRecaudado'];
            $fill = !$fill;
        }

        // Totales
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(102, 126, 234);
        $pdf->SetTextColor(255);
        $pdf->Cell(170, 7, 'TOTALES:', 1, 0, 'R', true);
        $pdf->Cell(28, 7, $totalMatriculados, 1, 0, 'C', true);
        $pdf->Cell(50, 7, '', 1, 0, 'C', true);
        $pdf->Cell(35, 7, number_format($totalRecaudado, 2), 1, 1, 'R', true);

        $pdf->Output('Reporte_Modulos_' . date('Ymd_His') . '.pdf', 'D');
    }
}
?>
