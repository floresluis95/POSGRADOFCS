<?php
/**
 * Generar Orden de Pago en PDF usando TCPDF - Con Copia
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión
if (!isset($_SESSION['Validar']) || !$_SESSION['Validar']) {
    error_log("PDF: Acceso denegado - sesión no válida");
    die('Acceso denegado - Por favor inicie sesión nuevamente');
}

// Verificar que se recibieron los datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("PDF: Método no permitido - Se esperaba POST");
    die('Método no permitido - Se esperaba POST');
}

// Log de datos recibidos para debugging
error_log("PDF: Datos POST recibidos: " . print_r($_POST, true));

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
$numeroTramite = $_POST['numeroTramite'] ?? '';
$cuentaAuxiliar = $_POST['cuentaAuxiliar'] ?? '';
$nombreFactura = $_POST['nombreFactura'] ?? '';
$nitCiFactura = $_POST['nitCiFactura'] ?? '';
$responsable = $_POST['responsable'] ?? '';
$firma = $_POST['firma'] ?? '';

// Verificar si es orden múltiple
$esMultiple = isset($_POST['esMultiple']) && $_POST['esMultiple'] === 'true';
$modulos = [];
$numeroOrden = $_POST['numeroOrden'] ?? '';

if ($esMultiple) {
    // Decodificar JSON de módulos
    $modulosJSON = $_POST['modulosJSON'] ?? '[]';
    $modulos = json_decode($modulosJSON, true);
    if (!is_array($modulos)) {
        $modulos = [];
    }
}

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

// Configurar márgenes más pequeños
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// Quitar header y footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Configurar auto page break
$pdf->SetAutoPageBreak(FALSE);

// Agregar página
$pdf->AddPage();

// Función para generar el contenido de la orden de pago
function generarContenidoOrden($pdf, $yInicio, $esCopia = false) {
    global $apaterno, $amaterno, $nombres, $correo, $ci, $celular;
    global $programa, $modulo, $montoNumeral, $montoLiteral;
    global $version, $numeroTramite, $cuentaAuxiliar;
    global $nombreFactura, $nitCiFactura, $responsable, $firma;
    global $fechaActual, $horaActual;
    global $esMultiple, $modulos, $numeroOrden;

    // Establecer posición Y inicial
    $pdf->SetY($yInicio);

    // Rutas a las imágenes
    $imagen_izquierda = '../../extensiones/imagenespdf/logouto.png';
    $imagen_derecha = '../../extensiones/imagenespdf/logofcs.png';

    // Configuración de colores y fuente base
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);

    // Imágenes del header
    $x_left = 10;
    $y_top = $yInicio;
    $width_img = 15;
    $height_img = 15;
    $pdf->Image($imagen_izquierda, $x_left, $y_top, $width_img, $height_img);

    $page_width = $pdf->GetPageWidth();
    $margin_right = 10;
    $x_right = $page_width - $margin_right - $width_img;
    $pdf->Image($imagen_derecha, $x_right, $y_top, $width_img, $height_img);

    // Texto del header (reducido)
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(0, 3, 'UNIVERSIDAD TECNICA DE ORURO', 0, 1, 'C', false);

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(0, 3, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C', false);

    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(0, 3, 'COORDINACION DE POSGRADO - ODONTOLOGIA', 0, 1, 'C', false);

    $pdf->SetFont('helvetica', '', 6);
    $pdf->Cell(0, 2.5, 'Av. Del Minero Edificio San Agustin II (Ex Almacenes COMIBOL) Telefono: 5237317', 0, 1, 'C', false);

    $pdf->SetFont('helvetica', '', 6);
    $pdf->Cell(0, 2.5, 'Oruro - Bolivia', 0, 1, 'C', false);

    // Línea separadora
    $pdf->SetDrawColor(102, 126, 234);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(10, $pdf->GetY() + 1, $page_width - 10, $pdf->GetY() + 1);

    $pdf->Ln(2);

    // Fecha, Hora y texto COPIA
    $pdf->SetTextColor(70, 78, 95);
    $pdf->SetFont('helvetica', 'B', 7);
    $y_fecha = $pdf->GetY();
    $pdf->SetXY(10, $y_fecha);
    $pdf->Cell(60, 3, 'Fecha: ' . $fechaActual, 0, 0, 'L');
    $pdf->Cell(60, 3, 'Hora: ' . $horaActual, 0, 0, 'C');
    if ($esCopia) {
        $pdf->SetTextColor(255, 0, 0);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 3, '** COPIA **', 0, 1, 'R');
        $pdf->SetTextColor(70, 78, 95);
    } else {
        $pdf->Ln();
    }

    $pdf->Ln(1);

    // Título
    $pdf->SetFillColor(174, 198, 207);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Cell(0, 5, 'ORDEN DE PAGO EN CAJA DE POSGRADO', 0, 1, 'C', true);

    $pdf->Ln(1);

    // DATOS DEL ESTUDIANTE
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetTextColor(51, 51, 51);

    $W_ENCABEZADO = 25;
    $W_TOTAL_DATOS = 165;
    $W_CELDA = $W_TOTAL_DATOS / 3;
    $H_FILA_TITULO = 3.5;
    $H_FILA_VALOR = 4;
    $H_TOTAL = ($H_FILA_TITULO + $H_FILA_VALOR) * 2;

    $x_inicio_bloque = $pdf->GetX();
    $y_inicio_bloque = $pdf->GetY();

    // Encabezado lateral
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->MultiCell($W_ENCABEZADO, $H_TOTAL, 'NOMBRE COMPLETO DEL POSGRADUANTE', 1, 'C', true, 0);

    // Tabla de datos
    $pdf->SetXY($x_inicio_bloque + $W_ENCABEZADO, $y_inicio_bloque);
    $x_inicio_tabla = $pdf->GetX();
    $pdf->SetFillColor(248, 249, 250);

    // Fila 1: Títulos
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO PATERNO', 1, 0, 'L', false);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO MATERNO', 1, 0, 'L', false);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'NOMBRES', 1, 1, 'L', false);

    // Fila 2: Valores
    $pdf->SetX($x_inicio_tabla);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $apaterno, 1, 0, 'L', true);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $amaterno, 1, 0, 'L', true);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $nombres, 1, 1, 'L', true);

    // Fila 3: Títulos
    $pdf->SetX($x_inicio_tabla);
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'CORREO ELECTRÓNICO', 1, 0, 'L', false);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'C.I.', 1, 0, 'L', false);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'N° CELULAR', 1, 1, 'L', false);

    // Fila 4: Valores
    $pdf->SetX($x_inicio_tabla);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $correo, 1, 0, 'L', true);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $ci, 1, 0, 'L', true);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $celular, 1, 1, 'L', true);

    $pdf->Ln(2);

    // DATOS PARA EMISIÓN DE COMPROBANTE
    $pdf->SetFillColor(174, 198, 207);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Cell(0, 5, 'DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO', 0, 1, 'C', true);

    $pdf->SetTextColor(70, 78, 95);

    // PROGRAMA
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 3, 'PROGRAMA', 1, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->MultiCell(0, 4, $programa, 1, 'L', false, 1);

    // VERSIÓN y N° TRÁMITE (CUENTA AUXILIAR) en una fila
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(40, 3, 'VERSIÓN', 1, 0, 'L');
    $pdf->Cell(0, 3, 'N° DE TRÁMITE (CUENTA AUXILIAR)', 1, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(40, 4, $version, 1, 0, 'L');
    $pdf->Cell(0, 4, $numeroTramite, 1, 1, 'L');

    $pdf->Ln(1);

    // MONTO
    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(50, 3, 'MONTO (NUMERAL)', 1, 0, 'L');
    $pdf->Cell(0, 3, 'MONTO (LITERAL)', 1, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(50, 5, $montoNumeral, 1, 0, 'L');
    $pdf->MultiCell(0, 5, $montoLiteral, 1, 'L');

    $pdf->Ln(2);

    // DATOS PARA EMISIÓN DE FACTURA
    $pdf->SetFillColor(174, 198, 207);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Cell(0, 5, 'DATOS PARA LA EMISIÓN DE FACTURA', 0, 1, 'C', true);

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(95, 3, 'NOMBRE DE LA FACTURA', 1, 0, 'L');
    $pdf->Cell(0, 3, 'NIT O CI', 1, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(95, 4, $nombreFactura, 1, 0, 'L');
    $pdf->Cell(0, 4, $nitCiFactura, 1, 1, 'L');

    $pdf->Ln(2);

    // DENOMINACIÓN DE LA CUENTA
    $pdf->SetFillColor(248, 249, 250);
    $pdf->SetDrawColor(102, 126, 234);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect($pdf->GetX(), $pdf->GetY(), $page_width - 20, 12, 'D');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(102, 126, 234);
    $pdf->Cell(0, 4, 'DENOMINACIÓN DE LA CUENTA', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(70, 78, 95);
    $pdf->Cell(0, 3, 'UTO - APORTES EXTRAORDINARIOS - N° CUENTA 10000006050938', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(17, 153, 142);
    $pdf->Cell(0, 3, 'NIT: 120129022', 0, 1, 'L');

    $pdf->Ln(3);

    // RESPONSABLE Y FIRMA
    $pdf->SetDrawColor(226, 229, 236);
    $pdf->SetLineWidth(0.2);
    $pdf->Line(10, $pdf->GetY(), $page_width - 10, $pdf->GetY());

    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', 6);
    $pdf->SetTextColor(153, 153, 153);
    $pdf->Cell(95, 3, 'RESPONSABLE', 0, 0, 'L');
    $pdf->Cell(0, 3, 'FIRMA', 0, 1, 'L');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(95, 4, $responsable, 0, 0, 'L');
    $pdf->Cell(0, 4, !empty($firma) ? $firma : '____________________', 0, 1, 'L');

    $pdf->Ln(3);

    // Línea para firma
    $pdf->SetLineWidth(0.2);
    $pdf->Line(80, $pdf->GetY(), 130, $pdf->GetY());
    $pdf->Ln(1);

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 3, 'Firma del Responsable', 0, 1, 'C');
}

// Generar el ORIGINAL (primera mitad de la página)
generarContenidoOrden($pdf, 10, false);

// Línea divisoria punteada
$pdf->SetY(137);
$pdf->SetDrawColor(150, 150, 150);
$pdf->SetLineStyle(array('width' => 0.2, 'dash' => 2));
$pdf->Line(10, 137, $pdf->GetPageWidth() - 10, 137);

// Generar la COPIA (segunda mitad de la página)
generarContenidoOrden($pdf, 140, true);

// Pie de página al final
$pdf->SetY(270);
$pdf->SetFont('helvetica', 'I', 6);
$pdf->SetTextColor(153, 153, 153);
$pdf->Cell(0, 3, 'Documento generado electrónicamente el ' . $fechaActual . ' a las ' . $horaActual, 0, 1, 'C');

// ========================================
// SALIDA DEL PDF
// ========================================
// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

// Generar el PDF
$nombreArchivo = 'Orden_Pago_' . str_replace(' ', '_', $nombreCompleto) . '_' . date('YmdHis') . '.pdf';
$pdf->Output($nombreArchivo, 'I');

exit;
?>
