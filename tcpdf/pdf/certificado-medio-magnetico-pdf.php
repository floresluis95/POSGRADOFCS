<?php
/**
 * Certificado de Medio Magnético - Carta de Certificación (PDF)
 * Formato: CARTA_FORMATO MEDIO MAGNETICO.docx (entregado por Coordinación, 2026-10-02).
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
// Los certificados los emite solo Administración o Secretaría (un estudiante no puede
// generarlos, y así tampoco saltarse el bloqueo de calificaciones por pago vencido).
if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC'], true)) {
    die('Acceso no autorizado.');
}

require_once '../../vendor/autoload.php';
require_once '../../modelos/certificado.modelo.php';
require_once __DIR__ . '/../../modelos/institucion.modelo.php'; // datos y logos de la institución (encabezado)

date_default_timezone_set('America/La_Paz');

$estudianteID = intval($_POST['estudianteID'] ?? 0);
$programaID = intval($_POST['programaID'] ?? 0);
$tratamiento = $_POST['tratamiento'] ?? 'El Sr.';
$nombre = $_POST['nombre'] ?? '';
$curso = $_POST['curso'] ?? '';
// Con tildes aunque venga de un registro antiguo ("MEDIO FISICO ... MONOGRAFICO")
$tipoTrabajo = str_replace(['FISICO', 'MONOGRAFICO'], ['FÍSICO', 'MONOGRÁFICO'], mb_strtoupper($_POST['tipotrabajo'] ?? '', 'UTF-8'));
$titulo = $_POST['titulo'] ?? '';
$vaucher = CertificadoModelo::NormalizarVoucher($_POST['vaucher'] ?? '');
if ($vaucher === false) {
    die('Error: El N° de voucher solo puede tener letras y números (y un guion entre las dos partes)');
}
$fecha = $_POST['fecha'] ?? '';
$coordinador = $_POST['coordinador'] ?? '';
$director = $_POST['director'] ?? '';

if (empty($nombre) || empty($titulo) || empty($vaucher) || empty($fecha)) {
    die('Error: Faltan datos requeridos para generar la carta');
}

// El N° de voucher es único en todo el sistema (también entre certificados). La
// demostración (completo=1) no registra nada, así que no se revisa.
$voucherRepetido = empty($_POST['completo']) ? CertificadoModelo::VoucherRepetidoMensaje($vaucher, intval($_POST['idDocumento'] ?? 0)) : null;
if ($voucherRepetido) {
    die('Error: ' . htmlspecialchars($voucherRepetido));
}

// Se guardan los datos del formulario para poder reimprimirlo (y corregirlo) desde
// Registro de documentos; con idDocumento es una reimpresión de ese registro.
// En la vista previa (antes de generar) no se registra nada.
if (!CertificadoModelo::EsVistaPrevia()) {
    CertificadoModelo::RegistrarEmisionModelo(
        'MEDIO_MAGNETICO', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null,
        $_POST, intval($_POST['idDocumento'] ?? 0)
    );
}

$mesesEspanolMinuscula = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];
$timestampFecha = strtotime($fecha);
$fechaTexto = 'Oruro, ' . (int) date('j', $timestampFecha) . ' de ' . $mesesEspanolMinuscula[(int) date('n', $timestampFecha)] . ' de ' . date('Y', $timestampFecha);

// ===================================
// GENERACIÓN DEL PDF
// Réplica del formato "CARTA_FORMATO MEDIO MAGNETICO.docx": hoja carta,
// márgenes laterales de 30 mm, membrete en Arial Narrow (aquí Helvetica
// angostada), cuerpo en Times New Roman 12 con interlineado 1,5 y los datos
// alineados a 62,5 mm del margen, como las tabulaciones del Word.
// ===================================
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Certificación Medio Magnético - ' . $nombre);
$pdf->SetSubject('Certificación de entrega de medio físico y digital');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(30, 12, 30);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);

$margenIzq = 30;
$anchoTexto = 215.9 - 30 - 30;   // 155,9 mm
$xValor = $margenIzq + 62.5;     // columna de los datos (tabulación del Word)
$anchoValor = $margenIzq + $anchoTexto - $xValor;
$interlineado = 7.3;             // Times 12 con interlineado 1,5

// ===================================
// MEMBRETE
// ===================================
$logoUTO = InstitucionModelo::LogoPdf('LOGO_IZQUIERDO');
$logoFCS = InstitucionModelo::LogoPdf('LOGO_DERECHO');
if ($logoUTO) {
    $pdf->Image($logoUTO, 32, 12.5, 21.7, 21.7, 'PNG', '', '', false, 300, '', false, false, 0, 'CM');
}
if ($logoFCS) {
    $pdf->Image($logoFCS, 170, 12.5, 18.2, 23.5, 'PNG', '', '', false, 300, '', false, false, 0, 'CM');
}

// Arial Narrow: Helvetica al 82 % de ancho
$pdf->setFontStretching(82);
$pdf->SetY(12.5);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 7.2, InstitucionModelo::Universidad(), 0, 1, 'C');
$pdf->Cell(0, 7.2, InstitucionModelo::Facultad(), 0, 1, 'C');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 5.8, InstitucionModelo::Unidad(), 0, 1, 'C');
$pdf->Ln(2.5);
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 3.6, InstitucionModelo::DireccionCompleta(' - ', false), 0, 1, 'C');
$pdf->Cell(0, 3.6, InstitucionModelo::Ciudad(), 0, 1, 'C');
$pdf->setFontStretching(100);

// Línea gruesa (3 pt) bajo el membrete, de margen a margen
$pdf->Ln(1.5);
$pdf->SetLineWidth(1.06);
$pdf->Line($margenIzq, $pdf->GetY(), $margenIzq + $anchoTexto, $pdf->GetY());
$pdf->SetLineWidth(0.2);

// ===================================
// TÍTULO
// ===================================
$pdf->SetY($pdf->GetY() + 12);
$pdf->SetFont('times', 'B', 15);
$pdf->Cell(0, 8, 'CERTIFICACIÓN', 0, 1, 'C');
$pdf->Ln(8);

// ===================================
// CUERPO
// ===================================
$pdf->SetFont('times', '', 12);
$pdf->MultiCell(0, $interlineado, 'La Dirección de Posgrado de la Universidad Técnica de Oruro certifica que:', 0, 'L', false, 1);

// "Etiqueta:   VALOR" con el valor en la columna de 62,5 mm (si no entra en
// una línea, continúa debajo en la misma columna, como la sangría francesa del Word)
function filaCertificacion($pdf, $etiqueta, $valor, $xValor, $anchoValor, $alto)
{
    $y = $pdf->GetY();
    $pdf->SetFont('times', '', 12);
    $pdf->SetXY(30, $y);
    $pdf->Cell($xValor - 30, $alto, $etiqueta, 0, 0, 'L');
    $pdf->MultiCell($anchoValor, $alto, mb_strtoupper($valor, 'UTF-8'), 0, 'L', false, 1, $xValor, $y);
}

filaCertificacion($pdf, $tratamiento, $nombre, $xValor, $anchoValor, $interlineado);
filaCertificacion($pdf, 'Ha realizado el curso de:', $curso, $xValor, $anchoValor, $interlineado);
filaCertificacion($pdf, 'Ha entregado el respectivo:', $tipoTrabajo, $xValor, $anchoValor, $interlineado);
$pdf->Ln($interlineado);

$pdf->SetFont('times', 'B', 12);
$pdf->MultiCell(0, 5.5, mb_strtoupper($titulo, 'UTF-8'), 0, 'J', false, 1);
$pdf->Ln(3);

$pdf->SetFont('times', '', 12);
$pdf->MultiCell(0, 6, 'Cumpliendo el requisito para tramitar su respectivo Diploma Académico.', 0, 'L', false, 1);
$pdf->Ln(2);
$pdf->MultiCell(0, 6, 'Es cuanto corresponde certificar para fines consiguientes:', 0, 'L', false, 1);
$pdf->Ln(14);

$pdf->Cell(0, 6, $fechaTexto, 0, 1, 'L');

// ===================================
// FIRMAS (dos columnas: Coordinador a la izquierda, Director a la derecha)
// ===================================
$yFirmas = $pdf->GetY() + 26;   // espacio para firmar y sellar
if ($yFirmas + 30 > 279.4 - 20) {
    $pdf->AddPage();
    $yFirmas = 40;
}

function firmaMedioMagnetico($pdf, $x, $y, $ancho, $nombreFirma, array $cargos)
{
    $pdf->SetFont('times', '', 12);
    $pdf->SetXY($x, $y);
    $pdf->MultiCell($ancho, 5.5, $nombreFirma, 0, 'C', false, 1, $x, $y);
    $pdf->SetFont('times', 'B', 9);
    foreach ($cargos as $cargo) {
        $pdf->SetX($x);
        $pdf->Cell($ancho, 4, $cargo, 0, 1, 'C');
    }
}

firmaMedioMagnetico($pdf, 26, $yFirmas, 80, $coordinador, [InstitucionModelo::CargoFirma(), InstitucionModelo::Universidad()]);
firmaMedioMagnetico($pdf, 111, $yFirmas, 80, $director, ['DIRECTOR POSGRADO', InstitucionModelo::Universidad()]);

// N° de recibo al pie, a la izquierda
$pdf->SetY($yFirmas + 24);
$pdf->SetFont('times', '', 12);
$pdf->Cell(0, 6, 'N° ' . mb_strtoupper($vaucher, 'UTF-8'), 0, 1, 'L');

if (CertificadoModelo::EsVistaPrevia()) {
    CertificadoModelo::MarcarVistaPrevia($pdf, false);
}

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = 'Certificado_Medio_Magnetico_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
