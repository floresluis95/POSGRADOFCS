<?php
/**
 * Controlador de Reportes PDF
 * Utiliza TCPDF para generar reportes
 */

// Incluir TCPDF
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once __DIR__ . '/../modelos/reportes.modelo.php';

// El servidor tiene configurada otra zona horaria (Europe/Berlin) en php.ini;
// sin esto, la fecha/hora impresa en los reportes no corresponde a Bolivia.
date_default_timezone_set('America/La_Paz');

class ReportesControladores extends TCPDF
{
    // Header personalizado (mismo formato institucional que los demás reportes: logos UTO/FCS)
    public function Header()
    {
        $pageWidth = $this->getPageWidth();
        $logoSize = 18; // mismo ancho y alto para ambos logos

        $logoUTO = __DIR__ . '/../extensiones/imagenespdf/logouto.png';
        $logoFCS = __DIR__ . '/../extensiones/imagenespdf/logofcs.png';

        if (file_exists($logoUTO)) {
            $this->Image($logoUTO, 15, 8, $logoSize, $logoSize, 'PNG');
        }
        if (file_exists($logoFCS)) {
            $this->Image($logoFCS, $pageWidth - 15 - $logoSize, 8, $logoSize, $logoSize, 'PNG');
        }

        $this->SetY(8);
        $this->SetTextColor(30, 60, 114);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 5, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(0, 4, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');
        $this->Cell(0, 4, 'COORDINACIÓN DE POSGRADO - ODONTOLOGÍA', 0, 1, 'C');

        $this->SetFont('helvetica', '', 7);
        $this->SetTextColor(100);
        $this->Cell(0, 4, 'Calle Presidente Montes entre Murguía y Aldana, Edificio UTO 2do Piso - Telf: 5237317 - Fax: 5247110 - Oruro - Bolivia', 0, 1, 'C');

        // Bajar el cursor hasta después de los logos, para que la fecha no quede oculta detrás de ellos
        $yTrasLogos = 8 + $logoSize + 2;
        if ($this->GetY() < $yTrasLogos) {
            $this->SetY($yTrasLogos);
        }
        $this->SetFont('helvetica', '', 8);
        $this->Cell(0, 4, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'R');

        $this->SetLineWidth(0.4);
        $this->SetDrawColor(30, 60, 114);
        $this->Line(15, $this->GetY() + 1, $pageWidth - 15, $this->GetY() + 1);
        $this->Ln(4);

        $this->SetTextColor(0, 0, 0);
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
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(30, 60, 114);

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
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->Cell(100, 7, 'TOTAL DE ESTUDIANTES:', 1, 0, 'R', true);
        $pdf->Cell(80, 7, count($estudiantes), 1, 1, 'C', true);

        $pdf->Output('Lista_Estudiantes_' . date('Ymd_His') . '.pdf', 'I');
    }

    // Reporte 2: Estudiantes por Programa (opcionalmente acotado por Sede)
    public static function GenerarReportePorPrograma($programaID = null, $sede = null)
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
        if ($sede) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'Sede: ' . mb_strtoupper($sede), 0, 1, 'C');
        }
        $pdf->Ln(3);

        // Obtener datos
        $estudiantes = ReportesModelos::EstudiantesPorPrograma($programaID, $sede);

        if (empty($estudiantes)) {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No hay estudiantes inscritos con los filtros seleccionados', 0, 1, 'C');
            $pdf->Output('Estudiantes_Por_Programa_' . date('Ymd') . '.pdf', 'I');
            return;
        }

        $programaActual = '';
        $contadorPrograma = 0;
        $contadorGeneral = 0;

        foreach ($estudiantes as $est) {
            // Nuevo programa
            if ($programaActual != $est['NombrePrograma']) {
                $programaActual = $est['NombrePrograma'];
                $contadorPrograma = 1;

                if ($contadorGeneral > 0) {
                    $pdf->Ln(5);
                }

                // Título del programa
                $pdf->SetFont('helvetica', 'B', 11);
                $pdf->SetFillColor(42, 82, 152);
                $pdf->SetTextColor(255);
                $pdf->Cell(0, 8, 'PROGRAMA: ' . mb_strtoupper($programaActual) . '  (' . mb_strtoupper($est['Sede']) . ' - ' . $est['GradoAcademico'] . ')', 0, 1, 'L', true);
                $pdf->Ln(2);

                // Header de tabla
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetFillColor(30, 60, 114);
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

            $contadorGeneral++;
        }

        $pdf->Output('Estudiantes_Por_Programa_' . date('Ymd_His') . '.pdf', 'I');
    }

    // Reporte 3: Módulos por Programa/Sede, con estado y avance de calificación
    public static function GenerarReporteModulos($programaID = null, $sede = null)
    {
        $pdf = new self();
        $pdf->SetCreator('Sistema Posgrado FCS');
        $pdf->SetTitle('Módulos por Programa');

        $pdf->SetMargins(10, 40, 10);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage('L'); // Orientación horizontal

        // Título
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 10, 'REPORTE DE MÓDULOS POR PROGRAMA' . ($sede ? ' - SEDE ' . mb_strtoupper($sede) : ''), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Estado del módulo, docente asignado y avance de calificación', 0, 1, 'C');
        $pdf->Ln(5);

        // Obtener datos
        $modulos = ReportesModelos::ModulosConMatriculados($programaID, $sede);

        if (empty($modulos)) {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No hay módulos con los filtros seleccionados', 0, 1, 'C');
            $pdf->Output('Reporte_Modulos_' . date('Ymd') . '.pdf', 'I');
            return;
        }

        // Header de tabla
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(30, 60, 114);

        $pdf->Cell(22, 8, 'Código', 1, 0, 'C', true);
        $pdf->Cell(58, 8, 'Nombre del Módulo', 1, 0, 'C', true);
        $pdf->Cell(70, 8, 'Programa / Sede', 1, 0, 'C', true);
        $pdf->Cell(38, 8, 'Docente', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Estado', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Calificados', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Aprobados', 1, 0, 'C', true);
        $pdf->Cell(24, 8, 'Reprobados', 1, 1, 'C', true);

        // Datos
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetDrawColor(200, 200, 200);

        $totalAprobados = 0;
        $totalReprobados = 0;
        $totalCalificados = 0;
        $fill = false;

        foreach ($modulos as $mod) {
            $pdf->SetFillColor(245, 245, 245);

            $pdf->Cell(22, 6, $mod['CodigoModulo'], 1, 0, 'C', $fill);
            $pdf->Cell(58, 6, mb_strtoupper(substr($mod['NombreModulo'], 0, 36)), 1, 0, 'L', $fill);
            $pdf->Cell(70, 6, substr($mod['NombrePrograma'], 0, 30) . ' (' . $mod['Sede'] . ')', 1, 0, 'L', $fill);
            $pdf->Cell(38, 6, $mod['Docente'] ? mb_strtoupper(substr($mod['Docente'], 0, 22)) : 'SIN ASIGNAR', 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, $mod['EstadoModulo'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $mod['TotalCalificados'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $mod['TotalAprobados'], 1, 0, 'C', $fill);
            $pdf->Cell(24, 6, $mod['TotalReprobados'], 1, 1, 'C', $fill);

            $totalCalificados += $mod['TotalCalificados'];
            $totalAprobados += $mod['TotalAprobados'];
            $totalReprobados += $mod['TotalReprobados'];
            $fill = !$fill;
        }

        // Totales
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->Cell(213, 7, 'TOTALES:', 1, 0, 'R', true);
        $pdf->Cell(20, 7, $totalCalificados, 1, 0, 'C', true);
        $pdf->Cell(20, 7, $totalAprobados, 1, 0, 'C', true);
        $pdf->Cell(24, 7, $totalReprobados, 1, 1, 'C', true);

        $pdf->Output('Reporte_Modulos_' . date('Ymd_His') . '.pdf', 'I');
    }

    // Reporte 4: Ejecutivo - KPIs por programa para apoyar decisiones administrativas
    public static function GenerarReporteEjecutivo($sede = null, $programaID = null)
    {
        $pdf = new self();
        $pdf->SetCreator('Sistema Posgrado FCS');
        $pdf->SetTitle('Reporte Ejecutivo');

        $pdf->SetMargins(10, 40, 10);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage('L');

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 10, 'REPORTE EJECUTIVO POR PROGRAMA' . ($sede ? ' - SEDE ' . mb_strtoupper($sede) : ' - TODAS LAS SEDES'), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Matrícula, cobranza, aprobación y avance académico para apoyo a la toma de decisiones', 0, 1, 'C');
        $pdf->Ln(5);

        $programas = ReportesModelos::ReporteEjecutivoPorPrograma($sede, $programaID);

        if (empty($programas)) {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No hay programas con los filtros seleccionados', 0, 1, 'C');
            $pdf->Output('Reporte_Ejecutivo_' . date('Ymd') . '.pdf', 'I');
            return;
        }

        // Header de tabla
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(30, 60, 114);

        $pdf->Cell(50, 9, 'Programa', 1, 0, 'C', true);
        $pdf->Cell(22, 9, 'Sede', 1, 0, 'C', true);
        $pdf->Cell(24, 9, 'Grado', 1, 0, 'C', true);
        $pdf->Cell(20, 9, 'Matric.', 1, 0, 'C', true);
        $pdf->Cell(28, 9, 'Comprometido (Bs.)', 1, 0, 'C', true);
        $pdf->Cell(28, 9, 'Recaudado (Bs.)', 1, 0, 'C', true);
        $pdf->Cell(18, 9, '% Cobro', 1, 0, 'C', true);
        $pdf->Cell(18, 9, 'Módulos', 1, 0, 'C', true);
        $pdf->Cell(20, 9, 'Validados', 1, 0, 'C', true);
        $pdf->Cell(18, 9, 'Docentes', 1, 0, 'C', true);
        $pdf->Cell(24, 9, '% Aprobación', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetDrawColor(200, 200, 200);
        $fill = false;

        $sumMatriculados = 0;
        $sumComprometido = 0;
        $sumRecaudado = 0;
        $sumModulos = 0;
        $sumValidados = 0;
        $sumCalificados = 0;
        $sumAprobados = 0;

        foreach ($programas as $prog) {
            $pctCobro = $prog['MontoComprometido'] > 0 ? ($prog['MontoRecaudado'] / $prog['MontoComprometido']) * 100 : 0;
            $pctAprobacion = $prog['TotalCalificados'] > 0 ? ($prog['TotalAprobados'] / $prog['TotalCalificados']) * 100 : 0;

            // Resaltar en rojo claro los programas con baja cobranza o baja aprobación (señal de alerta)
            if ($pctCobro < 50 && $prog['MontoComprometido'] > 0) {
                $pdf->SetFillColor(253, 235, 235);
            } elseif ($pctAprobacion < 60 && $prog['TotalCalificados'] > 0) {
                $pdf->SetFillColor(255, 247, 230);
            } else {
                $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            }

            $pdf->Cell(50, 6, mb_strtoupper(substr($prog['NombrePrograma'], 0, 26)), 1, 0, 'L', true);
            $pdf->Cell(22, 6, $prog['Sede'], 1, 0, 'C', true);
            $pdf->Cell(24, 6, $prog['GradoAcademico'], 1, 0, 'C', true);
            $pdf->Cell(20, 6, $prog['TotalMatriculados'], 1, 0, 'C', true);
            $pdf->Cell(28, 6, number_format($prog['MontoComprometido'], 2), 1, 0, 'R', true);
            $pdf->Cell(28, 6, number_format($prog['MontoRecaudado'], 2), 1, 0, 'R', true);
            $pdf->Cell(18, 6, number_format($pctCobro, 0) . '%', 1, 0, 'C', true);
            $pdf->Cell(18, 6, $prog['TotalModulos'], 1, 0, 'C', true);
            $pdf->Cell(20, 6, $prog['ModulosValidados'], 1, 0, 'C', true);
            $pdf->Cell(18, 6, $prog['TotalDocentes'], 1, 0, 'C', true);
            $pdf->Cell(24, 6, $prog['TotalCalificados'] > 0 ? number_format($pctAprobacion, 0) . '%' : '-', 1, 1, 'C', true);

            $sumMatriculados += $prog['TotalMatriculados'];
            $sumComprometido += $prog['MontoComprometido'];
            $sumRecaudado += $prog['MontoRecaudado'];
            $sumModulos += $prog['TotalModulos'];
            $sumValidados += $prog['ModulosValidados'];
            $sumCalificados += $prog['TotalCalificados'];
            $sumAprobados += $prog['TotalAprobados'];
            $fill = !$fill;
        }

        // Totales generales
        $pctCobroTotal = $sumComprometido > 0 ? ($sumRecaudado / $sumComprometido) * 100 : 0;
        $pctAprobTotal = $sumCalificados > 0 ? ($sumAprobados / $sumCalificados) * 100 : 0;

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->Cell(96, 7, 'TOTALES / PROMEDIO GENERAL:', 1, 0, 'R', true);
        $pdf->Cell(20, 7, $sumMatriculados, 1, 0, 'C', true);
        $pdf->Cell(28, 7, number_format($sumComprometido, 2), 1, 0, 'R', true);
        $pdf->Cell(28, 7, number_format($sumRecaudado, 2), 1, 0, 'R', true);
        $pdf->Cell(18, 7, number_format($pctCobroTotal, 0) . '%', 1, 0, 'C', true);
        $pdf->Cell(18, 7, $sumModulos, 1, 0, 'C', true);
        $pdf->Cell(20, 7, $sumValidados, 1, 0, 'C', true);
        $pdf->Cell(18, 7, '', 1, 0, 'C', true);
        $pdf->Cell(24, 7, $sumCalificados > 0 ? number_format($pctAprobTotal, 0) . '%' : '-', 1, 1, 'C', true);

        // Leyenda
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetFillColor(253, 235, 235);
        $pdf->Cell(5, 5, '', 1, 0, 'C', true);
        $pdf->Cell(90, 5, ' Cobranza por debajo del 50% (revisar seguimiento de pagos)', 0, 0, 'L');
        $pdf->SetFillColor(255, 247, 230);
        $pdf->Cell(5, 5, '', 1, 0, 'C', true);
        $pdf->Cell(90, 5, ' Aprobación por debajo del 60% (revisar apoyo académico)', 0, 1, 'L');

        $pdf->Output('Reporte_Ejecutivo_' . date('Ymd_His') . '.pdf', 'I');
    }
}
?>
