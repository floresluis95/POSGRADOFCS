<?php
/**
 * Certificado de Revisión - PDF
 * Informe al Director de Posgrado de que el estudiante cumplió todos los requisitos
 * (para otorgarle el grado), con la conformidad del despacho debajo.
 * Se imprime SOLO el texto, sobre el papel pre-impreso, en hoja carta vertical.
 * Las coordenadas se midieron sobre el formato en Word que usa Posgrado
 * (CERTIFICADO DE REVISION_*.docx); si algo se desalinea en el papel real, ajustar
 * las constantes del bloque "COORDENADAS".
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida.');
}
// Los certificados los emite solo Administración o Secretaría
if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC'], true)) {
    die('Acceso no autorizado.');
}

require_once '../../vendor/autoload.php';
require_once '../../modelos/certificado.modelo.php';

date_default_timezone_set('America/La_Paz');

$estudianteID = intval($_POST['estudianteID'] ?? 0);
$programaID = intval($_POST['programaID'] ?? 0);
$tratamiento = trim($_POST['tratamiento'] ?? 'la señora');
$nombre = trim($_POST['nombre'] ?? '');
$curso = trim($_POST['curso'] ?? '');
$fecha = $_POST['fecha'] ?? '';
$vaucher = CertificadoModelo::NormalizarVoucher($_POST['vaucher'] ?? '');
if ($vaucher === false) {
    die('Error: El N° de voucher solo puede tener letras y números (y un guion entre las dos partes)');
}

if ($nombre === '' || $curso === '' || empty($fecha) || !strtotime($fecha)) {
    die('Error: Faltan datos requeridos para generar el certificado');
}

// Se guardan los datos del formulario para poder reimprimirlo (y corregirlo) desde
// Registro de documentos; con idDocumento es una reimpresión de ese registro.
// En la vista previa (antes de generar) no se registra nada.
if (!CertificadoModelo::EsVistaPrevia()) {
    CertificadoModelo::RegistrarEmisionModelo(
        'REVISION', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null,
        $_POST, intval($_POST['idDocumento'] ?? 0)
    );
}

$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$t = strtotime($fecha);
$fechaTexto = date('j', $t) . ' de ' . $meses[(int)date('n', $t)] . ' de ' . date('Y', $t);

$nombreTexto = mb_strtoupper($nombre, 'UTF-8');
$cursoTexto = mb_strtoupper($curso, 'UTF-8');

// Según el grado, como en los formatos en Word de Posgrado:
//  - Maestría: "...SALINAS CASTILLO ha cumplido..." (sin coma) y el grado que se
//    otorga es "MAGISTER EN ORTODONCIA" (el programa dice "MAESTRIA EN ORTODONCIA").
//  - Especialidad (y los demás): "...ALA VALDIVIA, ha cumplido..." y el grado es
//    el mismo texto del programa ("ESPECIALIDAD EN ARMONIZACION OROFACIAL").
$esMaestria = (bool)preg_match('/^MAESTR[IÍ]A\b/u', $cursoTexto);
$comaTrasNombre = $esMaestria ? '' : ',';
$gradoOtorgado = $esMaestria ? preg_replace('/^MAESTR[IÍ]A\b/u', 'MAGISTER', $cursoTexto) : $cursoTexto;
$e = function ($texto) { return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); };

// ===================================
// COORDENADAS (mm) - Hoja carta vertical (215.9 x 279.4 mm)
// Medidas sobre el formato en Word: X_* es el inicio del texto e Y_* la parte de
// arriba de la primera línea de cada bloque.
// ===================================
$X_INFORME = 34.8;      $Y_INFORME = 95.8;      $W_TEXTO = 146.3;
$X_CONFORMIDAD = 34.6;  $Y_CONFORMIDAD = 197.4;
$INTERLINEA = 5.66;     // 1,16 líneas de Times 12, como en el Word
$ESPACIO_PARRAFO = 2.82; // 8 pt después de cada párrafo

// Lo que va antes de la fecha en cada bloque, tal como está en el formato en Word
// (completa lo que ya viene impreso en el papel)
$ANTES_FECHA_INFORME = 'A, ';
$ANTES_FECHA_CONFORMIDAD = 'á, ';

// ===================================
// GENERACIÓN DEL PDF
// ===================================
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Certificado de Revisión - ' . $nombre);
$pdf->SetSubject('Certificado de Revisión');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->setCellPaddings(0, 0, 0, 0);
$pdf->setCellMargins(0, 0, 0, 0);
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('times', '', 12);
// La Times New Roman de Word es un 1,3 % más angosta que la Times de TCPDF; así el
// texto justificado corta las líneas en las mismas palabras que el formato en Word.
$pdf->setFontStretching(98.66);
// Alto de línea = tamaño de letra x proporción (12 pt x 1,337 = 5,66 mm)
$pdf->setCellHeightRatio($INTERLINEA / (12 * 25.4 / 72));

// Escribe los párrafos (HTML simple, justificado) uno debajo de otro desde Y
$parrafos = function (array $lista, $x, $y) use ($pdf, $W_TEXTO, $ESPACIO_PARRAFO) {
    foreach ($lista as $html) {
        $pdf->writeHTMLCell($W_TEXTO, 0, $x, $y, $html, 0, 1, false, true, 'J');
        $y = $pdf->GetY() + $ESPACIO_PARRAFO;
    }
};

$parrafos([
    $e($ANTES_FECHA_INFORME . $fechaTexto),
    '&nbsp;',
    '<b>SEÑOR DIRECTOR DE POSGRADO</b>',
    'Revisada la documentación adjunta, se evidencia que ' . $e($tratamiento) . ' ' . $e($nombreTexto)
        . $comaTrasNombre . ' ha cumplido con todos los requisitos exigidos por la Universidad Técnica de Oruro, de la <b>'
        . $e($cursoTexto) . '</b>.',
    'Por tanto, corresponde otorgarle el GRADO ACADÉMICO DE ' . $e($gradoOtorgado) . '.',
    'Es cuanto me permito informar a su autoridad, para fines consiguientes.',
], $X_INFORME, $Y_INFORME);

$parrafos([$e($ANTES_FECHA_CONFORMIDAD . $fechaTexto)], $X_CONFORMIDAD, $Y_CONFORMIDAD);

// La conformidad es texto fijo: se dibuja con los mismos dos renglones del formato
// en Word (el primero justificado, cortando en "pase"). Así no depende del
// interletraje de Word, que la Times de TCPDF no tiene.
$yLinea = $pdf->GetY() + $ESPACIO_PARRAFO;
$pdf->SetXY($X_CONFORMIDAD, $yLinea);
$pdf->Cell($W_TEXTO, $INTERLINEA, 'Este despacho da conformidad al informe que antecede, para fines consiguientes pase', 0, 1, 'J');
$pdf->SetXY($X_CONFORMIDAD, $yLinea + $INTERLINEA);
$pdf->Cell($W_TEXTO, $INTERLINEA, 'a conocimiento del señor Vicerrector de la Universidad Técnica de Oruro.', 0, 1, 'L');

if (CertificadoModelo::EsVistaPrevia()) {
    CertificadoModelo::MarcarVistaPrevia($pdf, true);
}

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = 'Certificado_Revision_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
