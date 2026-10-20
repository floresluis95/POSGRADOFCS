<?php
/**
 * Certificado de Calificaciones - PDF
 * Se imprime SOLO el contenido (sin membrete propio) porque el papel ya viene
 * pre-impreso desde Posgrado Central (Universidad Técnica de Oruro).
 * Las coordenadas están calibradas contra el formato físico pre-impreso;
 * si algo se desalinea al imprimir en el papel real, ajustar las constantes
 * del bloque "COORDENADAS" más abajo (en milímetros).
 *
 * Con completo=1 (demostración) dibuja también todo el formato (membrete, cuadrícula,
 * firmas, escudo de marca de agua...) en hoja en blanco y NO registra la emisión.
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
require_once __DIR__ . '/formatos-certificados.php';

date_default_timezone_set('America/La_Paz');

$estudianteID = intval($_POST['estudianteID'] ?? 0);
$programaID = intval($_POST['programaID'] ?? 0);
$nombre = $_POST['nombre'] ?? '';
$programa = $_POST['programa'] ?? '';
$nivel = CertificadoModelo::GradoLegible($_POST['nivel'] ?? ''); // MAESTRIA → MAESTRÍA
$anio = $_POST['anio'] ?? '';
$vaucher = CertificadoModelo::NormalizarVoucher($_POST['vaucher'] ?? '');
if ($vaucher === false) {
    die('Error: El N° de voucher solo puede tener letras y números (y un guion entre las dos partes)');
}
$fecha = $_POST['fecha'] ?? '';
$completo = !empty($_POST['completo']); // demostración con el formato completo

if (!$estudianteID || !$programaID || empty($nombre) || empty($fecha)) {
    die('Error: Faltan datos requeridos para generar el certificado');
}

// Los módulos y notas se vuelven a consultar desde la BD (no se confía en el POST)
$modulos = CertificadoModelo::ObtenerModulosCalificadosModelo($estudianteID, $programaID);

if (empty($modulos)) {
    die('Error: El estudiante no tiene calificaciones registradas en este programa');
}

// Se guardan los datos del formulario para poder reimprimirlo (y corregirlo) desde
// Registro de documentos; con idDocumento es una reimpresión de ese registro.
// En la vista previa (antes de generar) y en la demostración no se registra nada.
if (!CertificadoModelo::EsVistaPrevia() && !$completo) {
    CertificadoModelo::RegistrarEmisionModelo(
        'CALIFICACIONES', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null,
        $_POST, intval($_POST['idDocumento'] ?? 0)
    );
}

/**
 * Convierte un número entero (0-100) a su literal en español, en mayúsculas.
 */
function numeroALetras($numero)
{
    $entero = intval($numero);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

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
 * "MODULO VII" -> "MOD. VII" (así se abrevia en el formato pre-impreso)
 */
function siglaModulo($codigoModulo)
{
    return preg_replace('/^M[OÓ]DULO\s*/iu', 'MOD. ', trim($codigoModulo));
}

// Letra del formato: Cambria (negrita, cursiva y negrita cursiva, convertida desde las
// fuentes de Windows a tcpdf/fuentes/ para que también funcione en el servidor)
const FUENTE_CERT = 'cambria';
const LETRA_MINIMA = 6;

/**
 * Tamaño de letra (empezando en $tamano) con el que $texto entra en $ancho:
 * de una sola línea, o sin pasar de $alto si se le da alto (texto en varias líneas).
 */
function tamanoQueEntra($pdf, $texto, $estilo, $tamano, $ancho, $alto = null)
{
    for (; $tamano > LETRA_MINIMA; $tamano -= 0.5) {
        $pdf->SetFont(FUENTE_CERT, $estilo, $tamano);
        $entra = $alto === null
            ? $pdf->GetStringWidth($texto) <= $ancho
            : $pdf->getStringHeight($ancho, $texto) <= $alto;
        if ($entra) {
            break;
        }
    }
    $pdf->SetFont(FUENTE_CERT, $estilo, $tamano);
    return $tamano;
}

/**
 * Escribe un dato sobre la línea punteada del formato: $yLinea es el renglón donde
 * se apoya la letra; si no entra en $ancho se achica (nunca invade el campo vecino).
 */
function campoEnLinea($pdf, $x, $yLinea, $ancho, $texto, $estilo, $tamano, $alin = 'L')
{
    $tamano = tamanoQueEntra($pdf, $texto, $estilo, $tamano, $ancho);
    $alto = $tamano * 0.3528 * 1.3;
    $descenso = $pdf->getFontDescent(FUENTE_CERT, $estilo, $tamano);
    $pdf->SetXY($x, $yLinea + $descenso - $alto);
    $pdf->Cell($ancho, $alto, $texto, 0, 0, $alin, false, '', 0, false, 'T', 'B');
}

/**
 * Escribe una casilla de la tabla centrada en vertical dentro del renglón impreso.
 * $varias: el texto puede partirse en líneas (nombre del módulo); si no, va en una.
 */
function casillaTabla($pdf, $x, $y, $ancho, $alto, $texto, $estilo, $tamano, $alin, $varias = false)
{
    tamanoQueEntra($pdf, $texto, $estilo, $tamano, $ancho, $varias ? $alto : null);
    $pdf->MultiCell($ancho, $alto, $texto, 0, $alin, false, 0, $x, $y, true, 0, false, false, $alto, 'M');
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
// Y_ = renglón (línea punteada) donde se apoya el texto; X_/W_ = inicio y ancho
// del espacio libre entre la etiqueta impresa y la siguiente.
// ===================================
$X_NOMBRE = 59.5;    $Y_NOMBRE = 67.7;    $W_NOMBRE = 100;
$X_VAUCHER = 169.5;  $Y_VAUCHER = 67.7;   $W_VAUCHER = 36;
$X_PROGRAMA = 39.5;  $Y_PROGRAMA = 75.5;  $W_PROGRAMA = 166;
$X_NIVEL = 29.5;     $Y_NIVEL = 83.9;     $W_NIVEL = 130;
$X_ANIO = 172.5;     $Y_ANIO = 83.9;      $W_ANIO = 33;

// Cuerpo de la tabla: 6 renglones iguales; el primero 2,5 mm debajo del encabezado
// y el último termina en el borde inferior (180,9 mm)
$Y_TABLA = 108.3;    $ALTO_FILA = 12.1;   $MARGEN_CASILLA = 1.2;
$X_SIGLA = 10.9;     $W_SIGLA = 16.9;
$X_NOMBREMOD = 27.8; $W_NOMBREMOD = 92.5; $SANGRIA_NOMBREMOD = 5.8;
$X_NUM = 120.3;      $W_NUM = 19.8;
$X_LITERAL = 140.1;  $W_LITERAL = 65.8;   $SANGRIA_LITERAL = 11;

$X_DIA = 137.0;      $ANCHO_DIA = 10.6;
$X_MES = 152.8;      $ANCHO_MES = 35.5;
$X_ANIOFECHA = 193.9; $ANCHO_ANIOFECHA = 12.2;
$Y_FECHA = 197.8;

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
$pdf->setCellPaddings(0, 0, 0, 0);
$dirFuentes = __DIR__ . '/../fuentes/';
$pdf->AddFont(FUENTE_CERT, 'B', $dirFuentes . 'cambriab.php');
$pdf->AddFont(FUENTE_CERT, 'I', $dirFuentes . 'cambriai.php');
$pdf->AddFont(FUENTE_CERT, 'BI', $dirFuentes . 'cambriaz.php');

$modulosPorHoja = array_chunk($modulos, 6);
$coordenadas = compact('Y_NOMBRE', 'Y_VAUCHER', 'Y_PROGRAMA', 'Y_NIVEL', 'Y_ANIO', 'Y_TABLA', 'ALTO_FILA',
    'X_SIGLA', 'X_NOMBREMOD', 'X_NUM', 'X_LITERAL', 'W_LITERAL', 'X_DIA', 'ANCHO_DIA', 'X_MES', 'ANCHO_MES',
    'X_ANIOFECHA', 'ANCHO_ANIOFECHA', 'Y_FECHA');
$correlativo = $completo ? CertificadoModelo::ContarEmitidosModelo('CALIFICACIONES') + 1 : 0;

foreach ($modulosPorHoja as $chunk) {
    $pdf->AddPage();
    if ($completo) {
        dibujarFormatoCalificaciones($pdf, $coordenadas, $correlativo, ConfiguracionModelo::NotaAprobacion());
    }
    $pdf->SetTextColor(0, 0, 0);

    // Bloque de datos del encabezado (se repite en cada hoja)
    campoEnLinea($pdf, $X_NOMBRE, $Y_NOMBRE, $W_NOMBRE, mb_strtoupper($nombre, 'UTF-8'), 'I', 12);
    campoEnLinea($pdf, $X_VAUCHER, $Y_VAUCHER, $W_VAUCHER, mb_strtoupper($vaucher, 'UTF-8'), 'B', 12);
    campoEnLinea($pdf, $X_PROGRAMA, $Y_PROGRAMA, $W_PROGRAMA, mb_strtoupper($programa, 'UTF-8'), 'B', 13);
    campoEnLinea($pdf, $X_NIVEL, $Y_NIVEL, $W_NIVEL, mb_strtoupper($nivel, 'UTF-8'), 'B', 13);
    campoEnLinea($pdf, $X_ANIO, $Y_ANIO, $W_ANIO, mb_strtoupper($anio, 'UTF-8'), 'B', 13, 'C');

    // Tabla de módulos (sin bordes: la cuadrícula ya está impresa en el papel).
    // Cada módulo ocupa su renglón fijo; la letra se achica si no entra.
    $m = $MARGEN_CASILLA;
    foreach ($chunk as $i => $mod) {
        $nota = intval($mod['Nota']);
        $y = $Y_TABLA + $i * $ALTO_FILA;
        $altoUtil = $ALTO_FILA - 2 * $m;

        casillaTabla($pdf, $X_SIGLA + $m, $y + $m, $W_SIGLA - 2 * $m, $altoUtil,
            siglaModulo($mod['codigomodulo']), 'I', 10, 'C');
        casillaTabla($pdf, $X_NOMBREMOD + $SANGRIA_NOMBREMOD, $y + $m, $W_NOMBREMOD - $SANGRIA_NOMBREMOD - 2 * $m, $altoUtil,
            mb_strtoupper($mod['nombremodulo'], 'UTF-8'), 'I', 10, 'L', true);
        casillaTabla($pdf, $X_NUM + $m, $y + $m, $W_NUM - 2 * $m, $altoUtil,
            (string) $nota, 'B', 10, 'C');
        casillaTabla($pdf, $X_LITERAL + $SANGRIA_LITERAL, $y + $m, $W_LITERAL - $SANGRIA_LITERAL - $m, $altoUtil,
            numeroALetras($nota), 'BI', 10, 'L');
    }

    // Fecha (el "Oruro, ... de ... de ..." ya está impreso en el papel)
    campoEnLinea($pdf, $X_DIA, $Y_FECHA, $ANCHO_DIA, (string) $diaFecha, 'B', 11, 'C');
    campoEnLinea($pdf, $X_MES, $Y_FECHA, $ANCHO_MES, $mesFecha, 'B', 11, 'C');
    campoEnLinea($pdf, $X_ANIOFECHA, $Y_FECHA, $ANCHO_ANIOFECHA, $anioFecha, 'B', 11, 'C');
}

if (CertificadoModelo::EsVistaPrevia()) {
    CertificadoModelo::MarcarVistaPrevia($pdf, true);
}

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = ($completo ? 'Demostracion_' : '') . 'Certificado_Calificaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
