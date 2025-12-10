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
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 7, 'ORDEN DE PAGO EN CAJA DE POSGRADO', 0, 1, 'C', true);



// ====================================================================
// SECCIÓN: DATOS DEL ESTUDIANTE (Posgraduante)
// ====================================================================

// --- 1. CONFIGURACIÓN GENERAL ---
// Color del borde de todas las celdas: Negro
$pdf->SetDrawColor(0, 0, 0); 
// Color general del texto para los datos (ej. un gris oscuro)
$pdf->SetTextColor(51, 51, 51); // #333333

// --- 2. DEFINICIÓN DE DIMENSIONES Y CONTENIDO ---
$W_ENCABEZADO = 30; // Ancho del encabezado lateral (30mm)
$W_TOTAL_DATOS = 160; // Ancho total de la tabla de datos
$W_CELDA = $W_TOTAL_DATOS / 3; // Ancho de cada celda de datos (160 / 3)

// Altura calculada: Suma de las alturas de las 4 filas de datos.
// Fila 1 (Título) + Fila 2 (Valor) + Fila 3 (Título) + Fila 4 (Valor)
$H_FILA_TITULO = 4.5;
$H_FILA_VALOR = 5.5;
$H_TOTAL = ($H_FILA_TITULO + $H_FILA_VALOR) * 2; // (4.5 + 5.5) * 2 = 20mm. 
// NOTA: El código original tenía H_TOTAL=27, lo cual es innecesario. 
// Usaremos la suma de las 4 alturas reales (20mm).

// --- 3. INICIO DE BLOQUE ---
$x_inicio_bloque = $pdf->GetX();
$y_inicio_bloque = $pdf->GetY();

// ====================================================================
// A. ENCABEZADO LATERAL: 'NOMBRE COMPLETO DEL POSGRADUANTE'
// ====================================================================

// Configuración de estilo del encabezado
$pdf->SetFillColor(255, 255, 255); // Fondo BLANCO (anula el #11998e original)
$pdf->SetTextColor(0, 0, 0);// Texto: Blanco (MEJORADO: No usar rojo aquí, es confuso)
$pdf->SetFont('helvetica', 'B', 8);

// Usar MultiCell con la altura TOTAL del bloque para asegurar alineación vertical
// El parámetro 'h' (altura de línea) del MultiCell se ajusta a H_TOTAL / (número de líneas necesarias).
// En este caso, simplemente usamos la altura total del bloque (20mm) como altura mínima.
// El '1' indica el borde, 'C' centra el texto, 'true' rellena el fondo.
$pdf->MultiCell(
    $W_ENCABEZADO, 
    $H_TOTAL, 
    'NOMBRE COMPLETO DEL POSGRADUANTE', 
    1, 
    'C', 
    true, 
    0 // Importante: 0 para continuar en la misma línea (a la derecha)
);

// ====================================================================
// B. BLOQUE DE DATOS (Tabla de 2x3)
// ====================================================================

// 1. Posicionar el cursor a la derecha del MultiCell, volviendo a Y inicial.
$pdf->SetXY($x_inicio_bloque + $W_ENCABEZADO, $y_inicio_bloque);
$x_inicio_tabla = $pdf->GetX(); // Guardar X inicial de la tabla

// 2. Definir estilos generales para las celdas de datos
$pdf->SetFillColor(248, 249, 250); // Fondo de las celdas de valor: Gris muy claro #F8F9FA

// --- Fila 1: Títulos APELLIDOS/NOMBRES (en ROJO) ---
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(255, 0, 0); // Títulos en ROJO

$pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO PATERNO', 1, 0, 'L', false); // false: sin relleno
$pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO MATERNO', 1, 0, 'L', false);
$pdf->Cell($W_CELDA, $H_FILA_TITULO, 'NOMBRES', 1, 1, 'L', false); // 1: Salto de línea

// --- Fila 2: Valores APELLIDOS/NOMBRES ---
$pdf->SetX($x_inicio_tabla); // Volver a la X de inicio de la tabla
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51); // Texto de valor: Gris oscuro

$pdf->Cell($W_CELDA, $H_FILA_VALOR, $apaterno, 1, 0, 'L', true); // true: con relleno
$pdf->Cell($W_CELDA, $H_FILA_VALOR, $amaterno, 1, 0, 'L', true);
$pdf->Cell($W_CELDA, $H_FILA_VALOR, $nombres, 1, 1, 'L', true); // 1: Salto de línea

// --- Fila 3: Títulos CORREO/CI/CELULAR (en ROJO) ---
$pdf->SetX($x_inicio_tabla); // Volver a la X de inicio de la tabla
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(255, 0, 0); // Títulos en ROJO

$pdf->Cell($W_CELDA, $H_FILA_TITULO, 'CORREO ELECTRÓNICO', 1, 0, 'L', false);
$pdf->Cell($W_CELDA, $H_FILA_TITULO, 'C.I.', 1, 0, 'L', false);
$pdf->Cell($W_CELDA, $H_FILA_TITULO, 'N° CELULAR', 1, 1, 'L', false); // 1: Salto de línea

// --- Fila 4: Valores CORREO/CI/CELULAR ---
$pdf->SetX($x_inicio_tabla); // Volver a la X de inicio de la tabla
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51); // Texto de valor: Gris oscuro

$pdf->Cell($W_CELDA, $H_FILA_VALOR, $correo, 1, 0, 'L', true);
$pdf->Cell($W_CELDA, $H_FILA_VALOR, $ci, 1, 0, 'L', true);
$pdf->Cell($W_CELDA, $H_FILA_VALOR, $celular, 1, 1, 'L', true); // 1: Salto de línea

// --- 4. SALTO DE LÍNEA ---
$pdf->Ln(4); // Separación con la siguiente sección

// ========================================
// SECCIÓN: DATOS PARA EMISIÓN DE COMPROBANTE
// ========================================
$pdf->SetFillColor(174, 198, 207); // Color #a5a5a5ff
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 7, 'DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO', 0, 1, 'C', true);

$pdf->SetTextColor(70, 78, 95);

// --- Sección 1: PROGRAMA, VERSIÓN y CUENTA AUXILIAR (Anchos Ajustados y Bordes) ---

// Definir estilo de las Etiquetas/Títulos (Rojo, 7pt)
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(255, 0, 0); // Color de texto: ROJO

// --- TÍTULOS ---
// Anchos ajustados: 80, 40, 0 (resto)
$pdf->Cell(80, 5, 'PROGRAMA', 1, 0, 'L'); // 80 ancho, Borde 1, no salta línea (0)
$pdf->Cell(40, 5, 'VERSIÓN', 1, 0, 'L');  // 40 ancho (más pequeño), Borde 1, no salta línea (0)
$pdf->Cell(0, 5, 'CUENTA AUXILIAR', 1, 1, 'L'); // Resto del ancho (automático), Borde 1, salta línea (1)

// Definir estilo de los Valores (Gris Oscuro, 9pt)
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(51, 51, 51); // Gris Oscuro (#333333)

// --- VALORES ---
// NOTA: MultiCell para el programa (si es largo). Usar 'false, 0' para evitar salto de línea.
$pdf->MultiCell(80, 5, $programa, 1, 'L', false, 0); // Ancho 80, Borde 1, no salta línea (0)
$pdf->Cell(40, 5, $version, 1, 0, 'L');             // Ancho 40, Borde 1, no salta línea (0)
$pdf->Cell(0, 5, $cuentaAuxiliar, 1, 1, 'L');        // Resto del ancho, Borde 1, salta línea (1)

$pdf->Ln(3); // Espacio

// --- Sección 3: MONTO (NUMERAL y LITERAL) (Bordes y Títulos Rojos) ---

// Definir estilo de las Etiquetas/Títulos (Rojo, 7pt)
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(255, 0, 0); // Color de texto: ROJO

// Etiquetas (Con borde: 1)
$pdf->Cell(90, 5, 'MONTO (NUMERAL)', 1, 0, 'L'); // 90 ancho, Borde 1, no salta línea (0)
$pdf->Cell(90, 5, 'MONTO (LITERAL)', 1, 1, 'L'); // 90 ancho, Borde 1, salta línea (1)

// Valor Numeral (Izquierda)
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(51, 51, 51);
$pdf->Cell(90, 8, $montoNumeral, 1, 0, 'L'); // 90 ancho, Borde 1, no salta línea (0)

// Valor Literal (Derecha)
$pdf->SetFont('helvetica', 'B', 8); // Cursiva, 8pt
$pdf->SetTextColor(51, 51, 51); // Gris Azulado (#464E5F)
$pdf->MultiCell(0, 8, $montoLiteral, 1, 0, 'L'); // Resto del ancho (0), Borde 1, salta línea

$pdf->Ln(5);
// Subsección: Datos para Emisión de Factura



$pdf->SetFillColor(174, 198, 207); // Color #a5a5a5ff
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 7, 'DATOS PARA LA EMISIÓN DE FACTURA', 0, 1, 'C', true);

$pdf->SetTextColor(70, 78, 95);

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
