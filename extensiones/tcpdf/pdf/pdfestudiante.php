<?php
/**
 * Ficha de Inscripción de Estudiante - PDF
 * Genera una ficha de inscripción con foto y datos del estudiante
 */

// Configurar errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Rutas base
$baseDir = realpath(__DIR__ . '/../../../');
require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/modelos/inscripcionmodulo.modelo.php';

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

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

// Agregar página
$pdf->AddPage();

// Imagen izquierda
$pdf->Image('../../imagenespdf/logouto.png', 15, 10, 25);  // (x, y, ancho)

// Imagen derecha
$pdf->Image('../../imagenespdf/logofcs.png', 175, 10, 20);// ========================================
// ENCABEZADO
// ========================================
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, 15);
$pdf->Cell(0, 10, 'UNIVERSIDAD TECNICA DE ORURO', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 12);
$pdf->SetXY(15, 22);
$pdf->Cell(0, 8, 'COORDINACION -POSGRADO ODONTOLOGIA', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(15, 28);
$pdf->Cell(0, 5, 'Av. del Minero Barrio San jose "Excomibol"', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(15, 33);
$pdf->Cell(0, 5, 'Tel.(+591) 5237317 - Fax 5247110"', 0, 1, 'C');

// Línea decorativa horizontal
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 42, 195, 42);

// ========================================
// TÍTULO DEL DOCUMENTO
// ========================================
$pdf->SetFont('helvetica', 'B', 23);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY(15, 42);
$pdf->Cell(0, 8, 'FICHA DE INSCRIPCIÓN', 0, 1, 'C');

// ========================================
// DATOS PERSONALES
// ========================================
$y = 54;
$leftMargin = 15;
$labelWidth = 55;
$dataWidth = 80;

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY($leftMargin, $y);
$pdf->Cell(0, 6, 'DATOS PERSONALES', 0, 1, 'L');
$y += 8;

// Función helper para agregar campos
function addField($pdf, $x, $y, $label, $value, $labelWidth, $dataWidth) {
    // Etiqueta
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetXY($x, $y);
    $pdf->Cell($labelWidth, 6, $label, 0, 0, 'L');

    // Valor
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);

    // Línea debajo del valor
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line($x + $labelWidth, $y + 5.5, $x + $labelWidth + $dataWidth, $y + 5.5);

    $pdf->SetXY($x + $labelWidth, $y);
    $pdf->Cell($dataWidth, 6, strtoupper($value), 0, 1, 'L');

    return $y + 7;
}

// Nombres
$y = addField($pdf, $leftMargin, $y, 'NOMBRES:', $estudiante['Nombre'], $labelWidth, $dataWidth);

// Apellidos
$apellidosCompletos = $estudiante['Apaterno'] . ' ' . $estudiante['Amaterno'];

$y = addField($pdf, $leftMargin, $y, 'APELLIDOS:', $apellidosCompletos, $labelWidth, $dataWidth);
// Carnet de Identidad
$ci = $estudiante['Ci'];
if (!empty($estudiante['Complemento'])) {
    $ci .= '-' . $estudiante['Complemento'];
}
$ci .= ' ' . $estudiante['Exp'];
$y = addField($pdf, $leftMargin, $y, 'CARNET DE IDENTIDAD:', $ci, $labelWidth, $dataWidth);

// Fecha de Nacimiento
if (!empty($estudiante['FechaNacimiento']) && $estudiante['FechaNacimiento'] != '0000-00-00') {
    $fechaNac = date('d/m/Y', strtotime($estudiante['FechaNacimiento']));
    $edad = $estudiante['Edad'];
    $y = addField($pdf, $leftMargin, $y, 'FECHA DE NACIMIENTO:', $fechaNac . '  EDAD:' . $edad . ' años', $labelWidth, $dataWidth);
}

// Profesión
$profesion = !empty($estudiante['NombreProfesion']) ? $estudiante['NombreProfesion'] : 'NO ESPECIFICADO';
$y = addField($pdf, $leftMargin, $y, 'PROFESIÓN:', $profesion, $labelWidth, $dataWidth);

// Lugar de Trabajo
$trabajo = !empty($estudiante['Trabajo']) ? $estudiante['Trabajo'] : 'NO ESPECIFICADO';
$y = addField($pdf, $leftMargin, $y, 'LUGAR DE TRABAJO:', $trabajo, $labelWidth, $dataWidth);

// ========================================
// DATOS DE CONTACTO
// ========================================
$y += 0;
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY($leftMargin, $y);
$pdf->Cell(0, 6, 'DATOS DE CONTACTO', 0, 1, 'L');
$y += 8;

// Dirección
$direccion = !empty($estudiante['Direccion']) ? $estudiante['Direccion'] : 'NO ESPECIFICADO';
$y = addField($pdf, $leftMargin, $y, 'DIRECCIÓN:', $direccion, $labelWidth, $dataWidth);

// Teléfono
$telefono = !empty($estudiante['Telefono']) ? $estudiante['Telefono'] : 'NO ESPECIFICADO';
$y = addField($pdf, $leftMargin, $y, 'TELÉFONO:', $telefono, $labelWidth, $dataWidth);

// Celular
$celular = !empty($estudiante['Celular']) ? $estudiante['Celular'] : 'NO ESPECIFICADO';
$y = addField($pdf, $leftMargin, $y, 'CELULAR:', $celular, $labelWidth, $dataWidth);

// Correo Electrónico
$correo = !empty($estudiante['Correo']) ? $estudiante['Correo'] : 'NO ESPECIFICADO';
$y = addField($pdf, $leftMargin, $y, 'CORREO ELECTRÓNICO:', $correo, $labelWidth, $dataWidth);

// ========================================
// PROGRAMA DE POSGRADO
// ========================================
$y += 0;
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY($leftMargin, $y);
$pdf->Cell(0, 6, 'PROGRAMA DE POSGRADO', 0, 1, 'L');
$y += 8;

// Programa Inscrito
$programa = $estudiante['NombrePrograma'];
$y = addField($pdf, $leftMargin, $y, 'PROGRAMA INSCRITO:', $programa, $labelWidth, 120);

// Grado Académico
$grado = $estudiante['GradoAcademico'];
$y = addField($pdf, $leftMargin, $y, 'GRADO ACADÉMICO:', $grado, $labelWidth, $dataWidth);

// Código del Programa
$codigo = $estudiante['CodigoPrograma'];
$y = addField($pdf, $leftMargin, $y, 'CÓDIGO DEL PROGRAMA:', $codigo, $labelWidth, $dataWidth);


// Texto "OBSERVACIONES:"
$pdf->SetXY($leftMargin, $y);
$pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1);

// Primera línea
$pdf->SetX($leftMargin);
$pdf->Cell(0, 8, '_____________________________________________________________________________________________________', 0, 1);

// Segunda línea
$pdf->SetX($leftMargin);
$pdf->Cell(0, 8, '_____________________________________________________________________________________________________', 0, 1);

// Actualizar Y
$y += 20;

// ========================================
// ESPACIO PARA FOTO (6x6 cm)
// ========================================
$fotoX = 17;
$fotoY = 192;
$fotoTamano = 60; // 6 cm = 60 mm

// Rectángulo para la foto
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.3);
$pdf->Rect($fotoX, $fotoY, $fotoTamano, $fotoTamano);

// Texto dentro del rectángulo de foto
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY($fotoX, $fotoY + 25);
$pdf->Cell($fotoTamano, 6, 'FOTO', 0, 1, 'C');
$pdf->SetXY($fotoX, $fotoY + 31);
$pdf->Cell($fotoTamano, 6, '6 x 6 cm', 0, 1, 'C');
// ========================================
// ESPACIO PARA FIRMA
// ========================================
$y += 30;
// Fecha de Inscripción


// Línea para firma
$firmaX = 90;
$firmaY = $y;
$pdf->SetDrawColor(100, 100, 100);
$pdf->SetLineWidth(0.3);
$pdf->Line($firmaX, $firmaY, $firmaX + 60, $firmaY);

// Texto "FIRMA DEL ESTUDIANTE"
// Firma
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY($firmaX, $firmaY + 2);
$pdf->Cell(60, 5, 'FIRMA DEL POSGRADUANTE', 0, 1, 'C');

// Fecha debajo de la firma
$fechaInscripcion = date('d/m/Y', strtotime($estudiante['FechaInscripcion']));

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);

// Posición de la fecha, un poco más abajo de la firma
$pdf->SetXY($firmaX, $firmaY + 12);
$pdf->Cell(60, 5, 'FECHA DE INSCRIPCIÓN: ' . $fechaInscripcion, 0, 1, 'C');

// ========================================
// CÓDIGO QR
// ========================================
$qrX = 170;
$qrY = 225;
$qrSize = 20; // 2 cm

// Preparar datos para el QR
$datosQR = [
    'id' => $estudiante['EstudianteID'],
    'ci' => $ci,
    'nombre' => $nombreCompleto,
    'programa' => $estudiante['NombrePrograma'],
    'codigo' => $estudiante['CodigoPrograma'],
    'fecha' => $fechaInscripcion
];

// Convertir a JSON
$qrContent = json_encode($datosQR, JSON_UNESCAPED_UNICODE);

// Generar código QR
// Parámetros: contenido, tipo, posición X, posición Y, ancho, alto, estilo, alineación
$style = [
    'border' => 2,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => [0, 0, 0],
    'bgcolor' => [255, 255, 255],
    'module_width' => 1,
    'module_height' => 1
];

$pdf->write2DBarcode($qrContent, 'QRCODE,H', $qrX, $qrY, $qrSize, $qrSize, $style, 'N');


$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY($qrX, $qrY + $qrSize + 6);
$pdf->Cell($qrSize, 3, 'Escanear para verificar', 0, 1, 'C');

// ========================================
// PIE DE PÁGINA
// ========================================
$pdf->SetY(-24);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 4, 'Fecha de emisión: ' . date('d/m/Y H:i'), 0, 1, 'C');
$pdf->Cell(0, 4, 'Este documento es un registro oficial del Sistema de Gestión de Posgrado FCS', 0, 1, 'C');

// Generar PDF
$pdf->Output('Ficha_Inscripcion_' . $idInscripcion . '_' . date('Ymd') . '.pdf', 'I');
?>
