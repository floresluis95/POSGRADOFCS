<?php
/**
 * Certificado de Aprobación de Trabajo Monográfico - PDF
 * Se imprime SOLO el contenido (sin membrete propio) porque el papel ya viene
 * pre-impreso desde Posgrado Central (Universidad Técnica de Oruro), en hoja
 * oficio vertical (8.5 x 14 in).
 * Las coordenadas se midieron sobre el formato en blanco (CERT_DE_APROBACION.tif);
 * es un primer ajuste y es normal que necesite un retoque de milímetros luego
 * de una impresión de prueba sobre el papel real (ver bloque "COORDENADAS").
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
$curso = $_POST['curso'] ?? '';
$vaucher = $_POST['vaucher'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$tribunalRaw = $_POST['tribunal'] ?? '';
$entrega = $_POST['entrega'] ?? '';
$calificacion = $_POST['calificacion'] ?? '';

if (empty($nombre) || empty($titulo) || empty($vaucher) || $calificacion === '') {
    die('Error: Faltan datos requeridos para generar el certificado');
}

$miembrosTribunal = array_filter(array_map('trim', explode('|', $tribunalRaw)));

// Se guarda (o actualiza) el trabajo de grado para reutilizar el título
// automáticamente en el certificado de Medio Magnético
if ($estudianteID && $programaID) {
    CertificadoModelo::GuardarTrabajoGradoModelo([
        'estudianteID' => $estudianteID,
        'programaID' => $programaID,
        'titulo' => $titulo,
        'tipoTrabajo' => 'MEDIO FISICO Y DIGITAL DEL TRABAJO DE TESIS',
        'tribunal' => $tribunalRaw,
        'calificacion' => $calificacion,
        'fechaEntrega' => $entrega,
        'fechaAprobacion' => date('Y-m-d'),
        'usuarioID' => $_SESSION['IdUsuario'] ?? null,
    ]);
}

CertificadoModelo::RegistrarDocumentoEmitidoModelo(
    'APROBACION_MONOGRAFIA', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null
);

/**
 * Convierte un número entero (0-100) a su literal en español, en mayúsculas.
 */
function numeroALetrasMonografia($numero)
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

$calificacionLiteral = numeroALetrasMonografia($calificacion);
$fechaEntregaTexto = !empty($entrega) ? date('d/m/Y', strtotime($entrega)) : '';

// ===================================
// COORDENADAS (mm) - Hoja Oficio vertical (215.9 x 355.6 mm)
// ===================================
$X_NOMBRE_CENTRO = 108;  $Y_NOMBRE = 60;
$X_PROGRAMA = 42;        $Y_PROGRAMA = 70.5;   $W_PROGRAMA = 165;
$X_CURSO = 34;           $Y_CURSO = 80.6;      $W_CURSO = 165;

$X_TITULO = 15;          $Y_TITULO = 102;      $W_TITULO = 185;
$X_TRIBUNAL = 15;        $Y_TRIBUNAL = 133;    $W_TRIBUNAL = 185;

$X_NUMERAL = 146;        $Y_NUMERAL = 160.4;   $W_NUMERAL = 40;
$X_ENTREGA = 79;         $Y_ENTREGA = 169;     $W_ENTREGA = 60;
$X_LITERAL = 143;        $Y_LITERAL = 177.6;   $W_LITERAL = 55;

$X_RECIBO = 52;          $Y_RECIBO = 303.6;    $W_RECIBO = 55;

// ===================================
// GENERACIÓN DEL PDF
// ===================================
$pdf = new TCPDF('P', 'mm', array(215.9, 355.6), true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Certificado de Aprobación de Monografía - ' . $nombre);
$pdf->SetSubject('Certificado de Aprobación de Trabajo Monográfico');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->SetTextColor(0, 0, 0);

// Nombre del estudiante (centrado, APELLIDOS NOMBRE)
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetXY(0, $Y_NOMBRE);
$pdf->Cell(215.9, 7, mb_strtoupper($nombre, 'UTF-8'), 0, 0, 'C');

// Programa / Curso
$pdf->SetFont('helvetica', '', 11);
$pdf->SetXY($X_PROGRAMA, $Y_PROGRAMA);
$pdf->Cell($W_PROGRAMA, 6, mb_strtoupper($programa, 'UTF-8'), 0, 0, 'L');

$pdf->SetXY($X_CURSO, $Y_CURSO);
$pdf->Cell($W_CURSO, 6, mb_strtoupper($curso, 'UTF-8'), 0, 0, 'L');

// Título del trabajo monográfico
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell($W_TITULO, 6, mb_strtoupper($titulo, 'UTF-8'), 0, 'L', false, 1, $X_TITULO, $Y_TITULO);

// Miembros del tribunal
$textoTribunal = mb_strtoupper(implode("\n", $miembrosTribunal), 'UTF-8');
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell($W_TRIBUNAL, 5, $textoTribunal, 0, 'L', false, 1, $X_TRIBUNAL, $Y_TRIBUNAL);

// Entrega informe final
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY($X_ENTREGA, $Y_ENTREGA);
$pdf->Cell($W_ENTREGA, 6, $fechaEntregaTexto, 0, 0, 'L');

// Calificación numeral y literal
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetXY($X_NUMERAL, $Y_NUMERAL);
$pdf->Cell($W_NUMERAL, 6, intval($calificacion), 0, 0, 'L');

$pdf->SetXY($X_LITERAL, $Y_LITERAL);
$pdf->Cell($W_LITERAL, 6, $calificacionLiteral, 0, 0, 'L');

// N° de recibo de caja (vaucher)
$pdf->SetFont('helvetica', '', 10);
$pdf->SetXY($X_RECIBO, $Y_RECIBO);
$pdf->Cell($W_RECIBO, 6, mb_strtoupper($vaucher, 'UTF-8'), 0, 0, 'L');

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = 'Certificado_Aprobacion_Monografia_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
