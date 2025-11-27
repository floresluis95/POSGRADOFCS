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

// Validar ID del estudiante
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Error: ID de estudiante no especificado');
}

$estudianteID = intval($_GET['id']);

// Obtener datos del estudiante
$estudiante = InscripcionModuloModelos::ObtenerDatosCompletosEstudianteModelo($estudianteID);

if (!$estudiante) {
    die('Error: No se encontró información del estudiante');
}

// Crear PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

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

// ========================================
// ENCABEZADO
// ========================================
$pdf->SetFont('helvetica', 'B', 18);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY(15, 15);
$pdf->Cell(0, 10, 'UNIVERSIDAD', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetXY(15, 23);
$pdf->Cell(0, 8, 'POSGRADO - FACULTAD DE CIENCIAS SOCIALES', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->SetXY(15, 31);
$pdf->Cell(0, 5, 'Sistema de Gestión Académica', 0, 1, 'C');

// Línea decorativa horizontal
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, 42, 195, 42);

// ========================================
// TÍTULO DEL DOCUMENTO
// ========================================
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(102, 126, 234);
$pdf->SetXY(15, 48);
$pdf->Cell(0, 8, 'FICHA DE INSCRIPCIÓN', 0, 1, 'C');

// ========================================
// ESPACIO PARA FOTO (6x6 cm)
// ========================================
$fotoX = 155;
$fotoY = 58;
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
// DATOS PERSONALES
// ========================================
$y = 58;
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

// Apellido Paterno
$y = addField($pdf, $leftMargin, $y, 'APELLIDO PATERNO:', $estudiante['Apaterno'], $labelWidth, $dataWidth);

// Apellido Materno
$y = addField($pdf, $leftMargin, $y, 'APELLIDO MATERNO:', $estudiante['Amaterno'], $labelWidth, $dataWidth);

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
    $y = addField($pdf, $leftMargin, $y, 'FECHA DE NACIMIENTO:', $fechaNac . ' (' . $edad . ' años)', $labelWidth, $dataWidth);
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
$y += 5;
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
$y += 5;
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

// Fecha de Inscripción
$fechaInscripcion = date('d/m/Y', strtotime($estudiante['FechaInscripcion']));
$y = addField($pdf, $leftMargin, $y, 'FECHA DE INSCRIPCIÓN:', $fechaInscripcion, $labelWidth, $dataWidth);

// ========================================
// ESPACIO PARA FIRMA
// ========================================
$y += 15;

// Línea para firma
$firmaX = 120;
$firmaY = $y;
$pdf->SetDrawColor(100, 100, 100);
$pdf->SetLineWidth(0.3);
$pdf->Line($firmaX, $firmaY, $firmaX + 60, $firmaY);

// Texto "FIRMA DEL ESTUDIANTE"
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(60, 60, 60);
$pdf->SetXY($firmaX, $firmaY + 2);
$pdf->Cell(60, 5, 'FIRMA DEL ESTUDIANTE', 0, 1, 'C');

// ========================================
// PIE DE PÁGINA
// ========================================
$pdf->SetY(-20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 4, 'Fecha de emisión: ' . date('d/m/Y H:i'), 0, 1, 'C');
$pdf->Cell(0, 4, 'Este documento es un registro oficial del Sistema de Gestión de Posgrado FCS', 0, 1, 'C');

// Generar PDF
$pdf->Output('Ficha_Inscripcion_' . $estudianteID . '_' . date('Ymd') . '.pdf', 'I');
?>
