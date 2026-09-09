<?php
/**
 * Certificado de Calificaciones - PDF
 * Se imprime SOLO el contenido (sin membrete propio) porque el papel ya viene
 * pre-impreso desde Posgrado Central (Universidad Técnica de Oruro).
 * Las coordenadas están calibradas contra el formato físico pre-impreso;
 * si algo se desalinea al imprimir en el papel real, ajustar las constantes
 * del bloque "COORDENADAS" más abajo (en milímetros).
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
$anio = $_POST['anio'] ?? '';
$vaucher = $_POST['vaucher'] ?? '';
$fecha = $_POST['fecha'] ?? '';

if (!$estudianteID || !$programaID || empty($nombre) || empty($fecha)) {
    die('Error: Faltan datos requeridos para generar el certificado');
}

// Los módulos y notas se vuelven a consultar desde la BD (no se confía en el POST)
$modulos = CertificadoModelo::ObtenerModulosCalificadosModelo($estudianteID, $programaID);

if (empty($modulos)) {
    die('Error: El estudiante no tiene calificaciones registradas en este programa');
}

/**
 * Convierte un número entero (0-100) a su literal en español, en mayúsculas.
 */
function numeroALetras($numero)
{
    $entero = intval($numero);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

    if ($entero == 0) {
        $literal = 'CERO';
    } elseif ($entero == 100) {
        $literal = 'CIEN';
    } elseif ($entero > 100) {
        $literal = 'CIENTO ';
        $resto = $entero - 100;

        if ($resto >= 10 && $resto < 20) {
            $literal .= $especiales[$resto - 10];
        } elseif ($resto >= 20 && $resto < 30) {
            $literal .= $veintenas[$resto - 20];
        } elseif ($resto >= 30) {
            $dec = floor($resto / 10);
            $uni = $resto % 10;
            $literal .= $decenas[$dec];
            if ($uni > 0) {
                $literal .= ' Y ' . $unidades[$uni];
            }
        } else {
            $literal .= $unidades[$resto];
        }
    } elseif ($entero >= 10 && $entero < 20) {
        $literal = $especiales[$entero - 10];
    } elseif ($entero >= 20 && $entero < 30) {
        $literal = $veintenas[$entero - 20];
    } else {
        $dec = floor($entero / 10);
        $uni = $entero % 10;
        $literal = $decenas[$dec];
        if ($uni > 0) {
            if ($dec > 0) {
                $literal .= ' Y ';
            }
            $literal .= $unidades[$uni];
        }
    }

    return trim($literal);
}

/**
 * "MODULO I" -> "MOD.I" (así se abrevia en el formato pre-impreso)
 */
function siglaModulo($codigoModulo)
{
    return str_ireplace('MODULO ', 'MOD.', trim($codigoModulo));
}

$mesesEspanol = [
    1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
    5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
    9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
];

$timestampFecha = strtotime($fecha);
$diaFecha = (int) date('j', $timestampFecha);
$mesFecha = $mesesEspanol[(int) date('n', $timestampFecha)];
$anioFecha = date('Y', $timestampFecha);

// ===================================
// COORDENADAS (mm) calibradas contra el formato pre-impreso (Carta / Letter)
// ===================================
$X_NOMBRE = 60;      $Y_NOMBRE = 62;      $W_NOMBRE = 108;
$X_VAUCHER = 171;    $Y_VAUCHER = 62;     $W_VAUCHER = 36;
$X_PROGRAMA = 33;    $Y_PROGRAMA = 71;    $W_PROGRAMA = 174;
$X_NIVEL = 23;       $Y_NIVEL = 80;       $W_NIVEL = 138;
$X_ANIO = 171;       $Y_ANIO = 80;        $W_ANIO = 36;

$X_TABLA = 9.65;     $Y_TABLA = 106;      $W_TABLA = 197.85;
$W_SIGLA_PCT = 9.22; $W_NOMBREMOD_PCT = 46.75; $W_NUM_PCT = 10.36; $W_LITERAL_PCT = 33.67;

$X_DIA = 123.1;      $ANCHO_DIA = 9.2;
$X_MES = 136.6;      $ANCHO_MES = 41.9;
$X_ANIOFECHA = 182.8; $ANCHO_ANIOFECHA = 24.7;
$Y_FECHA = 200.3;

// ===================================
// GENERACIÓN DEL PDF
// ===================================
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Certificado de Calificaciones - ' . $nombre);
$pdf->SetSubject('Certificado de Calificaciones');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);

$modulosPorHoja = array_chunk($modulos, 6);

foreach ($modulosPorHoja as $chunk) {
    $pdf->AddPage();
    $pdf->SetTextColor(0, 0, 0);

    // Bloque de datos del encabezado (se repite en cada hoja)
    $pdf->SetFont('helvetica', 'B', 11);
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

    $pdf->SetXY($X_ANIO, $Y_ANIO);
    $pdf->Cell($W_ANIO, 6, mb_strtoupper($anio, 'UTF-8'), 0, 0, 'L');

    // Tabla de módulos (sin bordes: la cuadrícula ya está impresa en el papel)
    $filasHtml = '';
    foreach ($chunk as $mod) {
        $nota = intval($mod['Nota']);
        $filasHtml .= '<tr>
            <td width="' . $W_SIGLA_PCT . '%" style="font-weight:bold;">' . htmlspecialchars(siglaModulo($mod['codigomodulo'])) . '</td>
            <td width="' . $W_NOMBREMOD_PCT . '%">' . htmlspecialchars(mb_strtoupper($mod['nombremodulo'], 'UTF-8')) . '</td>
            <td width="' . $W_NUM_PCT . '%" style="font-weight:bold; text-align:center;">' . $nota . '</td>
            <td width="' . $W_LITERAL_PCT . '%">' . htmlspecialchars(numeroALetras($nota)) . '</td>
        </tr>';
    }

    $pdf->SetFont('helvetica', '', 10);
    $tablaHtml = '<table cellpadding="1.5" style="font-size:10pt;">' . $filasHtml . '</table>';
    $pdf->writeHTMLCell($W_TABLA, 0, $X_TABLA, $Y_TABLA, $tablaHtml, 0, 0, false, true, 'L');

    // Fecha (el "de ... de ..." ya está impreso en el papel)
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY($X_DIA, $Y_FECHA);
    $pdf->Cell($ANCHO_DIA, 6, $diaFecha, 0, 0, 'C');
    $pdf->SetXY($X_MES, $Y_FECHA);
    $pdf->Cell($ANCHO_MES, 6, $mesFecha, 0, 0, 'C');
    $pdf->SetXY($X_ANIOFECHA, $Y_FECHA);
    $pdf->Cell($ANCHO_ANIOFECHA, 6, $anioFecha, 0, 0, 'C');
}

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = 'Certificado_Calificaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
