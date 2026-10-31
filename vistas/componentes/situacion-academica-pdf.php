<?php
/**
 * Situación académica de un estudiante en un programa (hoja carta), con el formato del
 * documento que usaba Posgrado en Word: encabezado institucional, programa, nombre, tabla
 * de módulos con puntuación y situación, y firma del Coordinador.
 * Se abre desde Buscar por Estudiante: ?estudianteID=&programaID=
 */
date_default_timezone_set('America/La_Paz');

// Cualquier aviso suelto antes del PDF lo corrompería: se atrapa y se descarta
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Solo el personal (los mismos que ven Buscar por Estudiante)
if (empty($_SESSION['Validar']) || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
    ob_end_clean();
    http_response_code(403);
    die('Acceso denegado');
}

require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once __DIR__ . '/../../modelos/calificacion.modelo.php';
require_once __DIR__ . '/../../modelos/autoridad.modelo.php';
require_once __DIR__ . '/../../modelos/institucion.modelo.php'; // datos y logos de la institución (encabezado)

$estudianteID = (int)($_GET['estudianteID'] ?? 0);
$programaID = (int)($_GET['programaID'] ?? 0);
$datos = CalificacionModelo::SituacionAcademicaModelo($estudianteID, $programaID);
if (!$datos) {
    ob_end_clean();
    http_response_code(404);
    die('El estudiante no está inscrito en ese programa');
}

class SituacionAcademicaPDF extends TCPDF
{
    // Encabezado del documento original: logos UTO y FCS a los lados, textos al centro
    public function Header()
    {
        $ancho = $this->getPageWidth();
        $logo = 20;
        $uto = InstitucionModelo::LogoPdf('LOGO_IZQUIERDO');
        $fcs = InstitucionModelo::LogoPdf('LOGO_DERECHO');
        if ($uto) $this->Image($uto, 18, 10, $logo, $logo, 'PNG', '', '', false, 300, '', false, false, 0, 'CM');
        if ($fcs) $this->Image($fcs, $ancho - 18 - $logo, 10, $logo, $logo, 'PNG', '', '', false, 300, '', false, false, 0, 'CM');

        $this->SetTextColor(0);
        $this->SetY(10);
        $this->setFontStretching(88);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 6, InstitucionModelo::Universidad(), 0, 1, 'C');
        $this->Cell(0, 6, InstitucionModelo::Facultad(), 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, InstitucionModelo::Unidad(), 0, 1, 'C');
        $this->SetFont('helvetica', '', 7.5);
        $this->Cell(0, 3.5, InstitucionModelo::DireccionCompleta(' - ', false), 0, 1, 'C');
        $this->Cell(0, 3.5, InstitucionModelo::Ciudad(), 0, 1, 'C');
        $this->setFontStretching(100);
    }

    // Sin pie (el documento original no lleva número de página)
    public function Footer()
    {
    }
}

$est = $datos['estudiante'];
$prog = $datos['programa'];
$nombre = mb_strtoupper(trim($est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre']), 'UTF-8');
$notaMinima = ConfiguracionModelo::NotaAprobacion();
// Marcado como abandono en Matriculados: los módulos sin nota desde el de abandono salen
// como ABANDONO (sin módulo registrado = desde el primero); los anteriores siguen PENDIENTE
$abandono = ($est['EstadoInscripcion'] ?? '') === 'ABANDONO';
$moduloAbandono = (int)($est['IdModuloAbandono'] ?? 0);

$pdf = new SituacionAcademicaPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema Posgrado FCS');
$pdf->SetTitle('Situación académica - ' . $nombre);
// Márgenes del documento original: 3 cm a los lados, 2.5 cm arriba y abajo
$pdf->SetMargins(30, 42, 30);
$pdf->SetHeaderMargin(10);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();
$anchoUtil = $pdf->getPageWidth() - 60;

// Título y programa
$pdf->SetFont('times', 'B', 18);
$pdf->Cell(0, 9, 'SITUACIÓN ACADÉMICA', 0, 1, 'C');
$pdf->SetFont('times', 'B', 13);
$programa = mb_strtoupper($prog['NombrePrograma'], 'UTF-8') . (!empty($prog['Version']) ? ' - ' . $prog['Version'] : '');
$pdf->MultiCell(0, 6, $programa, 0, 'C');
$pdf->Ln(3);

// Nombre del estudiante
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(0.25);
$pdf->SetFont('times', 'B', 11);
$pdf->Cell($anchoUtil * 0.3, 8, 'NOMBRE:', 1, 0, 'C');
$pdf->SetFont('times', '', 11);
$pdf->Cell($anchoUtil * 0.7, 8, $nombre, 1, 1, 'C', false, '', 1);
$pdf->Ln(3);

// Tabla de módulos (como el original, con más espacio para el nombre del módulo
// para que la tabla y la firma entren en una sola hoja)
$w = [$anchoUtil * 0.06, $anchoUtil * 0.52, $anchoUtil * 0.2, $anchoUtil * 0.22];
$pdf->SetFont('times', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
foreach (['N°', 'MÓDULO', 'PUNTUACIÓN', 'SITUACIÓN'] as $i => $titulo) {
    $pdf->Cell($w[$i], 7, $titulo, 1, $i === 3 ? 1 : 0, 'C', true);
}

foreach ($datos['modulos'] as $i => $m) {
    $nota = $m['Nota'] !== null ? (float)$m['Nota'] : null;
    $dejado = $abandono && (int)$m['Idmodulo'] >= $moduloAbandono;
    $situacion = $nota === null ? ($dejado ? 'ABANDONO' : 'PENDIENTE') : ($nota >= $notaMinima ? 'APROBADO' : 'REPROBADO');
    $puntuacion = $nota === null ? '-' : rtrim(rtrim(number_format($nota, 2), '0'), '.');

    // Alto de la fila según cuántas líneas ocupa el nombre del módulo
    $pdf->SetFont('times', '', 9.5);
    $texto = mb_strtoupper($m['NombreModulo'], 'UTF-8');
    $alto = max(7, $pdf->getNumLines($texto, $w[1] - 2) * 4.2 + 1.5);
    if ($pdf->GetY() + $alto > $pdf->getPageHeight() - 25) {
        $pdf->AddPage();
    }
    $pdf->MultiCell($w[0], $alto, $i + 1, 1, 'C', false, 0, '', '', true, 0, false, true, $alto, 'M');
    $pdf->MultiCell($w[1], $alto, $texto, 1, 'C', false, 0, '', '', true, 0, false, true, $alto, 'M');
    $pdf->SetFont('times', '', 11);
    $pdf->MultiCell($w[2], $alto, $puntuacion, 1, 'C', false, 0, '', '', true, 0, false, true, $alto, 'M');
    $pdf->MultiCell($w[3], $alto, $situacion, 1, 'C', false, 1, '', '', true, 0, false, true, $alto, 'M');
}

// Firma del Coordinador (se edita en Configuración > Autoridades)
if ($pdf->GetY() > $pdf->getPageHeight() - 25 - 36) {
    $pdf->AddPage();
}
$pdf->Ln(20);
$autoridades = AutoridadModelo::ListarModelo();
$pdf->SetFont('times', 'B', 11);
$pdf->Cell(0, 5, AutoridadModelo::NombreParaDocumento($autoridades, 'COORDINADOR'), 0, 1, 'C');
$pdf->Cell(0, 5, InstitucionModelo::CargoFirma(), 0, 1, 'C');
$pdf->Cell(0, 5, InstitucionModelo::Facultad(), 0, 1, 'C');

ob_end_clean();
$pdf->Output('Situacion_Academica_' . $est['Ci'] . '.pdf', 'I');
