<?php
/**
 * Historial Académico - PDF
 * Se imprime SOLO el contenido (sin membrete propio) porque el papel ya viene
 * pre-impreso desde Posgrado Central (Universidad Técnica de Oruro), en hoja
 * oficio boliviano horizontal (13 x 8.5 in = 330.2 x 215.9 mm).
 * Las coordenadas se midieron sobre el formato escaneado; si la impresora
 * corre el papel unos milímetros, se ajustan en el bloque "COORDENADAS".
 *
 * Con completo=1 (demostración) dibuja también todo el formato en la misma hoja oficio
 * y NO registra la emisión.
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
$vaucher = CertificadoModelo::NormalizarVoucher($_POST['vaucher'] ?? '');
if ($vaucher === false) {
    die('Error: El N° de voucher solo puede tener letras y números (y un guion entre las dos partes)');
}
$fecha = $_POST['fecha'] ?? '';
$completo = !empty($_POST['completo']); // demostración con el formato completo

if (!$estudianteID || !$programaID || empty($nombre) || empty($fecha)) {
    die('Error: Faltan datos requeridos para generar el historial académico');
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
        'HISTORIAL_ACADEMICO', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null,
        $_POST, intval($_POST['idDocumento'] ?? 0)
    );
}

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
// COORDENADAS (mm) - Hoja OFICIO boliviano horizontal (330.2 x 215.9 mm)
// Medidas tomadas del formato escaneado (img20261002_11102292.pdf, 2026-10-02).
// Todo se escribe DENTRO de las celdas/líneas del papel, con margen interno,
// y la letra se achica sola si el texto no entra.
// ===================================
const HIST_ANCHO_HOJA = 330.2;
const HIST_ALTO_HOJA = 215.9;
const HIST_PADDING = 1.5;          // separación del texto respecto a las líneas impresas
// Letra Cambria (negrita, cursiva y negrita cursiva, en tcpdf/fuentes/), como el
// certificado de calificaciones
const HIST_FUENTE = 'cambria';

// Campos del encabezado: [x inicial, x final, línea base]
$CAMPOS = [
    'nombre'   => [60.0, 212.0, 46.0],
    'vaucher'  => [223.0, 314.0, 46.0],
    'programa' => [39.0, 314.0, 53.0],
    'nivel'    => [27.0, 189.0, 59.5],
    'fecha'    => [206.0, 314.0, 59.5],
];

// Cuerpo de la tabla (debajo de la cabecera "Módulo / Asignatura / Calif. / Fecha")
$Y_CUERPO_INI = 76.5;
$Y_CUERPO_FIN = 176.0;

// Columnas de cada bloque: [x inicial, x final]
$BLOQUES = [
    // Bloque izquierdo: MOD.I - MOD.VI
    ['sigla' => [14.0, 40.0], 'nombre' => [40.0, 122.5], 'nota' => [122.5, 139.0], 'fecha' => [139.0, 164.5]],
    // Bloque derecho: MOD.VII - MOD.XII
    ['sigla' => [164.5, 190.5], 'nombre' => [190.5, 273.0], 'nota' => [273.0, 289.5], 'fecha' => [289.5, 315.5]],
];
$MODULOS_POR_BLOQUE = 6;

/**
 * Escribe un texto en una línea del encabezado, sin pasarse del final de la
 * línea impresa: si no entra, reduce la letra (hasta 6 pt).
 */
function campoHistorial($pdf, array $campo, $texto, $estilo, $tamano)
{
    [$xIni, $xFin, $base] = $campo;
    $ancho = $xFin - $xIni - HIST_PADDING;
    $pdf->SetFont(HIST_FUENTE, $estilo, $tamano);
    while ($tamano > 6 && $pdf->GetStringWidth($texto) > $ancho) {
        $tamano -= 0.5;
        $pdf->SetFont(HIST_FUENTE, $estilo, $tamano);
    }
    $alto = $tamano * 0.3528 * 1.25;            // alto de la línea según la letra
    $pdf->SetXY($xIni + HIST_PADDING / 2, $base - $alto + 0.6);
    $pdf->Cell($ancho, $alto, $texto, 0, 0, 'L', false, '', 1); // 1 = comprime si aún no entra
}

/**
 * Alto que necesita cada fila (módulo) de un bloque con la letra dada.
 */
function altosFilasHistorial($pdf, array $filas, array $bloque, $tamano)
{
    $pdf->SetFont(HIST_FUENTE, 'I', $tamano);
    $anchoNombre = $bloque['nombre'][1] - $bloque['nombre'][0] - 2 * HIST_PADDING;
    $altoLinea = $tamano * 0.3528 * 1.2;
    $altos = [];
    foreach ($filas as $fila) {
        $lineas = max(1, $pdf->getNumLines($fila['nombre'], $anchoNombre, false, true, 0, 0));
        $altos[] = $lineas * $altoLinea + 2 * HIST_PADDING;
    }
    return $altos;
}

// ===================================
// GENERACIÓN DEL PDF
// ===================================
$pdf = new TCPDF('L', 'mm', array(HIST_ALTO_HOJA, HIST_ANCHO_HOJA), true, 'UTF-8', false);
$correlativo = $completo ? CertificadoModelo::ContarEmitidosModelo('HISTORIAL_ACADEMICO') + 1 : 0;
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS - Universidad Técnica de Oruro');
$pdf->SetTitle('Historial Académico - ' . $nombre);
$pdf->SetSubject('Historial Académico');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->setCellPaddings(0, 0, 0, 0);
$dirFuentes = __DIR__ . '/../fuentes/';
$pdf->AddFont(HIST_FUENTE, 'B', $dirFuentes . 'cambriab.php');
$pdf->AddFont(HIST_FUENTE, 'I', $dirFuentes . 'cambriai.php');
$pdf->AddFont(HIST_FUENTE, 'BI', $dirFuentes . 'cambriaz.php');

$modulosPorHoja = array_chunk($modulos, $MODULOS_POR_BLOQUE * count($BLOQUES));

foreach ($modulosPorHoja as $chunk) {
    $pdf->AddPage();
    if ($completo) {
        dibujarFormatoHistorial($pdf, $CAMPOS, $BLOQUES, $Y_CUERPO_INI, $Y_CUERPO_FIN, $correlativo);
    }
    $pdf->SetTextColor(0, 0, 0);

    // Encabezado (se repite en cada hoja)
    // Nombre en cursiva; N°, programa, nivel y fecha en negrita
    campoHistorial($pdf, $CAMPOS['nombre'], mb_strtoupper($nombre, 'UTF-8'), 'I', 12);
    campoHistorial($pdf, $CAMPOS['vaucher'], mb_strtoupper($vaucher, 'UTF-8'), 'B', 12);
    campoHistorial($pdf, $CAMPOS['programa'], mb_strtoupper($programa, 'UTF-8'), 'B', 12);
    campoHistorial($pdf, $CAMPOS['nivel'], mb_strtoupper($nivel, 'UTF-8'), 'B', 12);
    campoHistorial($pdf, $CAMPOS['fecha'], $fechaTexto, 'B', 11);

    // Filas de cada bloque (6 módulos a la izquierda, 6 a la derecha)
    $altoDisponible = $Y_CUERPO_FIN - $Y_CUERPO_INI - 2 * HIST_PADDING;

    foreach ($BLOQUES as $i => $bloque) {
        $filas = [];
        foreach (array_slice($chunk, $i * $MODULOS_POR_BLOQUE, $MODULOS_POR_BLOQUE) as $mod) {
            $filas[] = [
                'sigla' => siglaModuloHistorial($mod['codigomodulo']),
                'nombre' => mb_strtoupper(trim($mod['nombremodulo']), 'UTF-8'),
                'nota' => (string)intval($mod['Nota']),
                'fecha' => !empty($mod['FechaRegistro']) ? date('d/m/Y', strtotime($mod['FechaRegistro'])) : '-',
            ];
        }
        if (!$filas) {
            continue;
        }

        // Letra más grande con la que las filas entran en el cuerpo de la tabla
        $tamano = 10;
        $altos = altosFilasHistorial($pdf, $filas, $bloque, $tamano);
        while ($tamano > 6 && array_sum($altos) > $altoDisponible) {
            $tamano -= 0.5;
            $altos = altosFilasHistorial($pdf, $filas, $bloque, $tamano);
        }

        // Si sobra espacio, las filas se reparten parejas (como renglones);
        // si no, cada una ocupa lo que necesita.
        $altoParejo = $altoDisponible / $MODULOS_POR_BLOQUE;
        if (max($altos) <= $altoParejo) {
            $altos = array_fill(0, count($filas), $altoParejo);
        }

        $altoLinea = $tamano * 0.3528 * 1.2;
        $y = $Y_CUERPO_INI + HIST_PADDING;
        foreach ($filas as $k => $fila) {
            $yTexto = $y + HIST_PADDING;

            // Sigla y nombre del módulo en cursiva; calificación y fecha en negrita
            $pdf->SetFont(HIST_FUENTE, 'I', $tamano);
            [$x0, $x1] = $bloque['sigla'];
            $pdf->SetXY($x0 + HIST_PADDING, $yTexto);
            $pdf->Cell($x1 - $x0 - 2 * HIST_PADDING, $altoLinea, $fila['sigla'], 0, 0, 'L', false, '', 1);

            $pdf->SetFont(HIST_FUENTE, 'I', $tamano);
            [$x0, $x1] = $bloque['nombre'];
            $pdf->MultiCell($x1 - $x0 - 2 * HIST_PADDING, $altoLinea, $fila['nombre'], 0, 'L', false, 0, $x0 + HIST_PADDING, $yTexto);

            $pdf->SetFont(HIST_FUENTE, 'B', $tamano);
            [$x0, $x1] = $bloque['nota'];
            $pdf->SetXY($x0 + HIST_PADDING, $yTexto);
            $pdf->Cell($x1 - $x0 - 2 * HIST_PADDING, $altoLinea, $fila['nota'], 0, 0, 'C', false, '', 1);

            $pdf->SetFont(HIST_FUENTE, 'B', $tamano);
            [$x0, $x1] = $bloque['fecha'];
            $pdf->SetXY($x0 + HIST_PADDING, $yTexto);
            $pdf->Cell($x1 - $x0 - 2 * HIST_PADDING, $altoLinea, $fila['fecha'], 0, 0, 'C', false, '', 1);

            $y += $altos[$k];
        }
    }
}

if (CertificadoModelo::EsVistaPrevia()) {
    CertificadoModelo::MarcarVistaPrevia($pdf, true);
}

if (ob_get_contents()) {
    ob_end_clean();
}

$nombreArchivo = ($completo ? 'Demostracion_' : '') . 'Historial_Academico_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
