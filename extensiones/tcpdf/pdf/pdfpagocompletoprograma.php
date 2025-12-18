<?php
/**
 * Comprobante de Pago Completo del Programa - PDF
 * Muestra el detalle del pago completo incluyendo descuentos
 */

// Configurar errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Rutas base
$baseDir = realpath(__DIR__ . '/../../../');
require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/modelos/conexion.modelo.php';

// Validar ID de inscripción
if (!isset($_GET['idinscripcion']) || empty($_GET['idinscripcion'])) {
    die('Error: ID de inscripción no especificado');
}

$idInscripcion = intval($_GET['idinscripcion']);

// Obtener datos del estudiante y pago
try {
    $pdo = Conexion::Conectar();
    $stmt = $pdo->prepare("
        SELECT e.EstudianteID, e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp,
               ep.idInscripcion, ep.ProgramaID, ep.FechaInscripcion,
               ep.costomatricula, ep.montoPagado, ep.montoDescuento, ep.porcentajeDescuento,
               ep.pagoCompleto, ep.nvauchermatricula,
               p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico,
               p.Costo AS CostoTotalPrograma, p.Version, p.NumeroTramite
        FROM estudianteprograma ep
        INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        WHERE ep.idInscripcion = :idinscripcion
    ");
    $stmt->bindParam(':idinscripcion', $idInscripcion, PDO::PARAM_INT);
    $stmt->execute();
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        die('Error: No se encontró información del estudiante');
    }

    // Verificar que sea pago completo
    if ($estudiante['pagoCompleto'] != 1) {
        die('Error: Este estudiante no tiene pago completo registrado');
    }

} catch (PDOException $e) {
    die('Error al obtener datos: ' . $e->getMessage());
}

// Calcular montos
$pagoCompleto = intval($estudiante['pagoCompleto']);
$montoPagado = floatval($estudiante['montoPagado']);
$montoDescuento = floatval($estudiante['montoDescuento']);
$porcentajeDescuento = floatval($estudiante['porcentajeDescuento']);
$costoMatriculaBD = floatval($estudiante['CostoMatricula']); // Costo de matrícula del programa
$costoProgramaBD = floatval($estudiante['CostoTotalPrograma']); // Costo del programa

// El costo TOTAL original es: Matrícula + Programa
// Este es el monto ANTES del descuento
$costoTotalOriginal = $costoMatriculaBD + $costoProgramaBD;

// Verificación de coherencia: costoTotalOriginal - montoDescuento debe ser igual a montoPagado
// Si hay una diferencia mayor a 0.01, usar montoPagado + montoDescuento como referencia
$diferencia = abs(($costoTotalOriginal - $montoDescuento) - $montoPagado);
if ($diferencia > 0.01) {
    // Si hay inconsistencia, recalcular el costo original sumando monto pagado + descuento
    $costoTotalOriginal = $montoPagado + $montoDescuento;
}

// Crear PDF
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS');
$nombreCompleto = $estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];
$pdf->SetTitle('Comprobante de Pago Completo - ' . $nombreCompleto);
$pdf->SetSubject('Comprobante de Pago Completo del Programa');

// Quitar header y footer automáticos
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

// Agregar página
$pdf->AddPage();

// ========================================
// ENCABEZADO CON LOGOS
// ========================================
$pdf->Image('../../imagenespdf/logouto.png', 15, 10, 25);
$pdf->Image('../../imagenespdf/logofcs.png', 175, 10, 20);

$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, 15);
$pdf->Cell(0, 8, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 11);
$pdf->SetXY(15, 22);
$pdf->Cell(0, 6, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');

$pdf->SetXY(15, 28);
$pdf->Cell(0, 6, 'POSGRADO - ODONTOLOGÍA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(15, 34);
$pdf->Cell(0, 4, 'Av. del Minero Barrio San José "Excomibol" - Tel. 5237317', 0, 1, 'C');

// Línea decorativa
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.8);
$pdf->Line(15, 42, 195, 42);

// ========================================
// TÍTULO DEL DOCUMENTO
// ========================================
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY(15, 46);
$pdf->Cell(0, 10, 'COMPROBANTE DE PAGO COMPLETO', 0, 1, 'C');

// Número de comprobante
$numeroComprobante = 'PC-' . str_pad($idInscripcion, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(15, 56);
$pdf->Cell(0, 5, 'N° Comprobante: ' . $numeroComprobante, 0, 1, 'C');
$pdf->SetXY(15, 61);
$pdf->Cell(0, 5, 'Fecha de emisión: ' . date('d/m/Y H:i'), 0, 1, 'C');

// ========================================
// DATOS DEL ESTUDIANTE
// ========================================
$y = 72;

// Recuadro de información
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.3);
$pdf->Rect(15, $y, 180, 35, 'DF');

$y += 5;

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY(20, $y);
$pdf->Cell(0, 6, 'DATOS DEL ESTUDIANTE', 0, 1, 'L');

$y += 8;

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(20, $y);
$pdf->Cell(40, 5, 'Estudiante:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX(60);
$pdf->Cell(0, 5, strtoupper($nombreCompleto), 0, 1, 'L');

$y += 6;

$ci = $estudiante['Ci'];
if (!empty($estudiante['Complemento'])) {
    $ci .= '-' . $estudiante['Complemento'];
}
$ci .= ' ' . $estudiante['Exp'];

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(20, $y);
$pdf->Cell(40, 5, 'C.I.:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX(60);
$pdf->Cell(0, 5, $ci, 0, 1, 'L');

$y += 6;

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(20, $y);
$pdf->Cell(40, 5, 'Programa:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX(60);
$pdf->MultiCell(130, 5, strtoupper($estudiante['NombrePrograma']), 0, 'L');

$y += 6;

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(20, $y);
$pdf->Cell(40, 5, 'Código:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX(60);
$pdf->Cell(50, 5, $estudiante['CodigoPrograma'], 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetX(120);
$pdf->Cell(30, 5, 'Grado:', 0, 0, 'L');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX(150);
$pdf->Cell(0, 5, $estudiante['GradoAcademico'], 0, 1, 'L');

// ========================================
// DETALLE DEL PAGO
// ========================================
$y = 115;

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY(15, $y);
$pdf->Cell(0, 8, 'DETALLE DEL PAGO COMPLETO', 0, 1, 'L');

$y += 12;

// Tabla de desglose
$pdf->SetFillColor(231, 243, 255); // Fondo azul claro
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);

// 1. Costo de Matrícula
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(15, $y);
$pdf->Cell(130, 8, '1. MATRÍCULA DEL PROGRAMA:', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(50, 8, 'Bs. ' . number_format($costoMatriculaBD, 2), 1, 1, 'R', true);

$y += 8;

// 2. Costo del Programa
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(15, $y);
$pdf->Cell(130, 8, '2. COSTO DEL PROGRAMA:', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(50, 8, 'Bs. ' . number_format($costoProgramaBD, 2), 1, 1, 'R', true);

$y += 8;

// 3. Subtotal (Matrícula + Programa)
$pdf->SetFillColor(255, 243, 205); // Fondo amarillo claro
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(15, $y);
$pdf->Cell(130, 8, 'SUBTOTAL (MATRÍCULA + PROGRAMA):', 1, 0, 'L', true);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(50, 8, 'Bs. ' . number_format($costoTotalOriginal, 2), 1, 1, 'R', true);

$y += 8;

// 4. Descuento aplicado (si existe)
if ($montoDescuento > 0) {
    $pdf->SetFillColor(212, 237, 218); // Fondo verde claro
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(40, 167, 69);
    $pdf->SetXY(15, $y);
    $pdf->Cell(130, 8, 'DESCUENTO APLICADO (' . number_format($porcentajeDescuento, 1) . '%):', 1, 0, 'L', true);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(50, 8, '- Bs. ' . number_format($montoDescuento, 2), 1, 1, 'R', true);

    $y += 8;
}

// Línea de separación más gruesa
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $y, 195, $y);
$y += 0.5;

// Total a pagar
$pdf->SetFillColor(102, 126, 234);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(15, $y);
$pdf->Cell(130, 10, 'TOTAL PAGADO:', 1, 0, 'L', true);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(50, 10, 'Bs. ' . number_format($montoPagado, 2), 1, 1, 'R', true);

// ========================================
// INFORMACIÓN ADICIONAL
// ========================================
$y += 15;

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(15, $y);
$pdf->Cell(0, 6, 'INFORMACIÓN DEL PAGO:', 0, 1, 'L');

$y += 8;

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, $y);
$pdf->Cell(50, 5, 'Fecha de inscripción:', 0, 0, 'L');
$pdf->Cell(0, 5, date('d/m/Y', strtotime($estudiante['FechaInscripcion'])), 0, 1, 'L');

$y += 6;

$pdf->SetXY(15, $y);
$pdf->Cell(50, 5, 'N° Voucher:', 0, 0, 'L');
$pdf->Cell(0, 5, $estudiante['nvauchermatricula'] ?: 'N/A', 0, 1, 'L');

// ========================================
// NOTA IMPORTANTE
// ========================================
$y += 12;

$pdf->SetFillColor(255, 243, 205);
$pdf->SetDrawColor(255, 193, 7);
$pdf->SetLineWidth(0.5);
$pdf->Rect(15, $y, 180, 20, 'DF');

$y += 5;

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(133, 100, 4);
$pdf->SetXY(20, $y);
$pdf->Cell(0, 5, 'NOTA IMPORTANTE:', 0, 1, 'L');

$y += 6;

$pdf->SetFont('helvetica', '', 9);
$pdf->SetXY(20, $y);
$pdf->MultiCell(170, 4, 'Este comprobante certifica que el estudiante ha cancelado el PAGO COMPLETO del programa, incluyendo la matrícula y todos los módulos del programa de posgrado. El descuento aplicado ya está reflejado en el monto total pagado.', 0, 'L');

// ========================================
// SELLO Y FIRMA
// ========================================
$y = 220;

// Línea para firma
$pdf->SetDrawColor(100, 100, 100);
$pdf->SetLineWidth(0.3);
$pdf->Line(25, $y, 85, $y);
$pdf->Line(115, $y, 175, $y);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY(25, $y + 2);
$pdf->Cell(60, 5, 'COORDINADOR DE POSGRADO', 0, 0, 'C');
$pdf->SetXY(115, $y + 2);
$pdf->Cell(60, 5, 'SELLO DE LA INSTITUCIÓN', 0, 0, 'C');

// ========================================
// PIE DE PÁGINA
// ========================================
$pdf->SetY(-20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 4, 'Este documento es un comprobante oficial del pago completo del programa', 0, 1, 'C');
$pdf->Cell(0, 4, 'Sistema de Gestión de Posgrado FCS - Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

// Generar PDF
$pdf->Output('Comprobante_Pago_Completo_' . $idInscripcion . '_' . date('Ymd') . '.pdf', 'I');
?>
