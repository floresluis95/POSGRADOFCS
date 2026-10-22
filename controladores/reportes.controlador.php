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

    // Reporte 5: Recaudación por cuenta bancaria (cuánto ingresó y cuánto falta cobrar en cada cuenta)
    public static function GenerarReporteRecaudacion($sede = null, $programaID = null, $fechaDesde = null, $fechaHasta = null)
    {
        $pdf = new self();
        $pdf->SetCreator('Sistema Posgrado FCS');
        $pdf->SetTitle('Recaudación por Cuenta');

        $pdf->SetMargins(10, 40, 10);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage('L');

        // Texto del periodo: el rango de fechas solo afecta a lo recaudado
        if ($fechaDesde && $fechaHasta) {
            $periodo = 'Del ' . date('d/m/Y', strtotime($fechaDesde)) . ' al ' . date('d/m/Y', strtotime($fechaHasta));
        } elseif ($fechaDesde) {
            $periodo = 'Desde el ' . date('d/m/Y', strtotime($fechaDesde));
        } elseif ($fechaHasta) {
            $periodo = 'Hasta el ' . date('d/m/Y', strtotime($fechaHasta));
        } else {
            $periodo = 'Todo el periodo';
        }

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 10, 'RECAUDACIÓN POR CUENTA BANCARIA' . ($sede ? ' - SEDE ' . mb_strtoupper($sede) : ' - TODAS LAS SEDES'), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Recaudado: ' . $periodo . '  |  Por cobrar: órdenes pendientes a la fecha', 0, 1, 'C');
        $pdf->Ln(5);

        $cuentas = ReportesModelos::RecaudacionPorCuenta($sede, $programaID, $fechaDesde, $fechaHasta);

        if (empty($cuentas)) {
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No hay cuentas ni pagos con los filtros seleccionados', 0, 1, 'C');
            $pdf->Output('Recaudacion_Por_Cuenta_' . date('Ymd') . '.pdf', 'I');
            return;
        }

        // Header de tabla (ancho útil en horizontal: 277 mm)
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(30, 60, 114);

        $pdf->Cell(62, 9, 'Programa / Sede', 1, 0, 'C', true);
        $pdf->Cell(22, 9, 'Concepto', 1, 0, 'C', true);
        $pdf->Cell(38, 9, 'Banco', 1, 0, 'C', true);
        $pdf->Cell(25, 9, 'Nº Cuenta', 1, 0, 'C', true);
        $pdf->Cell(18, 9, 'Tipo', 1, 0, 'C', true);
        $pdf->Cell(14, 9, 'Pagos', 1, 0, 'C', true);
        $pdf->Cell(28, 9, 'Recaudado (Bs.)', 1, 0, 'C', true);
        $pdf->Cell(28, 9, 'Por cobrar (Bs.)', 1, 0, 'C', true);
        $pdf->Cell(20, 9, 'Pendientes', 1, 0, 'C', true);
        $pdf->Cell(22, 9, 'Último pago', 1, 1, 'C', true);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetDrawColor(200, 200, 200);
        $fill = false;

        $sumPagos = 0;
        $sumRecaudado = 0;
        $sumPorCobrar = 0;
        $sumPendientes = 0;
        // Subtotales por concepto: la clínica se reporta aparte y no se mezcla
        // con lo recaudado por matrícula o cuotas.
        $porConcepto = [];

        foreach ($cuentas as $c) {
            $sinCuenta = $c['CuentaID'] === null;
            $inactiva = !$sinCuenta && (int)$c['EstadoCuenta'] !== 1;

            // Rojo claro: pagos que no tienen a qué cuenta ir (falta configurar la cuenta del programa).
            // Amarillo: cuenta desactivada que igual tiene movimientos.
            if ($sinCuenta) {
                $pdf->SetFillColor(253, 235, 235);
            } elseif ($inactiva) {
                $pdf->SetFillColor(255, 247, 230);
            } else {
                $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
            }

            if ($sinCuenta) {
                $tipo = '-';
            } elseif ($inactiva) {
                $tipo = 'INACTIVA';
            } else {
                $tipo = $c['Principal'] ? 'PRINCIPAL' : 'ALTERNA';
            }

            // stretch = 1: si el nombre no entra, TCPDF lo comprime en vez de desbordar la celda
            $pdf->Cell(62, 6, mb_strtoupper(mb_substr($c['NombrePrograma'], 0, 55)) . ' (' . $c['Sede'] . ')', 1, 0, 'L', true, '', 1);
            $pdf->Cell(22, 6, $c['Concepto'], 1, 0, 'C', true);
            $pdf->Cell(38, 6, $sinCuenta ? 'SIN CUENTA CONFIGURADA' : mb_strtoupper(mb_substr($c['Banco'], 0, 22)), 1, 0, 'L', true);
            $pdf->Cell(25, 6, $sinCuenta ? '-' : $c['NumeroCuenta'], 1, 0, 'C', true);
            $pdf->Cell(18, 6, $tipo, 1, 0, 'C', true);
            $pdf->Cell(14, 6, $c['NumPagos'], 1, 0, 'C', true);
            $pdf->Cell(28, 6, number_format($c['Recaudado'], 2), 1, 0, 'R', true);
            $pdf->Cell(28, 6, number_format($c['PorCobrar'], 2), 1, 0, 'R', true);
            $pdf->Cell(20, 6, $c['NumPendientes'], 1, 0, 'C', true);
            $pdf->Cell(22, 6, $c['UltimoPago'] ? date('d/m/Y', strtotime($c['UltimoPago'])) : '-', 1, 1, 'C', true);

            $sumPagos += $c['NumPagos'];
            $sumRecaudado += $c['Recaudado'];
            $sumPorCobrar += $c['PorCobrar'];
            $sumPendientes += $c['NumPendientes'];

            $concepto = $c['Concepto'];
            if (!isset($porConcepto[$concepto])) {
                $porConcepto[$concepto] = ['pagos' => 0, 'recaudado' => 0, 'porCobrar' => 0, 'pendientes' => 0];
            }
            $porConcepto[$concepto]['pagos'] += $c['NumPagos'];
            $porConcepto[$concepto]['recaudado'] += $c['Recaudado'];
            $porConcepto[$concepto]['porCobrar'] += $c['PorCobrar'];
            $porConcepto[$concepto]['pendientes'] += $c['NumPendientes'];
            $fill = !$fill;
        }

        // Subtotales por concepto (MATRICULA, CUOTAS, CLINICA), en ese orden
        $ordenConceptos = ['MATRICULA' => 1, 'CUOTAS' => 2, 'CLINICA' => 3];
        uksort($porConcepto, function ($a, $b) use ($ordenConceptos) {
            return ($ordenConceptos[$a] ?? 9) <=> ($ordenConceptos[$b] ?? 9);
        });
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(234, 239, 247);
        $pdf->SetTextColor(30, 60, 114);
        foreach ($porConcepto as $concepto => $t) {
            $pdf->Cell(165, 6, 'SUBTOTAL ' . $concepto . ':', 1, 0, 'R', true);
            $pdf->Cell(14, 6, $t['pagos'], 1, 0, 'C', true);
            $pdf->Cell(28, 6, number_format($t['recaudado'], 2), 1, 0, 'R', true);
            $pdf->Cell(28, 6, number_format($t['porCobrar'], 2), 1, 0, 'R', true);
            $pdf->Cell(20, 6, $t['pendientes'], 1, 0, 'C', true);
            $pdf->Cell(22, 6, '', 1, 1, 'C', true);
        }

        // Totales generales
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->Cell(165, 7, 'TOTALES:', 1, 0, 'R', true);
        $pdf->Cell(14, 7, $sumPagos, 1, 0, 'C', true);
        $pdf->Cell(28, 7, number_format($sumRecaudado, 2), 1, 0, 'R', true);
        $pdf->Cell(28, 7, number_format($sumPorCobrar, 2), 1, 0, 'R', true);
        $pdf->Cell(20, 7, $sumPendientes, 1, 0, 'C', true);
        $pdf->Cell(22, 7, '', 1, 1, 'C', true);

        // Leyenda
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetFillColor(253, 235, 235);
        $pdf->Cell(5, 5, '', 1, 0, 'C', true);
        $pdf->Cell(90, 5, ' Programa sin cuenta configurada para ese concepto', 0, 0, 'L');
        $pdf->SetFillColor(255, 247, 230);
        $pdf->Cell(5, 5, '', 1, 0, 'C', true);
        $pdf->Cell(90, 5, ' Cuenta desactivada que tiene pagos u órdenes', 0, 1, 'L');

        $pdf->Output('Recaudacion_Por_Cuenta_' . date('Ymd_His') . '.pdf', 'I');
    }
    // ---------------------------------------------------------------------
    // Ayudantes de los reportes nuevos (mismo formato que los anteriores)
    // ---------------------------------------------------------------------

    private static function NuevoPdf($titulo, $orientacion = 'P')
    {
        $pdf = new self($orientacion);
        $pdf->SetCreator('Sistema Posgrado FCS');
        $pdf->SetTitle($titulo);
        $pdf->SetMargins($orientacion === 'L' ? 10 : 15, 40, $orientacion === 'L' ? 10 : 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage($orientacion);
        return $pdf;
    }

    private static function Titulo($pdf, $titulo, $subtitulo = '')
    {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->Cell(0, 9, $titulo, 0, 1, 'C');
        if ($subtitulo !== '') {
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 5, $subtitulo, 0, 'C');
        }
        $pdf->Ln(3);
    }

    private static function SinDatos($pdf, $mensaje, $archivo)
    {
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 10, $mensaje, 0, 1, 'C');
        $pdf->Output($archivo . '_' . date('Ymd') . '.pdf', 'I');
    }

    // Fila de encabezado de tabla: [[ancho, texto, alineación], ...]
    private static function CabeceraTabla($pdf, array $columnas, $alto = 8)
    {
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        $pdf->SetDrawColor(30, 60, 114);
        foreach ($columnas as $i => $c) {
            $pdf->Cell($c[0], $alto, $c[1], 1, $i === count($columnas) - 1 ? 1 : 0, 'C', true, '', 1);
        }
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetDrawColor(200, 200, 200);
    }

    // Fila de datos: [[ancho, texto, alineación], ...]; el texto largo se comprime (stretch)
    private static function FilaTabla($pdf, array $columnas, $relleno, $alto = 6)
    {
        foreach ($columnas as $i => $c) {
            $pdf->Cell($c[0], $alto, (string)$c[1], 1, $i === count($columnas) - 1 ? 1 : 0, $c[2] ?? 'L', $relleno, '', 1);
        }
    }

    // Fila de totales (fondo azul): [[ancho, texto, alineación], ...]
    private static function TotalTabla($pdf, array $columnas, $alto = 7)
    {
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(30, 60, 114);
        $pdf->SetTextColor(255);
        self::FilaTabla($pdf, $columnas, true, $alto);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(51, 51, 51);
    }

    private static function Rayado($pdf, $par)
    {
        $pdf->SetFillColor($par ? 245 : 255, $par ? 245 : 255, $par ? 245 : 255);
    }

    private static function Bs($monto)
    {
        return number_format((float)$monto, 2);
    }

    private static function Fecha($fecha)
    {
        return $fecha ? date('d/m/Y', strtotime($fecha)) : '-';
    }

    private static function TextoPeriodo($desde, $hasta)
    {
        if ($desde && $hasta) return 'Del ' . self::Fecha($desde) . ' al ' . self::Fecha($hasta);
        if ($desde) return 'Desde el ' . self::Fecha($desde);
        if ($hasta) return 'Hasta el ' . self::Fecha($hasta);
        return 'Todo el periodo';
    }

    // Sede, programa y cuenta elegidos, para el subtítulo
    private static function TextoFiltros(array $f)
    {
        $partes = [];
        if (!empty($f['sede'])) $partes[] = 'Sede: ' . mb_strtoupper($f['sede']);
        if (!empty($f['programaID'])) {
            foreach (ReportesModelos::ListaProgramas() as $p) {
                if ((int)$p['ProgramaID'] === (int)$f['programaID']) $partes[] = 'Programa: ' . mb_strtoupper($p['NombrePrograma']);
            }
        }
        if (!empty($f['numeroCuenta'])) $partes[] = 'Cuenta N° ' . $f['numeroCuenta'];
        if (!empty($f['concepto'])) $partes[] = 'Concepto: ' . $f['concepto'];
        return $partes ? implode('  |  ', $partes) : 'Todas las sedes y programas';
    }

    private static function CiCompleto($fila)
    {
        return $fila['Ci'] . (!empty($fila['Complemento']) ? '-' . $fila['Complemento'] : '');
    }

    // ---------------------------------------------------------------------
    // ESTUDIANTES
    // ---------------------------------------------------------------------

    // Estado de cuenta de un estudiante: por programa, lo que debe, lo que pagó y lo pendiente
    public static function GenerarReporteEstadoCuenta($ci)
    {
        $pdf = self::NuevoPdf('Estado de Cuenta');
        self::Titulo($pdf, 'ESTADO DE CUENTA DEL ESTUDIANTE');

        $datos = $ci !== '' ? ReportesModelos::EstadoCuentaEstudiante($ci) : null;
        if (!$datos) {
            self::SinDatos($pdf, $ci === '' ? 'Escriba el C.I. del estudiante' : 'No se encontró un estudiante con C.I. ' . $ci, 'Estado_Cuenta');
            return;
        }
        $est = $datos['estudiante'];

        // Datos del estudiante
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(234, 239, 247);
        $pdf->Cell(30, 6, 'Estudiante:', 1, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(150, 6, mb_strtoupper(trim($est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre'])), 1, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, 'C.I.:', 1, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(45, 6, self::CiCompleto($est) . ' ' . $est['Exp'], 1, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(25, 6, 'Celular:', 1, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(80, 6, $est['Celular'] ?: '-', 1, 1, 'L');
        $pdf->Ln(4);

        if (empty($datos['programas'])) {
            self::SinDatos($pdf, 'El estudiante no tiene inscripciones', 'Estado_Cuenta');
            return;
        }

        $columnas = [[10, 'Nº'], [30, 'Concepto'], [24, 'Fecha'], [40, 'Voucher / Orden'], [28, 'Monto (Bs.)'], [48, 'Estado']];
        foreach ($datos['programas'] as $prog) {
            if ($pdf->GetY() > 230) $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(42, 82, 152);
            $pdf->SetTextColor(255);
            $pdf->Cell(0, 7, mb_strtoupper($prog['NombrePrograma']) . '  (' . mb_strtoupper($prog['Sede']) . ' - ' . $prog['GradoAcademico'] . ')', 0, 1, 'L', true, '', 1);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->SetFont('helvetica', '', 8);
            $plan = $prog['TipoPlan'] ? $prog['TipoPlan'] . ' en ' . (int)$prog['NumeroCuotasPlan'] . ' cuotas' : 'Sin plan de pagos';
            $pdf->Cell(0, 5, 'Inscripción: ' . self::Fecha($prog['FechaInscripcion']) . '   |   Estado: ' . $prog['EstadoInscripcion'] . '   |   Plan: ' . $plan, 0, 1, 'L');
            $pdf->Ln(1);

            self::CabeceraTabla($pdf, $columnas, 7);
            $n = 1;
            $par = false;
            $fila = function ($concepto, $fecha, $voucher, $monto, $estado) use ($pdf, &$n, &$par) {
                self::Rayado($pdf, $par);
                self::FilaTabla($pdf, [[10, $n++, 'C'], [30, $concepto, 'L'], [24, self::Fecha($fecha), 'C'], [40, $voucher ?: '-', 'C'], [28, self::Bs($monto), 'R'], [48, $estado, 'C']], true);
                $par = !$par;
            };

            // Matrícula
            $fila('MATRÍCULA', $prog['FechaInscripcion'], $prog['nvauchermatricula'],
                (float)$prog['montoPagado'] > 0 ? $prog['montoPagado'] : $prog['costomatricula'],
                (float)$prog['montoPagado'] > 0 ? 'PAGADO' : 'SIN PAGO');

            // Cuotas (pagadas, en revisión, rechazadas...) y órdenes de cuota aún sin pagar
            $pagadoCuotas = 0;
            foreach ($prog['Cuotas'] as $c) {
                if ($c['Estado'] === 'PAGADO') $pagadoCuotas += (float)$c['Monto'];
                $fila('CUOTA ' . ($c['NumeroCuota'] ?: ''), $c['FechaPago'], $c['NumeroVoucher'], $c['Monto'], $c['Estado']);
            }
            foreach ($prog['CuotasPorPagar'] as $o) {
                $fila('CUOTA (orden)', $o['FechaGeneracion'], 'Orden ' . $o['NumeroOrden'], $o['MontoTotal'],
                    $o['Estado'] === 'EXTRAVIADO' ? 'VOUCHER POR VALIDAR' : 'POR PAGAR');
            }

            // Clínicas (aparte del saldo del programa)
            $pagadoClinica = 0;
            foreach ($prog['Clinicas'] as $cl) {
                $pagada = !empty($cl['IdPagoClinica']);
                if ($pagada) $pagadoClinica += (float)$cl['MontoTotal'];
                $fila('CLÍNICA', $pagada ? $cl['FechaPago'] : $cl['FechaGeneracion'], $pagada ? $cl['NumeroVoucher'] : 'Orden ' . $cl['NumeroOrden'],
                    $cl['MontoTotal'], $pagada ? 'PAGADO' : ($cl['Estado'] === 'EXTRAVIADO' ? 'VOUCHER POR VALIDAR' : 'POR PAGAR'));
            }

            // Resumen del programa: el saldo es el costo del plan menos las cuotas pagadas
            $costoPrograma = (float)($prog['MontoTotalPagar'] ?? 0);
            $saldo = max($costoPrograma - $pagadoCuotas, 0);
            $pdf->Ln(1);
            self::TotalTabla($pdf, [[104, 'Costo del programa (sin matrícula):', 'R'], [28, self::Bs($costoPrograma), 'R'], [48, '', 'C']], 6);
            self::TotalTabla($pdf, [[104, 'Cuotas pagadas:', 'R'], [28, self::Bs($pagadoCuotas), 'R'], [48, '', 'C']], 6);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor($saldo > 0 ? 253 : 232, $saldo > 0 ? 235 : 245, $saldo > 0 ? 235 : 233);
            $pdf->SetTextColor($saldo > 0 ? 176 : 21, $saldo > 0 ? 42 : 101, $saldo > 0 ? 55 : 52);
            $pdf->Cell(104, 7, 'SALDO PENDIENTE:', 1, 0, 'R', true);
            $pdf->Cell(28, 7, self::Bs($saldo), 1, 0, 'R', true);
            $pdf->Cell(48, 7, $prog['TipoPlan'] ? ($saldo > 0 ? 'CON SALDO' : 'AL DÍA') : 'SIN PLAN', 1, 1, 'C', true);
            $pdf->SetTextColor(51, 51, 51);
            if ($pagadoClinica > 0) {
                $pdf->SetFont('helvetica', 'I', 7);
                $pdf->Cell(0, 5, 'Clínica pagada: Bs. ' . self::Bs($pagadoClinica) . ' (no se suma al saldo del programa)', 0, 1, 'R');
            }
            $pdf->Ln(5);
        }

        $pdf->Output('Estado_Cuenta_' . $est['Ci'] . '_' . date('Ymd_His') . '.pdf', 'I');
    }

    // ---------------------------------------------------------------------
    // PROGRAMAS
    // ---------------------------------------------------------------------

    // Saldos por estudiante de cada programa (para seguimiento de cobranza)
    public static function GenerarReporteSaldos($sede = null, $programaID = null, $soloConSaldo = false)
    {
        $pdf = self::NuevoPdf('Saldos por Estudiante', 'L');
        self::Titulo($pdf, 'SALDOS POR ESTUDIANTE', self::TextoFiltros(['sede' => $sede, 'programaID' => $programaID])
            . ($soloConSaldo ? '  |  Solo estudiantes con saldo pendiente' : '') . '  |  Montos de matrícula y cuotas (sin clínica)');

        $programas = ReportesModelos::SaldosPorPrograma($sede, $programaID, $soloConSaldo);
        if (empty($programas)) {
            self::SinDatos($pdf, 'No hay estudiantes con los filtros seleccionados', 'Saldos_Estudiantes');
            return;
        }

        $columnas = [[8, 'Nº', 'C'], [72, 'Estudiante', 'L'], [24, 'C.I.', 'C'], [22, 'Celular', 'C'], [24, 'Plan', 'C'], [17, 'Cuotas', 'C'],
                     [28, 'Costo total (Bs.)', 'R'], [28, 'Pagado (Bs.)', 'R'], [28, 'Saldo (Bs.)', 'R'], [26, 'Estado', 'C']];
        $general = ['costo' => 0, 'pagado' => 0, 'saldo' => 0, 'n' => 0];

        foreach ($programas as $bloque) {
            $prog = $bloque['programa'];
            if ($pdf->GetY() > 160) $pdf->AddPage('L');
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(42, 82, 152);
            $pdf->SetTextColor(255);
            $pdf->Cell(0, 7, mb_strtoupper($prog['NombrePrograma']) . '  (' . mb_strtoupper($prog['Sede']) . ' - ' . $prog['GradoAcademico'] . ')', 0, 1, 'L', true, '', 1);
            $pdf->Ln(1);
            self::CabeceraTabla($pdf, $columnas);

            $sub = ['costo' => 0, 'pagado' => 0, 'saldo' => 0];
            foreach ($bloque['estudiantes'] as $i => $e) {
                $abandono = $e['EstadoInscripcion'] === 'ABANDONO';
                self::Rayado($pdf, $i % 2 === 1);
                $pdf->SetTextColor($abandono ? 150 : 51);
                self::FilaTabla($pdf, [
                    [8, $i + 1, 'C'], [72, mb_strtoupper($e['NombreLista']), 'L'], [24, $e['Ci'], 'C'], [22, $e['Celular'] ?: '-', 'C'],
                    [24, $e['TipoPlan'] ?: 'SIN PLAN', 'C'], [17, (int)$e['CuotasPagadas'] . ((int)$e['NumeroCuotas'] > 0 ? '/' . (int)$e['NumeroCuotas'] : ''), 'C'],
                    [28, self::Bs($e['CostoTotal']), 'R'], [28, self::Bs($e['TotalPagado']), 'R'], [28, self::Bs($e['SaldoPendiente']), 'R'],
                    [26, $abandono ? 'ABANDONO' : ((float)$e['SaldoPendiente'] > 0 ? 'CON SALDO' : 'AL DÍA'), 'C'],
                ], true);
                $pdf->SetTextColor(51);
                if (!$abandono) {
                    $sub['costo'] += $e['CostoTotal'];
                    $sub['pagado'] += $e['TotalPagado'];
                    $sub['saldo'] += $e['SaldoPendiente'];
                }
            }
            self::TotalTabla($pdf, [[167, 'SUBTOTAL (sin abandonos):', 'R'], [28, self::Bs($sub['costo']), 'R'], [28, self::Bs($sub['pagado']), 'R'], [28, self::Bs($sub['saldo']), 'R'], [26, '', 'C']]);
            $pdf->Ln(5);
            foreach ($sub as $k => $v) $general[$k] += $v;
            $general['n'] += count($bloque['estudiantes']);
        }

        if (count($programas) > 1) {
            self::TotalTabla($pdf, [[167, 'TOTAL GENERAL (' . count($programas) . ' programas):', 'R'], [28, self::Bs($general['costo']), 'R'], [28, self::Bs($general['pagado']), 'R'], [28, self::Bs($general['saldo']), 'R'], [26, '', 'C']]);
        }
        $pdf->Output('Saldos_Estudiantes_' . date('Ymd_His') . '.pdf', 'I');
    }

    // Consolidado de notas de un programa: estudiantes × módulos, promedio y aprobados
    public static function GenerarReporteNotasPrograma($programaID)
    {
        $pdf = self::NuevoPdf('Consolidado de Notas', 'L');
        $datos = $programaID ? ReportesModelos::ConsolidadoNotas($programaID) : ['programa' => null];
        $prog = $datos['programa'];
        if (!$prog) {
            self::Titulo($pdf, 'CONSOLIDADO DE NOTAS');
            self::SinDatos($pdf, 'Seleccione un programa', 'Consolidado_Notas');
            return;
        }
        $notaMinima = ConfiguracionModelo::NotaAprobacion();
        self::Titulo($pdf, 'CONSOLIDADO DE NOTAS', mb_strtoupper($prog['NombrePrograma']) . '  (' . mb_strtoupper($prog['Sede']) . ' - ' . $prog['GradoAcademico'] . ')'
            . '  |  Nota mínima de aprobación: ' . $notaMinima);

        $modulos = $datos['modulos'];
        if (empty($modulos) || empty($datos['estudiantes'])) {
            self::SinDatos($pdf, empty($modulos) ? 'El programa no tiene módulos' : 'El programa no tiene estudiantes activos', 'Consolidado_Notas');
            return;
        }

        // Ancho útil 277 mm: Nº + Estudiante + C.I. fijos; el resto se reparte entre los módulos
        $anchoModulo = min(20, (277 - 8 - 62 - 22 - 18 - 18) / count($modulos));
        $columnas = [[8, 'Nº'], [62, 'Estudiante'], [22, 'C.I.']];
        foreach ($modulos as $i => $m) {
            $columnas[] = [$anchoModulo, 'M' . ($i + 1)];
        }
        $columnas[] = [18, 'Promedio'];
        $columnas[] = [18, 'Aprobados'];
        self::CabeceraTabla($pdf, $columnas);

        foreach ($datos['estudiantes'] as $i => $e) {
            self::Rayado($pdf, $i % 2 === 1);
            $pdf->Cell(8, 6, $i + 1, 1, 0, 'C', true);
            $pdf->Cell(62, 6, mb_strtoupper($e['Estudiante']), 1, 0, 'L', true, '', 1);
            $pdf->Cell(22, 6, self::CiCompleto($e), 1, 0, 'C', true);
            $suma = 0;
            $aprobados = 0;
            foreach ($modulos as $m) {
                $nota = $e['Notas'][$m['Idmodulo']] ?? null;
                if ($nota !== null) {
                    $suma += $nota;
                    if ($nota >= $notaMinima) $aprobados++;
                }
                // Reprobado en rojo
                $pdf->SetTextColor($nota !== null && $nota < $notaMinima ? 200 : 51, $nota !== null && $nota < $notaMinima ? 30 : 51, $nota !== null && $nota < $notaMinima ? 30 : 51);
                $pdf->Cell($anchoModulo, 6, $nota === null ? '-' : rtrim(rtrim(number_format($nota, 2), '0'), '.'), 1, 0, 'C', true);
                $pdf->SetTextColor(51);
            }
            $calificados = count($e['Notas']);
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Cell(18, 6, $calificados ? number_format($suma / $calificados, 1) : '-', 1, 0, 'C', true);
            $pdf->Cell(18, 6, $aprobados . '/' . count($modulos), 1, 1, 'C', true);
            $pdf->SetFont('helvetica', '', 7);
        }

        // Leyenda: qué módulo es cada columna
        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 5, 'Módulos', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 7);
        foreach ($modulos as $i => $m) {
            $pdf->Cell(138, 4.5, 'M' . ($i + 1) . ': ' . ($m['CodigoModulo'] ? $m['CodigoModulo'] . ' - ' : '') . mb_strtoupper($m['NombreModulo']), 0, $i % 2 === 1 ? 1 : 0, 'L', false, '', 1);
        }
        $pdf->Ln(6);
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->Cell(0, 4, '"-" = módulo sin nota registrada. Las notas en rojo están por debajo de la nota mínima. El promedio es de los módulos calificados.', 0, 1, 'L');

        $pdf->Output('Consolidado_Notas_' . date('Ymd_His') . '.pdf', 'I');
    }

    // ---------------------------------------------------------------------
    // CUENTAS
    // ---------------------------------------------------------------------

    // Cada pago recibido, con su estudiante, voucher y cuenta
    public static function GenerarReporteMovimientos(array $f)
    {
        $pdf = self::NuevoPdf('Movimientos por Cuenta', 'L');
        self::Titulo($pdf, 'MOVIMIENTOS POR CUENTA', self::TextoFiltros($f) . '  |  ' . self::TextoPeriodo($f['desde'] ?? null, $f['hasta'] ?? null));

        $movimientos = ReportesModelos::MovimientosPagados($f);
        if (empty($movimientos)) {
            self::SinDatos($pdf, 'No hay pagos con los filtros seleccionados', 'Movimientos_Cuenta');
            return;
        }

        $columnas = [[8, 'Nº'], [18, 'Fecha'], [21, 'Concepto'], [60, 'Estudiante'], [20, 'C.I.'], [58, 'Programa'], [38, 'Cuenta'], [28, 'Voucher'], [26, 'Monto (Bs.)']];
        self::CabeceraTabla($pdf, $columnas);
        $porConcepto = [];
        $total = 0;
        foreach ($movimientos as $i => $m) {
            self::Rayado($pdf, $i % 2 === 1);
            $concepto = $m['Concepto'] === 'CUOTAS' ? 'CUOTA' . ($m['NumeroCuota'] ? ' ' . $m['NumeroCuota'] : '') : $m['Concepto'];
            self::FilaTabla($pdf, [
                [8, $i + 1, 'C'], [18, self::Fecha($m['Fecha']), 'C'], [21, $concepto, 'C'], [60, mb_strtoupper($m['Estudiante']), 'L'],
                [20, self::CiCompleto($m), 'C'], [58, mb_strtoupper($m['NombrePrograma']) . ' (' . $m['Sede'] . ')', 'L'],
                [38, $m['NumeroCuenta'] ? $m['Banco'] . ' ' . $m['NumeroCuenta'] : 'SIN CUENTA', 'L'], [28, $m['Voucher'] ?: '-', 'C'], [26, self::Bs($m['Monto']), 'R'],
            ], true);
            $porConcepto[$m['Concepto']] = ($porConcepto[$m['Concepto']] ?? ['n' => 0, 'monto' => 0]);
            $porConcepto[$m['Concepto']]['n']++;
            $porConcepto[$m['Concepto']]['monto'] += $m['Monto'];
            $total += $m['Monto'];
        }

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(234, 239, 247);
        $pdf->SetTextColor(30, 60, 114);
        foreach (['MATRICULA', 'CUOTAS', 'CLINICA'] as $concepto) {
            if (!isset($porConcepto[$concepto])) continue;
            $pdf->Cell(251, 6, 'SUBTOTAL ' . $concepto . ' (' . $porConcepto[$concepto]['n'] . ' pagos):', 1, 0, 'R', true);
            $pdf->Cell(26, 6, self::Bs($porConcepto[$concepto]['monto']), 1, 1, 'R', true);
        }
        self::TotalTabla($pdf, [[251, 'TOTAL RECAUDADO (' . count($movimientos) . ' pagos):', 'R'], [26, self::Bs($total), 'R']]);

        $pdf->Output('Movimientos_Cuenta_' . date('Ymd_His') . '.pdf', 'I');
    }

    // Cuentas de cada programa: resumen por cuenta y, debajo, los pagos que entraron a cada una
    public static function GenerarReporteCuentasPrograma($sede = null, $programaID = null, $fechaDesde = null, $fechaHasta = null)
    {
        $pdf = self::NuevoPdf('Cuentas del Programa', 'L');
        self::Titulo($pdf, 'CUENTAS DEL PROGRAMA', self::TextoFiltros(['sede' => $sede, 'programaID' => $programaID])
            . '  |  Recaudado: ' . self::TextoPeriodo($fechaDesde, $fechaHasta) . '  |  Por cobrar: órdenes pendientes a la fecha');

        // Resumen (con lo por cobrar) y detalle de pagos, agrupados por programa
        $resumen = [];
        foreach (ReportesModelos::RecaudacionPorCuenta($sede, $programaID, $fechaDesde, $fechaHasta) as $c) {
            $resumen[$c['ProgramaID']][] = $c;
        }
        $pagos = [];
        foreach (ReportesModelos::MovimientosPagados(['sede' => $sede, 'programaID' => $programaID, 'desde' => $fechaDesde, 'hasta' => $fechaHasta]) as $m) {
            $pagos[$m['ProgramaID']][$m['NumeroCuenta'] ?? ''][] = $m;
        }

        $programas = array_filter(ReportesModelos::ListaProgramas($sede), function ($p) use ($programaID, $resumen) {
            return (!$programaID || (int)$p['ProgramaID'] === (int)$programaID) && isset($resumen[$p['ProgramaID']]);
        });
        if (empty($programas)) {
            self::SinDatos($pdf, 'No hay cuentas ni pagos con los filtros seleccionados', 'Cuentas_Programa');
            return;
        }

        $primero = true;
        foreach ($programas as $prog) {
            if (!$primero) $pdf->AddPage('L');
            $primero = false;

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(42, 82, 152);
            $pdf->SetTextColor(255);
            $pdf->Cell(0, 8, mb_strtoupper($prog['NombrePrograma']) . '  (' . mb_strtoupper($prog['Sede']) . ' - ' . $prog['GradoAcademico'] . ')', 0, 1, 'L', true, '', 1);
            $pdf->Ln(2);

            // Resumen: una fila por cuenta, de matrícula a clínica según para qué está configurada
            // (una cuenta puede recibir pagos de otro concepto si se eligió en la orden)
            $orden = ['MATRICULA' => 1, 'CUOTAS' => 2, 'CLINICA' => 3];
            $cuentas = $resumen[$prog['ProgramaID']];
            foreach ($cuentas as &$c) {
                $c['CuentaDe'] = $c['TipoCuenta'] ?? $c['Concepto'];
            }
            unset($c);
            usort($cuentas, function ($a, $b) use ($orden) {
                return [$orden[$a['CuentaDe']] ?? 9, -(int)$a['Principal'], -(float)$a['Recaudado']] <=> [$orden[$b['CuentaDe']] ?? 9, -(int)$b['Principal'], -(float)$b['Recaudado']];
            });
            self::CabeceraTabla($pdf, [[26, 'Cuenta de'], [64, 'Banco'], [35, 'Nº Cuenta'], [28, 'Tipo'], [18, 'Pagos'], [36, 'Recaudado (Bs.)'], [36, 'Por cobrar (Bs.)'], [34, 'Último pago']]);
            $suma = ['n' => 0, 'rec' => 0, 'cob' => 0];
            foreach ($cuentas as $i => $c) {
                $sinCuenta = $c['CuentaID'] === null;
                $tipo = $sinCuenta ? '-' : ((int)$c['EstadoCuenta'] !== 1 ? 'INACTIVA' : ($c['Principal'] ? 'PRINCIPAL' : 'ALTERNA'));
                if ($sinCuenta) {
                    $pdf->SetFillColor(253, 235, 235);
                } else {
                    self::Rayado($pdf, $i % 2 === 1);
                }
                self::FilaTabla($pdf, [
                    [26, $c['CuentaDe'], 'C'], [64, $sinCuenta ? 'SIN CUENTA CONFIGURADA' : mb_strtoupper($c['Banco']), 'L'], [35, $sinCuenta ? '-' : $c['NumeroCuenta'], 'C'],
                    [28, $tipo, 'C'], [18, $c['NumPagos'], 'C'], [36, self::Bs($c['Recaudado']), 'R'], [36, self::Bs($c['PorCobrar']), 'R'], [34, self::Fecha($c['UltimoPago']), 'C'],
                ], true);
                $suma['n'] += $c['NumPagos'];
                $suma['rec'] += $c['Recaudado'];
                $suma['cob'] += $c['PorCobrar'];
            }
            self::TotalTabla($pdf, [[153, 'TOTAL DEL PROGRAMA:', 'R'], [18, $suma['n'], 'C'], [36, self::Bs($suma['rec']), 'R'], [36, self::Bs($suma['cob']), 'R'], [34, '', 'C']]);
            $pdf->Ln(5);

            // Detalle: los pagos de cada cuenta, en el mismo orden que el resumen
            $pagosPrograma = $pagos[$prog['ProgramaID']] ?? [];
            $ordenCuentas = array_flip(array_map(function ($c) { return (string)($c['NumeroCuenta'] ?? ''); }, $cuentas));
            uksort($pagosPrograma, function ($a, $b) use ($ordenCuentas) {
                return ($ordenCuentas[(string)$a] ?? 99) <=> ($ordenCuentas[(string)$b] ?? 99);
            });
            foreach ($pagosPrograma as $numeroCuenta => $lista) {
                if ($pdf->GetY() > 165) $pdf->AddPage('L');
                $pdf->SetFont('helvetica', 'B', 8.5);
                $pdf->SetFillColor(234, 239, 247);
                $pdf->SetTextColor(30, 60, 114);
                $tipos = implode(', ', array_unique(array_column($lista, 'TipoCuenta')));
                $pdf->Cell(0, 7, $numeroCuenta === '' ? 'PAGOS SIN CUENTA CONFIGURADA' : 'CUENTA Nº ' . $numeroCuenta . ' - ' . mb_strtoupper($lista[0]['Banco']) . ($tipos ? '  (' . $tipos . ')' : ''), 0, 1, 'L', true);
                $pdf->Ln(1);
                self::CabeceraTabla($pdf, [[8, 'Nº'], [20, 'Fecha'], [24, 'Concepto'], [110, 'Estudiante'], [26, 'C.I.'], [50, 'Voucher'], [39, 'Monto (Bs.)']], 7);
                $total = 0;
                foreach ($lista as $i => $m) {
                    self::Rayado($pdf, $i % 2 === 1);
                    $concepto = $m['Concepto'] === 'CUOTAS' ? 'CUOTA' . ($m['NumeroCuota'] ? ' ' . $m['NumeroCuota'] : '') : $m['Concepto'];
                    self::FilaTabla($pdf, [[8, $i + 1, 'C'], [20, self::Fecha($m['Fecha']), 'C'], [24, $concepto, 'C'], [110, mb_strtoupper($m['Estudiante']), 'L'],
                        [26, self::CiCompleto($m), 'C'], [50, $m['Voucher'] ?: '-', 'C'], [39, self::Bs($m['Monto']), 'R']], true);
                    $total += $m['Monto'];
                }
                self::TotalTabla($pdf, [[238, 'SUBTOTAL CUENTA (' . count($lista) . ' pagos):', 'R'], [39, self::Bs($total), 'R']], 6);
                $pdf->Ln(4);
            }
        }

        $pdf->Output('Cuentas_Programa_' . date('Ymd_His') . '.pdf', 'I');
    }

    // Lo recaudado mes a mes, separado por concepto
    public static function GenerarReporteIngresosMes(array $f)
    {
        $pdf = self::NuevoPdf('Ingresos por Mes');
        self::Titulo($pdf, 'INGRESOS POR MES', self::TextoFiltros($f) . '  |  ' . self::TextoPeriodo($f['desde'] ?? null, $f['hasta'] ?? null));

        $movimientos = ReportesModelos::MovimientosPagados($f);
        if (empty($movimientos)) {
            self::SinDatos($pdf, 'No hay pagos con los filtros seleccionados', 'Ingresos_Mes');
            return;
        }

        $meses = [];
        foreach ($movimientos as $m) {
            $mes = substr($m['Fecha'], 0, 7);
            $meses[$mes] = $meses[$mes] ?? ['MATRICULA' => 0, 'CUOTAS' => 0, 'CLINICA' => 0, 'n' => 0];
            $meses[$mes][$m['Concepto']] += $m['Monto'];
            $meses[$mes]['n']++;
        }
        ksort($meses);

        $nombres = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                    '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
        $columnas = [[40, 'Mes'], [30, 'Matrícula (Bs.)'], [30, 'Cuotas (Bs.)'], [30, 'Clínica (Bs.)'], [32, 'Total (Bs.)'], [18, 'Pagos']];
        self::CabeceraTabla($pdf, $columnas);
        $suma = ['MATRICULA' => 0, 'CUOTAS' => 0, 'CLINICA' => 0, 'n' => 0];
        $i = 0;
        foreach ($meses as $mes => $t) {
            self::Rayado($pdf, $i++ % 2 === 1);
            $totalMes = $t['MATRICULA'] + $t['CUOTAS'] + $t['CLINICA'];
            self::FilaTabla($pdf, [
                [40, $nombres[substr($mes, 5, 2)] . ' ' . substr($mes, 0, 4), 'L'], [30, self::Bs($t['MATRICULA']), 'R'], [30, self::Bs($t['CUOTAS']), 'R'],
                [30, self::Bs($t['CLINICA']), 'R'], [32, self::Bs($totalMes), 'R'], [18, $t['n'], 'C'],
            ], true);
            foreach ($suma as $k => $v) $suma[$k] += $t[$k];
        }
        self::TotalTabla($pdf, [[40, 'TOTAL', 'L'], [30, self::Bs($suma['MATRICULA']), 'R'], [30, self::Bs($suma['CUOTAS']), 'R'], [30, self::Bs($suma['CLINICA']), 'R'],
            [32, self::Bs($suma['MATRICULA'] + $suma['CUOTAS'] + $suma['CLINICA']), 'R'], [18, $suma['n'], 'C']]);

        if (count($meses) > 1) {
            $pdf->Ln(3);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 5, 'Promedio mensual: Bs. ' . self::Bs(($suma['MATRICULA'] + $suma['CUOTAS'] + $suma['CLINICA']) / count($meses)) . ' (' . count($meses) . ' meses con pagos)', 0, 1, 'R');
        }

        $pdf->Output('Ingresos_Mes_' . date('Ymd_His') . '.pdf', 'I');
    }
}
?>
