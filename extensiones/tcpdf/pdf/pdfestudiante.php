<?php
/**
 * Ficha de Inscripción de Estudiante - PDF
 * Datos del estudiante y del programa, estado de la matrícula (pagada o
 * pendiente) y requisitos de matriculación entregados, con espacio para la
 * foto 6x6, la firma del posgraduante y un QR de verificación.
 */

// El servidor tiene otra zona horaria configurada en php.ini; sin esto, las
// fechas impresas en el PDF no corresponden a la hora de Bolivia.
date_default_timezone_set('America/La_Paz');

// Configurar errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// La ficha tiene datos personales: solo el personal con sesión puede verla
// (antes bastaba con conocer o adivinar el idinscripcion de la URL).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['Validar']) || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
    http_response_code(403);
    die('Acceso denegado. Inicie sesión en el sistema.');
}

// Rutas base
$baseDir = realpath(__DIR__ . '/../../../');
require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/modelos/inscripcionmodulo.modelo.php';
require_once $baseDir . '/modelos/requisito.modelo.php';

// Validar ID de inscripción
if (!isset($_GET['idinscripcion']) || empty($_GET['idinscripcion'])) {
    die('Error: ID de inscripción no especificado');
}

$idInscripcion = intval($_GET['idinscripcion']);

// Obtener datos del estudiante por inscripción específica
$estudiante = InscripcionModuloModelos::ObtenerDatosCompletosEstudianteModelo($idInscripcion);

if (!$estudiante) {
    die('Error: No se encontró información del estudiante');
}

$requisitos = RequisitoModelo::ListarPorInscripcionModelo($idInscripcion);

// Crear PDF
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS');
$nombreCompleto = $estudiante['Nombre'] . ' ' . $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];
$pdf->SetTitle('Ficha de Inscripción - ' . $nombreCompleto);
$pdf->SetSubject('Ficha de Inscripción de Estudiante');

// Quitar header y footer automáticos
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar márgenes (sin salto automático: la ficha usa posiciones fijas en una hoja)
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(false);

// Agregar página
$pdf->AddPage();

// Logos
$pdf->Image(__DIR__ . '/../../imagenespdf/logouto.png', 15, 10, 25);
$pdf->Image(__DIR__ . '/../../imagenespdf/logofcs.png', 175, 10, 20);

// ========================================
// ENCABEZADO
// ========================================
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, 15);
$pdf->Cell(0, 10, 'UNIVERSIDAD TECNICA DE ORURO', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 12);
$pdf->SetXY(15, 22);
$pdf->Cell(0, 8, 'COORDINACION -POSGRADO ODONTOLOGIA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(15, 28);
$pdf->Cell(0, 5, 'Calle Presidente Montes entre Murguía y Aldana, Edificio UTO 2do Piso', 0, 1, 'C');
$pdf->SetXY(15, 33);
$pdf->Cell(0, 5, 'Telf: 5237317 - Fax: 5247110 - Oruro - Bolivia', 0, 1, 'C');

// Línea decorativa horizontal
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 41, 195, 41);

// ========================================
// TÍTULO DEL DOCUMENTO
// ========================================
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, 43);
$pdf->Cell(0, 8, 'FICHA DE INSCRIPCIÓN', 0, 1, 'C');

// ========================================
// AYUDANTES DE DIBUJO
// ========================================
$leftMargin = 15;

function tituloSeccion($pdf, $x, $y, $texto)
{
    $pdf->SetFont('helvetica', 'B', 10.5);
    $pdf->SetTextColor(30, 60, 114);
    $pdf->SetXY($x, $y);
    $pdf->Cell(0, 6, $texto, 0, 1, 'L');
    return $y + 7;
}

// Campo "ETIQUETA: valor" con línea debajo. Si el valor no entra en el ancho,
// se reduce la letra hasta que entre en una sola línea (sin salirse del margen).
// $mayusculas = false para valores que deben respetar su formato (p. ej. el correo).
function campo($pdf, $x, $y, $label, $value, $labelWidth, $dataWidth, $mayusculas = true)
{
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetXY($x, $y);
    $pdf->Cell($labelWidth, 6, $label, 0, 0, 'L');

    $valor = $mayusculas ? mb_strtoupper($value, 'UTF-8') : $value;
    $fontSize = 9;
    $pdf->SetFont('helvetica', '', $fontSize);
    while ($fontSize > 6 && $pdf->GetStringWidth($valor) > $dataWidth - 1) {
        $fontSize -= 0.5;
        $pdf->SetFont('helvetica', '', $fontSize);
    }

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetLineWidth(0.2);
    $pdf->Line($x + $labelWidth, $y + 5.5, $x + $labelWidth + $dataWidth, $y + 5.5);
    $pdf->SetXY($x + $labelWidth, $y);
    $pdf->Cell($dataWidth, 6, $valor, 0, 0, 'L');

    return $y + 7;
}

// ========================================
// DATOS PERSONALES (a la izquierda) + FOTO 6x6 (a la derecha)
// ========================================
$y = tituloSeccion($pdf, $leftMargin, 53, 'DATOS PERSONALES');

$fotoX = 135;
$fotoY = 54;
$fotoTamano = 60; // 6 cm = 60 mm
$pdf->SetDrawColor(30, 60, 114);
$pdf->SetLineWidth(0.3);
$pdf->Rect($fotoX, $fotoY, $fotoTamano, $fotoTamano);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY($fotoX, $fotoY + 25);
$pdf->Cell($fotoTamano, 6, 'FOTO', 0, 1, 'C');
$pdf->SetXY($fotoX, $fotoY + 31);
$pdf->Cell($fotoTamano, 6, '6 x 6 cm', 0, 1, 'C');

$lw = 42;   // ancho de etiqueta
$dw = 72;   // ancho del valor (termina antes de la foto)

$y = campo($pdf, $leftMargin, $y, 'NOMBRES:', $estudiante['Nombre'], $lw, $dw);
$y = campo($pdf, $leftMargin, $y, 'APELLIDOS:', $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'], $lw, $dw);

$ci = $estudiante['Ci'];
if (!empty($estudiante['Complemento'])) {
    $ci .= '-' . $estudiante['Complemento'];
}
$ci .= ' ' . $estudiante['Exp'];
$y = campo($pdf, $leftMargin, $y, 'CARNET DE IDENTIDAD:', $ci, $lw, $dw);

if (!empty($estudiante['FechaNacimiento']) && $estudiante['FechaNacimiento'] != '0000-00-00') {
    $fechaNac = date('d/m/Y', strtotime($estudiante['FechaNacimiento']));
    $y = campo($pdf, $leftMargin, $y, 'FECHA DE NACIMIENTO:', $fechaNac . '   EDAD: ' . $estudiante['Edad'] . ' AÑOS', $lw, $dw);
}

$profesion = !empty($estudiante['NombreProfesion']) ? $estudiante['NombreProfesion'] : 'NO ESPECIFICADO';
$y = campo($pdf, $leftMargin, $y, 'PROFESIÓN:', $profesion, $lw, $dw);

$trabajo = !empty($estudiante['Trabajo']) ? $estudiante['Trabajo'] : 'NO ESPECIFICADO';
$y = campo($pdf, $leftMargin, $y, 'LUGAR DE TRABAJO:', $trabajo, $lw, $dw);

$etiquetasTipoUniversidad = ['PUBLICA' => 'Pública', 'PRIVADA' => 'Privada', 'EXTRANJERA' => 'Extranjera', 'CONVENIO' => 'De Convenio'];
if (!empty($estudiante['NombreUniversidad'])) {
    $tipoUniv = $etiquetasTipoUniversidad[$estudiante['TipoUniversidad']] ?? $estudiante['TipoUniversidad'];
    $universidad = $estudiante['NombreUniversidad'] . ' (' . $tipoUniv . ')';
    if (!empty($estudiante['FechaEgreso']) && $estudiante['FechaEgreso'] != '0000-00-00') {
        $universidad .= ' - EGRESO: ' . date('d/m/Y', strtotime($estudiante['FechaEgreso']));
    }
} else {
    $universidad = 'NO ESPECIFICADO';
}
// Lo que sigue empieza debajo de la foto; la universidad va a todo el ancho
// porque su nombre suele ser largo y no entra al lado de la foto.
$y = max($y, $fotoY + $fotoTamano) + 2;
$y = campo($pdf, $leftMargin, $y, 'UNIVERSIDAD DE EGRESO:', $universidad, $lw, 180 - $lw) + 1;

// Dos columnas para los bloques siguientes
$colAncho = 88;
$col2X = $leftMargin + $colAncho + 4;
$lw2 = 30;
$dw2 = $colAncho - $lw2;

// ========================================
// DATOS DE CONTACTO
// ========================================
$y = tituloSeccion($pdf, $leftMargin, $y, 'DATOS DE CONTACTO');
$direccion = !empty($estudiante['Direccion']) ? $estudiante['Direccion'] : 'NO ESPECIFICADO';
$telefono = !empty($estudiante['Telefono']) ? $estudiante['Telefono'] : 'NO ESPECIFICADO';
$celular = !empty($estudiante['Celular']) ? $estudiante['Celular'] : 'NO ESPECIFICADO';
$correo = !empty($estudiante['Correo']) ? $estudiante['Correo'] : 'NO ESPECIFICADO';

campo($pdf, $leftMargin, $y, 'DIRECCIÓN:', $direccion, $lw2, $dw2);
$y = campo($pdf, $col2X, $y, 'TELÉFONO:', $telefono, $lw2, $dw2);
campo($pdf, $leftMargin, $y, 'CELULAR:', $celular, $lw2, $dw2);
$y = campo($pdf, $col2X, $y, 'CORREO:', $correo, $lw2, $dw2, false);

// ========================================
// PROGRAMA DE POSGRADO
// ========================================
$y = tituloSeccion($pdf, $leftMargin, $y + 1, 'PROGRAMA DE POSGRADO');
$y = campo($pdf, $leftMargin, $y, 'PROGRAMA INSCRITO:', $estudiante['NombrePrograma'], $lw, 180 - $lw);
campo($pdf, $leftMargin, $y, 'GRADO ACADÉMICO:', $estudiante['GradoAcademico'], $lw2 + 8, $dw2 - 8);
$y = campo($pdf, $col2X, $y, 'CÓDIGO:', $estudiante['CodigoPrograma'], $lw2, $dw2);

// ========================================
// MATRÍCULA: pagada (con voucher) o pendiente (inscrito sin pago de matrícula)
// ========================================
$fechaInscripcion = date('d/m/Y', strtotime($estudiante['FechaInscripcion']));
$montoPagado = (float)($estudiante['montoPagado'] ?? 0);
$voucher = trim($estudiante['nvauchermatricula'] ?? '');
$matriculaPagada = $montoPagado > 0 && $voucher !== '' && mb_strtoupper($voucher, 'UTF-8') !== 'SIN MATRÍCULA';

$y = tituloSeccion($pdf, $leftMargin, $y + 1, 'MATRÍCULA');

// Recuadro de estado: verde si está pagada, ámbar si está pendiente
$estadoTexto = $matriculaPagada ? 'PAGADA' : 'PENDIENTE';
$pdf->SetLineWidth(0.3);
if ($matriculaPagada) {
    $pdf->SetFillColor(231, 246, 238);
    $pdf->SetDrawColor(47, 133, 90);
    $pdf->SetTextColor(47, 133, 90);
} else {
    $pdf->SetFillColor(255, 246, 224);
    $pdf->SetDrawColor(183, 121, 31);
    $pdf->SetTextColor(183, 121, 31);
}
$pdf->SetFont('helvetica', 'B', 9.5);
$pdf->SetXY($leftMargin, $y);
$pdf->Cell(32, 7, $estadoTexto, 1, 0, 'C', true);

if ($matriculaPagada) {
    $detalleMatricula = 'Bs. ' . number_format($montoPagado, 2) . '   ·   VOUCHER N° ' . $voucher . '   ·   FECHA: ' . $fechaInscripcion;
} else {
    $detalleMatricula = 'Inscrito sin pago de matrícula: el programa completo se cancela por cuotas.';
}
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($leftMargin + 35, $y);
$pdf->Cell(145, 7, $detalleMatricula, 0, 1, 'L');
$y += 10;

// ========================================
// REQUISITOS DE MATRICULACIÓN (☑ entregado / ☐ pendiente)
// ========================================
$obligatorios = array_filter($requisitos, function ($r) { return (int)$r['Obligatorio'] === 1; });
$entregados = count(array_filter($obligatorios, function ($r) { return (int)$r['Entregado'] === 1; }));
$y = tituloSeccion($pdf, $leftMargin, $y, 'REQUISITOS (' . $entregados . ' DE ' . count($obligatorios) . ' ENTREGADOS)');

$filaAlto = 6.2;
$cajaTam = 3.6;
foreach (array_values($requisitos) as $i => $req) {
    $colX = ($i % 2 === 0) ? $leftMargin : $col2X;
    $filaY = $y + floor($i / 2) * $filaAlto;
    $entregado = (int)$req['Entregado'] === 1;

    // Casilla
    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(60, 60, 60);
    $pdf->Rect($colX, $filaY + 1.2, $cajaTam, $cajaTam);
    if ($entregado) {
        // Visto dibujado (no depende de que la fuente tenga el carácter ✓)
        $pdf->SetDrawColor(47, 133, 90);
        $pdf->SetLineWidth(0.6);
        $pdf->Line($colX + 0.7, $filaY + 3.1, $colX + 1.5, $filaY + 4.1);
        $pdf->Line($colX + 1.5, $filaY + 4.1, $colX + 3.0, $filaY + 1.8);
    }

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY($colX + $cajaTam + 2, $filaY);
    $pdf->Cell(58, $filaAlto, $req['Nombre'], 0, 0, 'L');

    $pdf->SetFont('helvetica', '', 7.5);
    $pdf->SetTextColor(110, 110, 110);
    $pdf->SetXY($colX + $cajaTam + 60, $filaY);
    $pdf->Cell(24, $filaAlto, $entregado && !empty($req['FechaEntrega']) ? date('d/m/Y', strtotime($req['FechaEntrega'])) : 'Pendiente', 0, 0, 'R');
}
$y += ceil(count($requisitos) / 2) * $filaAlto + 3;

// ========================================
// OBSERVACIONES
// ========================================
$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY($leftMargin, $y);
$pdf->Cell(32, 6, 'OBSERVACIONES:', 0, 0, 'L');
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.2);
$pdf->Line($leftMargin + 32, $y + 5.5, 145, $y + 5.5);

// ========================================
// FIRMA (izquierda) y CÓDIGO QR (derecha), al pie de la hoja
// ========================================
$qrSize = 26;
$qrX = 169;
$qrY = 228;

$firmaX = 55;
$firmaY = 245;
$pdf->SetDrawColor(100, 100, 100);
$pdf->SetLineWidth(0.3);
$pdf->Line($firmaX, $firmaY, $firmaX + 70, $firmaY);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY($firmaX, $firmaY + 1.5);
$pdf->Cell(70, 5, 'FIRMA DEL POSGRADUANTE', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($firmaX, $firmaY + 7);
$pdf->Cell(70, 5, 'FECHA DE INSCRIPCIÓN: ' . $fechaInscripcion, 0, 1, 'C');

// Datos del QR como texto con etiquetas (una línea por dato): al escanearlo con
// el celular se lee directamente, en vez de un JSON difícil de entender.
$programaQR = $estudiante['GradoAcademico'] . ' EN ' . $estudiante['NombrePrograma'];
if (!empty($estudiante['Version'])) {
    $programaQR .= ' (' . $estudiante['Version'] . ')';
}

$lineasQR = [
    'FICHA DE INSCRIPCIÓN - POSGRADO FCS UTO',
    'N° INSCRIPCIÓN: ' . $estudiante['idInscripcion'],
    'ESTUDIANTE: ' . $nombreCompleto,
    'CI: ' . $ci,
    'PROGRAMA: ' . $programaQR,
    'CÓDIGO: ' . $estudiante['CodigoPrograma'],
    'FECHA DE INSCRIPCIÓN: ' . $fechaInscripcion,
    'MATRÍCULA: ' . $estadoTexto,
    'REQUISITOS: ' . $entregados . ' DE ' . count($obligatorios),
];
$qrContent = implode("\n", $lineasQR);

$style = [
    'border' => 2,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => [0, 0, 0],
    'bgcolor' => [255, 255, 255],
    'module_width' => 1,
    'module_height' => 1
];

// Corrección 'M' (no 'H'): con más texto, 'H' genera un QR muy denso que cuesta
// leer impreso en 2-3 cm.
$pdf->write2DBarcode($qrContent, 'QRCODE,M', $qrX, $qrY, $qrSize, $qrSize, $style, 'N');

$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY($qrX, $qrY + $qrSize + 0.5);
$pdf->Cell($qrSize, 3, 'Escanear para verificar', 0, 1, 'C');

// ========================================
// PIE DE PÁGINA
// ========================================
$pdf->SetXY(15, 263);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(180, 4, 'Fecha de emisión: ' . date('d/m/Y H:i'), 0, 1, 'C');
$pdf->SetX(15);
$pdf->Cell(180, 4, 'Este documento es un registro oficial del Sistema de Gestión de Posgrado FCS', 0, 1, 'C');

// Generar PDF
$pdf->Output('Ficha_Inscripcion_' . $idInscripcion . '_' . date('Ymd') . '.pdf', 'I');
