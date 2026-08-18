<?php
/**
 * Generar Orden de Pago en PDF (formato oficial UTO - Coordinación de Posgrado)
 * Se imprimen dos copias (ORIGINAL y COPIA) en una sola hoja carta.
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
$montoNumeral = $_POST['montoNumeral'] ?? '';
$montoLiteral = $_POST['montoLiteral'] ?? '';
$version = $_POST['version'] ?? '';
$numeroTramite = $_POST['numeroTramite'] ?? '';
$nombreFactura = $_POST['nombreFactura'] ?? '';
$nitCiFactura = $_POST['nitCiFactura'] ?? '';

$nombreCompleto = trim("$apaterno $amaterno $nombres");

// Colores
$colorEtiqueta = [214, 106, 20]; // naranja (etiquetas del formato oficial)
$colorTexto = [30, 30, 30];

/**
 * Dibuja un bloque completo de la orden de pago (se usa dos veces: original y copia)
 */
function dibujarOrdenPago($pdf, $yInicio, $esCopia, $datos, $colorEtiqueta, $colorTexto)
{
    extract($datos);

    $page_width = $pdf->GetPageWidth();
    $margin = 10;
    $x_left = $margin;
    $content_width = $page_width - (2 * $margin);

    $pdf->SetY($yInicio);

    // ========================================
    // CABECERA
    // ========================================
    $imagen_izquierda = '../../extensiones/imagenespdf/logouto.png';
    $imagen_derecha = '../../extensiones/imagenespdf/logofcs.png';

    $width_img = 13;
    if (file_exists($imagen_izquierda)) {
        $pdf->Image($imagen_izquierda, $x_left, $yInicio, $width_img);
    }
    if (file_exists($imagen_derecha)) {
        $pdf->Image($imagen_derecha, $page_width - $margin - $width_img, $yInicio, $width_img);
    }

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetY($yInicio);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 4.2, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 3.8, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 3.4, 'COORDINACION DE POSGRADO-ODONTOLOGIA', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 6);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 3, 'Av. Del Minero Edificio San Agustín II (Ex Almacenes COMIBOL) Telf: 5237317 - Fax 5247110 - Oruro - Bolivia', 0, 1, 'C');

    if ($esCopia) {
        // Se dibuja la etiqueta arriba a la derecha (junto al logo) sin perder la posición Y actual
        $y_actual = $pdf->GetY();
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->SetXY($page_width - $margin - 25, $yInicio);
        $pdf->Cell(25, 3, '** COPIA **', 0, 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY($y_actual);
    }

    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line($x_left, $pdf->GetY() + 0.8, $page_width - $margin, $pdf->GetY() + 0.8);
    $pdf->Ln(1.6);

    // ========================================
    // TÍTULO
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 6, 'ORDEN DE PAGO EN CAJA DE POSGRADO', 1, 1, 'C');

    // ========================================
    // DATOS DEL POSGRADUANTE
    // ========================================
    $W_ENCABEZADO = 36;
    $W_TOTAL_DATOS = $content_width - $W_ENCABEZADO;
    $W_CELDA = $W_TOTAL_DATOS / 3;
    $H_FILA_TITULO = 3.5;
    $H_FILA_VALOR = 4.5;
    $H_TOTAL = ($H_FILA_TITULO + $H_FILA_VALOR) * 2;

    $y_bloque = $pdf->GetY();

    $pdf->SetFont('helvetica', 'BI', 6.5);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->SetXY($x_left, $y_bloque);
    $pdf->MultiCell($W_ENCABEZADO, $H_TOTAL, 'NOMBRE COMPLETO DEL POSGRADUANTE:', 1, 'L', false, 0, '', '', true, 0, false, true, $H_TOTAL, 'M');

    $x_tabla = $x_left + $W_ENCABEZADO;

    $pdf->SetXY($x_tabla, $y_bloque);
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO PATERNO', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO MATERNO', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'NOMBRE (S)', 1, 1, 'L');

    $pdf->SetX($x_tabla);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $apaterno, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $amaterno, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $nombres, 1, 1, 'L');

    $pdf->SetX($x_tabla);
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'CORREO ELECTRONICO', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'C.I.', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'N° DE CELULAR:', 1, 1, 'L');

    $pdf->SetX($x_tabla);
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $correo, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $ci, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $celular, 1, 1, 'L');

    $pdf->SetY($y_bloque + $H_TOTAL);

    // ========================================
    // DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 5.5, 'DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO', 1, 1, 'C');

    $W_PROGRAMA = $content_width * 0.62;
    $W_DERECHA = $content_width - $W_PROGRAMA;
    $H_ROW_A = 4;
    $H_ROW_B = 7;
    $H_PROGRAMA = $H_ROW_A + $H_ROW_B;

    $y_prog = $pdf->GetY();

    // Fila A: etiqueta "PROGRAMA:" (izq) / "Versión y/o" + valor (der)
    $pdf->SetXY($x_left, $y_prog);
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_PROGRAMA, $H_ROW_A, 'PROGRAMA:', 1, 0, 'L');

    $pdf->Cell($W_DERECHA * 0.42, $H_ROW_A, 'Versión y/o', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_DERECHA * 0.58, $H_ROW_A, $version, 1, 1, 'L');

    // Fila B: texto del programa (izq, multilinea) / "Cuenta auxiliar" + valor (der)
    $pdf->SetXY($x_left, $y_prog + $H_ROW_A);
    $pdf->SetFont('helvetica', 'BI', 6.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->MultiCell($W_PROGRAMA, $H_ROW_B, $programa, 1, 'L', false, 0, '', '', true, 0, false, true, $H_ROW_B, 'M');

    $pdf->SetXY($x_left + $W_PROGRAMA, $y_prog + $H_ROW_A);
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_DERECHA * 0.42, $H_ROW_B, 'Cuenta auxiliar', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_DERECHA * 0.58, $H_ROW_B, $numeroTramite, 1, 1, 'L');

    $pdf->SetY($y_prog + $H_PROGRAMA);

    // Fila MONTO / LITERAL (en línea)
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(18, 5.5, 'MONTO:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell(28, 5.5, $montoNumeral, 1, 0, 'L');
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(18, 5.5, 'LITERAL:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'BI', 6.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($content_width - 18 - 28 - 18, 5.5, $montoLiteral, 1, 1, 'L');

    // ========================================
    // DATOS PARA LA EMISIÓN DE FACTURA
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 5.5, 'DATOS PARA LA EMISIÓN DE FACTURA', 1, 1, 'C');

    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(38, 5.5, 'NOMBRE DE LA FACTURA:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($content_width * 0.58 - 38, 5.5, $nombreFactura, 1, 0, 'L');
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(20, 5.5, 'NIT Ó C.I.:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($content_width - ($content_width * 0.58) - 20, 5.5, $nitCiFactura, 1, 1, 'L');

    // ========================================
    // DATOS PARA DEPÓSITO EN BANCO UNIÓN
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 5.5, 'DATOS PARA DEPOSITO EN BANCO UNION', 1, 1, 'C');

    $W_BANCO_IZQ = $content_width * 0.55;
    $W_BANCO_DER = $content_width - $W_BANCO_IZQ;

    $pdf->SetX($x_left);
    $pdf->SetFillColor(253, 235, 208);
    $pdf->SetFont('helvetica', 'BI', 6);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_BANCO_IZQ, 4.5, 'DENOMINACION DE LA CUENTA', 1, 0, 'L', true);
    $pdf->Cell($W_BANCO_DER, 4.5, 'NUMERO DE CUENTA', 1, 1, 'L', true);

    $y_cuenta = $pdf->GetY();
    $pdf->SetFont('helvetica', 'B', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->SetXY($x_left, $y_cuenta);
    $pdf->MultiCell($W_BANCO_IZQ, 8, "UTO\nAPORTES EXTRAORDINARIOS", 1, 'L', true, 0, '', '', true, 0, false, true, 8, 'M');
    $pdf->SetXY($x_left + $W_BANCO_IZQ, $y_cuenta);
    $pdf->Cell($W_BANCO_DER, 8, '10000006050938', 1, 0, 'L', true);
    $pdf->Ln(8);

    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($content_width, 4.5, 'NIT: 120129022', 1, 1, 'L', true);

    return $pdf->GetY() - $yInicio;
}

// Datos comunes para las dos copias
$datos = compact(
    'apaterno', 'amaterno', 'nombres', 'correo', 'ci', 'celular',
    'programa', 'montoNumeral', 'montoLiteral', 'version', 'numeroTramite',
    'nombreFactura', 'nitCiFactura'
);

// Crear instancia de TCPDF
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

$pdf->SetCreator('Universidad Técnica de Oruro');
$pdf->SetAuthor('Facultad de Ciencias de la Salud - Postgrado');
$pdf->SetTitle('Orden de Pago - ' . $nombreCompleto);
$pdf->SetSubject('Orden de Pago en Caja de Posgrado');

$pdf->SetMargins(10, 8, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(5);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false);

// Márgenes internos de celda (espacio entre el texto y los bordes) e interlineado compacto
$pdf->setCellPaddings(1, 0.5, 1, 0.5);
$pdf->setCellHeightRatio(1.05);

$pdf->AddPage();

// ORIGINAL (mitad superior de la hoja)
$alturaBloque = dibujarOrdenPago($pdf, 8, false, $datos, $colorEtiqueta, $colorTexto);

// Línea divisoria punteada entre original y copia
$y_divisoria = 8 + $alturaBloque + 6;
$pdf->SetLineStyle(['width' => 0.2, 'dash' => 2, 'color' => [150, 150, 150]]);
$pdf->Line(10, $y_divisoria, $pdf->GetPageWidth() - 10, $y_divisoria);
$pdf->SetLineStyle(['dash' => 0]);

// COPIA (mitad inferior de la hoja)
dibujarOrdenPago($pdf, $y_divisoria + 6, true, $datos, $colorEtiqueta, $colorTexto);

// ========================================
// SALIDA DEL PDF
// ========================================
if (ob_get_level()) {
    ob_end_clean();
}

$nombreArchivo = 'Orden_Pago_' . str_replace(' ', '_', $nombreCompleto) . '_' . date('YmdHis') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
