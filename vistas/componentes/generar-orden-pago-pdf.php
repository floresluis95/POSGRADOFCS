<?php
/**
 * Generar Orden de Pago en PDF usando TCPDF
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    die('Acceso denegado');
}

// Verificar que se recibieron los datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método no permitido');
}

// Incluir TCPDF
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Recibir datos del POST
$apaterno = $_POST['apaterno'] ?? '';
$amaterno = $_POST['amaterno'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$correo = $_POST['correo'] ?? '';
$ci = $_POST['ci'] ?? '';
$celular = $_POST['celular'] ?? '';
$programa = $_POST['programa'] ?? '';
$modulo = $_POST['modulo'] ?? '';
$montoNumeral = $_POST['montoNumeral'] ?? '';
$montoLiteral = $_POST['montoLiteral'] ?? '';
$version = $_POST['version'] ?? '';
$cuentaAuxiliar = $_POST['cuentaAuxiliar'] ?? '';
$nombreFactura = $_POST['nombreFactura'] ?? '';
$nitCiFactura = $_POST['nitCiFactura'] ?? '';
$responsable = $_POST['responsable'] ?? '';
$firma = $_POST['firma'] ?? '';

// Nombre completo del estudiante
$nombreCompleto = trim("$apaterno $amaterno $nombres");

// Fecha y hora actual
$fechaActual = date('d/m/Y');
$horaActual = date('H:i:s');

// Crear instancia de TCPDF
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

// Configurar información del documento
$pdf->SetCreator('Universidad Técnica de Oruro');
$pdf->SetAuthor('Facultad de Ciencias de la Salud - Postgrado');
$pdf->SetTitle('Orden de Pago - ' . $nombreCompleto);
$pdf->SetSubject('Orden de Pago de Módulo');

// Configurar márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Quitar header y footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar auto page break
$pdf->SetAutoPageBreak(TRUE, 15);

// Agregar página
$pdf->AddPage();

// Configurar fuente
$pdf->SetFont('helvetica', '', 10);

// ========================================
// HEADER DEL DOCUMENTO
// ========================================
// Asegúrate de que las rutas a las imágenes sean correctas
$imagen_izquierda = '../../extensiones/imagenespdf/logouto.png';
$imagen_derecha = '../../extensiones/imagenespdf/logofcs.png';

// --- CONFIGURACIÓN DE COLORES Y FUENTE BASE (Solo el color del texto será negro) ---
$pdf->SetTextColor(0, 0, 0); // Texto negro (RGB 0, 0, 0)
$pdf->SetDrawColor(0, 0, 0); // Para bordes, si los hubiere

// La altura de celda (el interlineado) se ajusta a 4 para ser muy compacto.

// --- 1. COLOCAR IMAGEN IZQUIERDA ---
// $pdf->Image(ruta, X, Y, Ancho, Alto);
// Ajusta las coordenadas X, Y, Ancho y Alto según el tamaño real de tu documento.
$x_left = 10; // Posición X desde el borde izquierdo
$y_top = 10;  // Posición Y desde el borde superior
$width_img = 20; // Ancho de la imagen
$height_img = 20; // Alto de la imagen
$pdf->Image($imagen_izquierda, $x_left, $y_top, $width_img, $height_img);


// --- 2. TEXTO CENTRADO (Con interlineado ajustado a 4) ---

// UNIVERSIDAD TECNICA DE ORURO (Interlineado de 4)
$pdf->SetFont('helvetica', 'B', 10);
// NOTA: Se usa 'false' en el último parámetro para NO aplicar relleno de fondo
$pdf->Cell(0, 4, 'UNIVERSIDAD TECNICA DE ORURO', 0, 1, 'C', false);

// FACULTAD DE CIENCIAS DE LA SALUD (Interlineado de 4)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 4, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C', false);

// COORDINACION DE POSGRADO - ODONTOLOGIA (Interlineado de 4)
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 4, 'COORDINACION DE POSGRADO - ODONTOLOGIA', 0, 1, 'C', false);

// Dirección (Interlineado de 3.5, aún más compacto)
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 3.5, 'Av. Del Minero Edificio San Agustin II (Ex Almacenes COMIBOL) Telefono: 5237317 - Fax: 5247110', 0, 1, 'C', false);

// Ciudad (Interlineado de 3.5)
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 3.5, 'Oruro - Bolivia', 0, 1, 'C', false);


// --- 3. COLOCAR IMAGEN DERECHA ---
// Necesitas calcular la posición X para que la imagen quede pegada al borde derecho.
// Ancho Total del Documento (ej. A4 es 210mm) - Margen Derecho - Ancho de la Imagen
$page_width = $pdf->GetPageWidth(); // Obtiene el ancho de la página actual
$margin_right = 10; // Asumiendo un margen derecho de 10
$x_right = $page_width - $margin_right - $width_img; 

$pdf->Image($imagen_derecha, $x_right, $y_top, $width_img, $height_img);
// Línea separadora
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);

$pdf->Ln(5);

// Fecha y Hora
$pdf->SetTextColor(70, 78, 95);
$pdf->SetFont('helvetica', 'B', 9);
$y = $pdf->GetY();
$pdf->Cell(95, 5, 'Fecha: ' . $fechaActual, 0, 0, 'L');
$pdf->Cell(95, 5, 'Hora: ' . $horaActual, 0, 1, 'R');

$pdf->Ln(3);

// ========================================
// SECCIÓN: DATOS DEL ESTUDIANTE
// ========================================
$pdf->SetFillColor(17, 153, 142); // Color #11998e
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'DATOS DEL ESTUDIANTE', 0, 1, 'L', true);

// Contenido de la sección
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(70, 78, 95);
$pdf->SetFont('helvetica', '', 9);

// Crear tabla
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(60, 5, 'APELLIDO PATERNO', 0, 0, 'L');
$pdf->Cell(60, 5, 'APELLIDO MATERNO', 0, 0, 'L');
$pdf->Cell(60, 5, 'NOMBRES', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 6, $apaterno, 0, 0, 'L');
$pdf->Cell(60, 6, $amaterno, 0, 0, 'L');
$pdf->Cell(60, 6, $nombres, 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(60, 5, 'CORREO ELECTRÓNICO', 0, 0, 'L');
$pdf->Cell(60, 5, 'C.I.', 0, 0, 'L');
$pdf->Cell(60, 5, 'N° CELULAR', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(60, 6, $correo, 0, 0, 'L');
$pdf->Cell(60, 6, $ci, 0, 0, 'L');
$pdf->Cell(60, 6, $celular, 0, 1, 'L');

$pdf->Ln(5);

// ========================================
// SECCIÓN: DATOS PARA EMISIÓN DE COMPROBANTE
// ========================================
$pdf->SetFillColor(253, 57, 122); // Color #fd397a
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO', 0, 1, 'L', true);

$pdf->SetTextColor(70, 78, 95);

// Programa
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(0, 5, 'PROGRAMA', 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51);
$pdf->MultiCell(0, 5, $programa, 0, 'L');

$pdf->Ln(1);

// Módulo
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(0, 5, 'MÓDULO', 0, 1, 'L');
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51);
$pdf->MultiCell(0, 5, $modulo, 0, 'L');

$pdf->Ln(1);

// Monto
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(90, 5, 'MONTO (NUMERAL)', 0, 0, 'L');
$pdf->Cell(90, 5, 'MONTO (LITERAL)', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(17, 153, 142);
$pdf->Cell(90, 8, $montoNumeral, 0, 0, 'L');

$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(70, 78, 95);
$pdf->MultiCell(90, 8, $montoLiteral, 0, 'L');

$pdf->Ln(5);

// ========================================
// SECCIÓN: FORMULARIO DE REGISTRO
// ========================================
$pdf->SetFillColor(102, 126, 234);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, 'FORMULARIO DE REGISTRO', 0, 1, 'L', true);

$pdf->SetTextColor(70, 78, 95);

// Versión y Cuenta Auxiliar
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(90, 5, 'VERSIÓN', 0, 0, 'L');
$pdf->Cell(90, 5, 'CUENTA AUXILIAR', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(90, 6, $version, 0, 0, 'L');
$pdf->Cell(90, 6, $cuentaAuxiliar, 0, 1, 'L');

$pdf->Ln(3);

// Subsección: Datos para Emisión de Factura
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(102, 126, 234);
$pdf->Cell(0, 6, 'DATOS PARA LA EMISIÓN DE FACTURA', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(90, 5, 'NOMBRE DE LA FACTURA', 0, 0, 'L');
$pdf->Cell(90, 5, 'NIT O CI', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(90, 6, $nombreFactura, 0, 0, 'L');
$pdf->Cell(90, 6, $nitCiFactura, 0, 1, 'L');

$pdf->Ln(5);

// ========================================
// DENOMINACIÓN DE LA CUENTA
// ========================================
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(102, 126, 234);
$pdf->SetLineWidth(0.5);
$pdf->Rect($pdf->GetX(), $pdf->GetY(), 180, 18, 'D');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(102, 126, 234);
$pdf->Cell(0, 6, 'DENOMINACIÓN DE LA CUENTA', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(70, 78, 95);
$pdf->Cell(0, 5, 'UTO - APORTES EXTRAORDINARIOS - NÚMERO DE CUENTA 10000006050938', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(17, 153, 142);
$pdf->Cell(0, 5, 'NIT: 120129022', 0, 1, 'L');

$pdf->Ln(8);

// ========================================
// RESPONSABLE Y FIRMA
// ========================================
$pdf->SetDrawColor(226, 229, 236);
$pdf->SetLineWidth(0.3);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(90, 5, 'RESPONSABLE', 0, 0, 'L');
$pdf->Cell(90, 5, 'FIRMA', 0, 1, 'L');

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(90, 6, $responsable, 0, 0, 'L');
$pdf->Cell(90, 6, !empty($firma) ? $firma : '____________________', 0, 1, 'L');

$pdf->Ln(15);

// Línea para firma del responsable
$pdf->SetLineWidth(0.3);
$pdf->Line(80, $pdf->GetY(), 130, $pdf->GetY());
$pdf->Ln(2);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(0, 5, 'Firma del Responsable', 0, 1, 'C');

$pdf->Ln(5);

// Pie de página
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(0, 5, 'Este documento fue generado electrónicamente el ' . $fechaActual . ' a las ' . $horaActual, 0, 1, 'C');

// ========================================
// SALIDA DEL PDF
// ========================================
// Limpiar cualquier salida previa
ob_end_clean();

// Generar el PDF
$nombreArchivo = 'Orden_Pago_' . str_replace(' ', '_', $nombreCompleto) . '_' . date('YmdHis') . '.pdf';
$pdf->Output($nombreArchivo, 'I'); // I = inline (mostrar en navegador), D = descargar

exit;
?>
