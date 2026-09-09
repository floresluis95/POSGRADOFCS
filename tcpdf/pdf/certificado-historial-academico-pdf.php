<?php
/**
 * Historial Académico - PDF
 * Se imprime SOLO el contenido (sin membrete propio) porque el papel ya viene
 * pre-impreso desde Posgrado Central (Universidad Técnica de Oruro), en hoja
 * oficio horizontal (14 x 8.5 in).
 * Las coordenadas se calibraron a partir del documento de referencia que ya
 * usan (HISTORIAL_ACADEMICO.docx) y de una copia impresa de ejemplo; es un
 * primer ajuste y es normal que necesite un retoque de milímetros luego de
 * una impresión de prueba sobre el papel real (ver bloque "COORDENADAS").
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
$nombre = $_POST['nombre'] ?? '';
$programa = $_POST['programa'] ?? '';
$nivel = $_POST['nivel'] ?? '';
$vaucher = $_POST['vaucher'] ?? '';
$fecha = $_POST['fecha'] ?? '';

if (!$estudianteID || !$programaID || empty($nombre) || empty($fecha)) {
    die('Error: Faltan datos requeridos para generar el historial académico');
}

// Los módulos y notas se vuelven a consultar desde la BD (no se confía en el POST)
$modulos = CertificadoModelo::ObtenerModulosCalificadosModelo($estudianteID, $programaID);

if (empty($modulos)) {
    die('Error: El estudiante no tiene calificaciones registradas en este programa');
}

CertificadoModelo::RegistrarDocumentoEmitidoModelo(
    'HISTORIAL_ACADEMICO', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null
);

/**
 * "MODULO I" -> "MOD.I" (así se abrevia en el formato pre-impreso)
 */
function siglaModuloHistorial($codigoModulo)
{
    return str_ireplace('MODULO ', 'MOD.', trim($codigoModulo));
}

$mesesEspanolMinuscula = [
    1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
    5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
    9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
];

$timestampFecha = strtotime($fecha);
$fechaTexto = (int) date('j', $timestampFecha) . ' de ' . $mesesEspanolMinuscula[(int) date('n', $timestampFecha)] . ' de ' . date('Y', $timestampFecha);

// ===================================
// COORDENADAS (mm) - Hoja Oficio horizontal (355.6 x 215.9 mm)
// ===================================
$X_NOMBRE = 78;      $Y_NOMBRE = 27;      $W_NOMBRE = 130;
$X_VAUCHER = 270;    $Y_VAUCHER = 27;     $W_VAUCHER = 37;
$X_PROGRAMA = 53;    $Y_PROGRAMA = 34;    $W_PROGRAMA = 92;
$X_NIVEL = 47;       $Y_NIVEL = 40;       $W_NIVEL = 45;
$X_FECHA = 248;      $Y_FECHA = 40;       $W_FECHA = 79;

$Y_TABLA = 66;
// Ancho de cada columna dentro de un bloque (izquierdo o derecho)
$W_SIGLA = 22.0;
$W_NOMBREMOD = 83.4;
$W_NUM = 12.6;
$W_FECHAMOD = 25.9;
// Posicion inicial (X) de cada bloque de 6 modulos
$X_SIGLA_IZQ = 31.6;   // Bloque izquierdo: MOD.I - MOD.VI
$X_SIGLA_DER = 181.5;  // Bloque derecho: MOD.VII - MOD.XII

// ===================================
// GENERACIÓN DEL PDF
// ===================================
$pdf = new TCPDF('L', 'mm', array(355.6, 215.9), true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Historial Académico - ' . $nombre);
$pdf->SetSubject('Historial Académico');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);

$modulosPorHoja = array_chunk($modulos, 12);

foreach ($modulosPorHoja as $chunk) {
    $pdf->AddPage();
    $pdf->SetTextColor(0, 0, 0);

    // Bloque de datos del encabezado (se repite en cada hoja)
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetXY($X_NOMBRE, $Y_NOMBRE);
    $pdf->Cell($W_NOMBRE, 6, mb_strtoupper($nombre, 'UTF-8'), 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY($X_VAUCHER, $Y_VAUCHER);
    $pdf->Cell($W_VAUCHER, 6, mb_strtoupper($vaucher, 'UTF-8'), 0, 0, 'L');

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetXY($X_PROGRAMA, $Y_PROGRAMA);
    $pdf->Cell($W_PROGRAMA, 6, mb_strtoupper($programa, 'UTF-8'), 0, 0, 'L');

    $pdf->SetXY($X_NIVEL, $Y_NIVEL);
    $pdf->Cell($W_NIVEL, 6, mb_strtoupper($nivel, 'UTF-8'), 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY($X_FECHA, $Y_FECHA);
    $pdf->Cell($W_FECHA, 6, $fechaTexto, 0, 0, 'L');

    // Los 12 módulos del chunk se reparten en dos bloques de 6 (izquierda/derecha)
    $mitadIzquierda = array_slice($chunk, 0, 6);
    $mitadDerecha = array_slice($chunk, 6, 6);

    $bloques = [
        ['modulos' => $mitadIzquierda, 'xSigla' => $X_SIGLA_IZQ],
        ['modulos' => $mitadDerecha, 'xSigla' => $X_SIGLA_DER],
    ];

    foreach ($bloques as $bloque) {
        if (empty($bloque['modulos'])) {
            continue;
        }

        // Se dibuja como una sola tabla de 4 columnas (sin bordes: la cuadricula
        // ya esta impresa en el papel) para que las filas se alineen
        // automaticamente aunque el nombre del modulo ocupe varias lineas
        $anchoBloque = $W_SIGLA + $W_NOMBREMOD + $W_NUM + $W_FECHAMOD;
        $pctSigla = round($W_SIGLA / $anchoBloque * 100, 2);
        $pctNombre = round($W_NOMBREMOD / $anchoBloque * 100, 2);
        $pctNum = round($W_NUM / $anchoBloque * 100, 2);
        $pctFecha = round($W_FECHAMOD / $anchoBloque * 100, 2);

        $filasHtml = '';
        foreach ($bloque['modulos'] as $mod) {
            $nota = intval($mod['Nota']);
            $fechaAprob = !empty($mod['FechaRegistro']) ? date('d/m/Y', strtotime($mod['FechaRegistro'])) : '-';
            $filasHtml .= '<tr>
                <td width="' . $pctSigla . '%" style="font-weight:bold;">' . htmlspecialchars(siglaModuloHistorial($mod['codigomodulo'])) . '</td>
                <td width="' . $pctNombre . '%">' . htmlspecialchars(mb_strtoupper($mod['nombremodulo'], 'UTF-8')) . '</td>
                <td width="' . $pctNum . '%" style="font-weight:bold; text-align:center;">' . $nota . '</td>
                <td width="' . $pctFecha . '%" style="text-align:center;">' . $fechaAprob . '</td>
            </tr>';
        }

        $pdf->SetFont('helvetica', '', 9);
        $tablaHtml = '<table cellpadding="1.2" style="font-size:9pt;">' . $filasHtml . '</table>';
        $pdf->writeHTMLCell($anchoBloque, 0, $bloque['xSigla'], $Y_TABLA, $tablaHtml, 0, 0, false, true, 'L');
    }
}

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = 'Historial_Academico_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
