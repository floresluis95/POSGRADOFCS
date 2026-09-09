<?php
/**
 * Certificado de Medio Magnético - Carta de Certificación (PDF)
 * A diferencia de los otros 3 certificados, esta carta se genera completa
 * (con su propio membrete institucional), ya que no se imprime sobre un
 * formato pre-impreso de Posgrado Central.
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida.');
}

require_once '../../vendor/autoload.php';
require_once '../../modelos/certificado.modelo.php';

date_default_timezone_set('America/La_Paz');

$estudianteID = intval($_POST['estudianteID'] ?? 0);
$programaID = intval($_POST['programaID'] ?? 0);
$tratamiento = $_POST['tratamiento'] ?? 'El Sr.';
$nombre = $_POST['nombre'] ?? '';
$curso = $_POST['curso'] ?? '';
$tipoTrabajo = $_POST['tipotrabajo'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$vaucher = $_POST['vaucher'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$coordinador = $_POST['coordinador'] ?? '';
$director = $_POST['director'] ?? '';

if (empty($nombre) || empty($titulo) || empty($vaucher) || empty($fecha)) {
    die('Error: Faltan datos requeridos para generar la carta');
}

CertificadoModelo::RegistrarDocumentoEmitidoModelo(
    'MEDIO_MAGNETICO', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null
);

$mesesEspanolMinuscula = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];
$timestampFecha = strtotime($fecha);
$fechaTexto = 'Oruro, ' . (int) date('j', $timestampFecha) . ' de ' . $mesesEspanolMinuscula[(int) date('n', $timestampFecha)] . ' de ' . date('Y', $timestampFecha);

// ===================================
// GENERACIÓN DEL PDF
// ===================================
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Certificación Medio Magnético - ' . $nombre);
$pdf->SetSubject('Certificación de entrega de medio físico y digital');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(20, 15, 20);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);

// ===================================
// MEMBRETE
// ===================================
$logoUTO = '../../extensiones/imagenespdf/logouto.png';
$logoFCS = '../../extensiones/imagenespdf/logofcs.png';

if (file_exists($logoUTO)) {
    $pdf->Image($logoUTO, 15, 10, 22, 0, 'PNG');
}
if (file_exists($logoFCS)) {
    $pdf->Image($logoFCS, 175, 10, 22, 0, 'PNG');
}

$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetY(12);
$pdf->Cell(0, 5, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 5, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');
$pdf->Cell(0, 5, 'COORDINACION DE POSGRADO - ODONTOLOGIA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 4, 'C. Presidente Montes entre C/ Murguia y Aldana Edificio de Posgrado UTO 2do Piso, Teléfonos: 5237317- Fax 5247110', 0, 1, 'C');
$pdf->Cell(0, 4, 'Oruro – Bolivia', 0, 1, 'C');

$pdf->Ln(3);
$pdf->SetLineWidth(0.6);
$pdf->Line(15, $pdf->GetY(), 197, $pdf->GetY());
$pdf->SetLineWidth(0.2);
$pdf->Ln(16);

// ===================================
// TÍTULO
// ===================================
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 8, 'CERTIFICACIÓN', 0, 1, 'C');
$pdf->Ln(12);

// ===================================
// CUERPO
// ===================================
$xLabel = 20;
$wLabel = 55;
$xValor = 75;
$wValor = 122;

$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, 'La Dirección de Posgrado de la Universidad Técnica de Oruro certifica que:', 0, 'L', false, 1);
$pdf->Ln(8);

$pdf->SetFont('helvetica', '', 11);
$pdf->SetX($xLabel);
$pdf->Cell($wLabel - 20, 6, $tratamiento, 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetX($xValor);
$pdf->MultiCell($wValor, 6, mb_strtoupper($nombre, 'UTF-8'), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 11);
$pdf->SetX($xLabel);
$pdf->Cell($wLabel, 6, 'Ha realizado el curso de:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetX($xValor);
$pdf->MultiCell($wValor, 6, mb_strtoupper($curso, 'UTF-8'), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('helvetica', '', 11);
$pdf->SetX($xLabel);
$pdf->Cell($wLabel, 6, 'Ha entregado el respectivo:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetX($xValor);
$pdf->MultiCell($wValor, 6, mb_strtoupper($tipoTrabajo, 'UTF-8'), 0, 'L');
$pdf->Ln(8);

$pdf->SetFont('helvetica', 'B', 11);
$pdf->MultiCell(0, 6, mb_strtoupper($titulo, 'UTF-8'), 0, 'J', false, 1);
$pdf->Ln(8);

$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, 'Cumpliendo el requisito para tramitar su respectivo Diploma Académico.', 0, 'L', false, 1);
$pdf->Ln(4);
$pdf->MultiCell(0, 6, 'Es cuanto corresponde certificar para fines consiguientes:', 0, 'L', false, 1);
$pdf->Ln(24);

$pdf->Cell(0, 6, $fechaTexto, 0, 1, 'L');
$pdf->Ln(24);

// ===================================
// FIRMAS
// ===================================
$yFirmas = $pdf->GetY();
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY(20, $yFirmas);
$pdf->MultiCell(80, 5, $coordinador . "\nCOORDINADOR DE POSGRADO\nODONTOLOGÍA\nUNIVERSIDAD TÉCNICA DE ORURO", 0, 'C');

$pdf->SetXY(115, $yFirmas);
$pdf->MultiCell(80, 5, $director . "\nDIRECTOR POSGRADO\nUNIVERSIDAD TÉCNICA DE ORURO", 0, 'C');

$pdf->Ln(15);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 6, 'N° ' . mb_strtoupper($vaucher, 'UTF-8'), 0, 1, 'L');

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = 'Certificado_Medio_Magnetico_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
