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

$pdf->SetFillColor(174, 198, 207); // Color #04126aff
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 7, 'ORDEN DE PAGO EN CAJA DE POSGRADO', 0, 1, 'C', true);



// ========================================
// SECCIÓN: DATOS DEL ESTUDIANTE
// ========================================
// --- CONFIGURACIÓN DE COLOR DE BORDE (MARGENES) ---
$pdf->SetDrawColor(0, 0, 0); // Color del borde: Negro (RGB 0, 0, 0)

// --- Anchos de Columna ---
$W_ENCABEZADO = 30;
$W_CELDA = 160 / 3; // 53.33 mm por celda
$H_TOTAL = 27;      // Altura total del bloque

// --- Encabezado "NOMBRE COMPLETO DEL POSGRADUANTE" (MultiCell) ---
$pdf->SetFillColor(17, 153, 142); // Color de fondo: #11998e
$pdf->SetTextColor(255, 0, 0); 
$pdf->SetFont('helvetica', 'B', 10);

// 1. Guardar posición inicial (X, Y)
$x_inicio_columna = $pdf->GetX();
$y_inicio_columna = $pdf->GetY();

// 2. Usar MultiCell: El texto se ajustará automáticamente en varias líneas 
// dentro de los 30mm de ancho.
// El '4.5' es la altura de cada línea. 27/4.5 = 6 líneas (suficiente para el texto).
$pdf->MultiCell($W_ENCABEZADO, 4.5, 'NOMBRE COMPLETO DEL POSGRADUANTE', 1, 'C', true);

// 3. Mover el cursor para comenzar la tabla a la derecha
// Usamos SetXY para saltar de nuevo a la posición X del borde derecho del MultiCell
// y volver a la posición Y inicial.
$pdf->SetXY($x_inicio_columna + $W_ENCABEZADO, $y_inicio_columna);

// --- Preparación para la Tabla (a la derecha del encabezado) ---
$x_inicio_tabla = $pdf->GetX(); 
$y_inicio_tabla = $pdf->GetY(); 

// --- Creación de la Tabla ---
$pdf->SetFillColor(248, 249, 250); 
$pdf->SetTextColor(70, 78, 95);

// ******* Fila 1: Títulos de APELLIDOS/NOMBRES (Color ROJO) *******
$pdf->SetX($x_inicio_tabla); 

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(255, 0, 0); 
$pdf->Cell($W_CELDA, 4.5, 'APELLIDO PATERNO', 1, 0, 'L'); 
$pdf->Cell($W_CELDA, 4.5, 'APELLIDO MATERNO', 1, 0, 'L');
$pdf->Cell($W_CELDA, 4.5, 'NOMBRES', 1, 1, 'L');

// ******* Fila 2: Valores de APELLIDOS/NOMBRES (Color original) *******
$pdf->SetX($x_inicio_tabla); 

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51); 
$pdf->Cell($W_CELDA, 5.5, $apaterno, 1, 0, 'L');
$pdf->Cell($W_CELDA, 5.5, $amaterno, 1, 0, 'L');
$pdf->Cell($W_CELDA, 5.5, $nombres, 1, 1, 'L');

$pdf->Ln(1); // Salto de línea reducido a 1mm

// ******* Fila 3: Títulos de CORREO/CI/CELULAR (Color ROJO) *******
$pdf->SetX($x_inicio_tabla); 

$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(255, 0, 0); 
$pdf->Cell($W_CELDA, 4.5, 'CORREO ELECTRÓNICO', 1, 0, 'L');
$pdf->Cell($W_CELDA, 4.5, 'C.I.', 1, 0, 'L');
$pdf->Cell($W_CELDA, 4.5, 'N° CELULAR', 1, 1, 'L');

// ******* Fila 4: Valores de CORREO/CI/CELULAR (Color original) *******
$pdf->SetX($x_inicio_tabla); 

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51); 
$pdf->Cell($W_CELDA, 5.5, $correo, 1, 0, 'L');
$pdf->Cell($W_CELDA, 5.5, $ci, 1, 0, 'L');
$pdf->Cell($W_CELDA, 5.5, $celular, 1, 1, 'L');

$pdf->Ln(4);

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
