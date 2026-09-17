<?php
/**
 * Render compartido de la Orden de Pago oficial (formato UTO - Coordinación de Posgrado,
 * doble copia ORIGINAL/COPIA en una sola hoja carta).
 *
 * Lo usan dos puntos de entrada distintos, para que ambos impriman exactamente el
 * mismo documento:
 *  - generar-orden-pago-pdf.php: el administrador, justo al generar la orden (POST).
 *  - orden-pago-pdf.php: el estudiante, desde "Mis Órdenes de Pago" (GET por ID).
 */

require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

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
    $pdf->SetFont('helvetica', 'B', 10.5);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 5, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 9.5);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 4.5, 'FACULTAD DE CIENCIAS DE LA SALUD', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 4, 'COORDINACION DE POSGRADO-ODONTOLOGIA', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetX($x_left);
    $pdf->Cell($content_width, 3.6, 'Calle Presidente Montes entre Murguía y Aldana, Edificio UTO 2do Piso - Telf: 5237317 - Fax 5247110 - Oruro - Bolivia', 0, 1, 'C');

    if ($esCopia) {
        // Se dibuja la etiqueta arriba a la derecha (junto al logo) sin perder la posición Y actual
        $y_actual = $pdf->GetY();
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->SetXY($page_width - $margin - 25, $yInicio);
        $pdf->Cell(25, 3, '** COPIA **', 0, 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY($y_actual);
    }

    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line($x_left, $pdf->GetY() + 0.8, $page_width - $margin, $pdf->GetY() + 0.8);
    $pdf->Ln(1.8);

    // ========================================
    // TÍTULO
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 7.5, 'ORDEN DE PAGO EN CAJA DE POSGRADO', 1, 1, 'C');

    // ========================================
    // DATOS DEL POSGRADUANTE
    // ========================================
    $W_ENCABEZADO = 40;
    $W_TOTAL_DATOS = $content_width - $W_ENCABEZADO;
    $W_CELDA = $W_TOTAL_DATOS / 3;
    $H_FILA_TITULO = 4.2;
    $H_FILA_VALOR = 5.5;
    $H_TOTAL = ($H_FILA_TITULO + $H_FILA_VALOR) * 2;

    $y_bloque = $pdf->GetY();

    $pdf->SetFont('helvetica', 'BI', 7.5);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->SetXY($x_left, $y_bloque);
    $pdf->MultiCell($W_ENCABEZADO, $H_TOTAL, 'NOMBRE COMPLETO DEL POSGRADUANTE:', 1, 'L', false, 0, '', '', true, 0, false, true, $H_TOTAL, 'M');

    $x_tabla = $x_left + $W_ENCABEZADO;

    $pdf->SetXY($x_tabla, $y_bloque);
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO PATERNO', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO MATERNO', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'NOMBRE (S)', 1, 1, 'L');

    $pdf->SetX($x_tabla);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $apaterno, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $amaterno, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $nombres, 1, 1, 'L');

    $pdf->SetX($x_tabla);
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'CORREO ELECTRONICO', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'C.I.', 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'N° DE CELULAR:', 1, 1, 'L');

    $pdf->SetX($x_tabla);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $correo, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $ci, 1, 0, 'L');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $celular, 1, 1, 'L');

    $pdf->SetY($y_bloque + $H_TOTAL);

    // ========================================
    // DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 6.5, 'DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO', 1, 1, 'C');

    $W_PROGRAMA = $content_width * 0.62;
    $W_DERECHA = $content_width - $W_PROGRAMA;
    $H_ROW_A = 5;
    $H_ROW_B = 8.5;
    $H_PROGRAMA = $H_ROW_A + $H_ROW_B;

    $y_prog = $pdf->GetY();

    // Fila A: etiqueta "PROGRAMA:" (izq) / "Versión y/o" + valor (der)
    $pdf->SetXY($x_left, $y_prog);
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_PROGRAMA, $H_ROW_A, 'PROGRAMA:', 1, 0, 'L');

    $pdf->Cell($W_DERECHA * 0.42, $H_ROW_A, 'Versión y/o', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_DERECHA * 0.58, $H_ROW_A, $version, 1, 1, 'L');

    // Fila B: texto del programa (izq, multilinea) / "Cuenta auxiliar" + valor (der)
    $pdf->SetXY($x_left, $y_prog + $H_ROW_A);
    $pdf->SetFont('helvetica', 'BI', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->MultiCell($W_PROGRAMA, $H_ROW_B, $programa, 1, 'L', false, 0, '', '', true, 0, false, true, $H_ROW_B, 'M');

    $pdf->SetXY($x_left + $W_PROGRAMA, $y_prog + $H_ROW_A);
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_DERECHA * 0.42, $H_ROW_B, 'Cuenta auxiliar', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($W_DERECHA * 0.58, $H_ROW_B, $numeroTramite, 1, 1, 'L');

    $pdf->SetY($y_prog + $H_PROGRAMA);

    // Fila MONTO / LITERAL (en línea)
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(20, 6.5, 'MONTO:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell(32, 6.5, $montoNumeral, 1, 0, 'L');
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(20, 6.5, 'LITERAL:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'BI', 7.5);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($content_width - 20 - 32 - 20, 6.5, $montoLiteral, 1, 1, 'L');

    // ========================================
    // DATOS PARA LA EMISIÓN DE FACTURA
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 6.5, 'DATOS PARA LA EMISIÓN DE FACTURA', 1, 1, 'C');

    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(42, 6.5, 'NOMBRE DE LA FACTURA:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($content_width * 0.58 - 42, 6.5, $nombreFactura, 1, 0, 'L');
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell(22, 6.5, 'NIT Ó C.I.:', 1, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->Cell($content_width - ($content_width * 0.58) - 22, 6.5, $nitCiFactura, 1, 1, 'L');

    // ========================================
    // DATOS PARA DEPÓSITO EN BANCO UNIÓN
    // ========================================
    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($content_width, 6.5, 'DATOS PARA DEPOSITO EN BANCO UNION', 1, 1, 'C');

    $W_BANCO_IZQ = $content_width * 0.55;
    $W_BANCO_DER = $content_width - $W_BANCO_IZQ;

    $pdf->SetX($x_left);
    $pdf->SetFillColor(253, 235, 208);
    $pdf->SetFont('helvetica', 'BI', 7);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($W_BANCO_IZQ, 5.5, 'DENOMINACION DE LA CUENTA', 1, 0, 'L', true);
    $pdf->Cell($W_BANCO_DER, 5.5, 'NUMERO DE CUENTA', 1, 1, 'L', true);

    $y_cuenta = $pdf->GetY();
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $pdf->SetXY($x_left, $y_cuenta);
    $pdf->MultiCell($W_BANCO_IZQ, 9.5, "UTO\nAPORTES EXTRAORDINARIOS", 1, 'L', true, 0, '', '', true, 0, false, true, 9.5, 'M');
    $pdf->SetXY($x_left + $W_BANCO_IZQ, $y_cuenta);
    $pdf->Cell($W_BANCO_DER, 9.5, $numeroCuentaDeposito, 1, 0, 'L', true);
    $pdf->Ln(9.5);

    $pdf->SetX($x_left);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    $pdf->Cell($content_width, 5.5, 'NIT: 120129022', 1, 1, 'L', true);

    // ========================================
    // FIRMA DEL COORDINADOR / RESPONSABLE
    // ========================================
    $pdf->Ln(2);
    $W_FIRMA = 75;
    $x_firma = $x_left + ($content_width - $W_FIRMA) / 2;
    $y_linea = $pdf->GetY() + 4;

    $pdf->SetLineWidth(0.3);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line($x_firma, $y_linea, $x_firma + $W_FIRMA, $y_linea);

    $pdf->SetXY($x_firma, $y_linea + 1);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    $textoFirma = !empty($firma) ? mb_strtoupper($firma, 'UTF-8') : 'FIRMA COORDINADOR(A) / RESPONSABLE';
    $pdf->Cell($W_FIRMA, 4, $textoFirma, 0, 1, 'C');

    return $pdf->GetY() - $yInicio;
}

/**
 * Arma el PDF completo (original + copia) y lo envía al navegador.
 * @param array $datos apaterno, amaterno, nombres, correo, ci, celular, programa,
 *        montoNumeral, montoLiteral, version, numeroTramite, nombreFactura,
 *        nitCiFactura, numeroCuentaDeposito, firma
 */
function generarPdfOrdenPagoOficial(array $datos)
{
    $colorEtiqueta = [214, 106, 20]; // naranja (etiquetas del formato oficial)
    $colorTexto = [30, 30, 30];

    $nombreCompleto = trim(($datos['apaterno'] ?? '') . ' ' . ($datos['amaterno'] ?? '') . ' ' . ($datos['nombres'] ?? ''));

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
}

/**
 * Convertir un monto numérico a su representación literal en bolivianos, para la
 * Orden de Pago (mismo formato/redacción que usa el administrador al generarla).
 */
function numeroALetrasOrdenPagoOficial($numero)
{
    $entero = (int)floor($numero);
    $decimales = (int)round(($numero - $entero) * 100);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    $convertirEntero = function ($num) use (&$convertirEntero, $unidades, $decenas, $especiales, $centenas) {
        if ($num == 0) return 'CERO';
        if ($num == 100) return 'CIEN';

        $resultado = '';

        if ($num >= 1000000) {
            $millones = (int)floor($num / 1000000);
            $resultado .= ($millones == 1) ? 'UN MILLON ' : $convertirEntero($millones) . ' MILLONES ';
            $num %= 1000000;
        }

        if ($num >= 1000) {
            $miles = (int)floor($num / 1000);
            $resultado .= ($miles == 1) ? 'MIL ' : $convertirEntero($miles) . ' MIL ';
            $num %= 1000;
        }

        if ($num >= 100) {
            $resultado .= $centenas[(int)floor($num / 100)] . ' ';
            $num %= 100;
        }

        if ($num >= 10 && $num < 20) {
            $resultado .= $especiales[$num - 10] . ' ';
        } elseif ($num >= 20) {
            $resultado .= $decenas[(int)floor($num / 10)];
            if ($num % 10 > 0) $resultado .= ' Y ' . $unidades[$num % 10];
            $resultado .= ' ';
        } elseif ($num > 0) {
            $resultado .= $unidades[$num] . ' ';
        }

        return trim($resultado);
    };

    $letras = $convertirEntero($entero);

    return $decimales > 0
        ? strtoupper($letras . ' CON ' . $decimales . '/100 BOLIVIANOS')
        : strtoupper($letras . ' 00/100 BOLIVIANOS');
}

/**
 * Etiqueta ordinal de una cuota del programa ("Primera cuota del programa"...),
 * igual que la que arma el administrador al generar la orden de la cuota.
 */
function etiquetaCuotaOrdenPago($numeroCuota)
{
    $ordinales = ['', 'Primera', 'Segunda', 'Tercera', 'Cuarta', 'Quinta',
        'Sexta', 'Séptima', 'Octava', 'Novena', 'Décima'];
    $n = (int)$numeroCuota ?: 1;

    return ($n > 0 && $n < count($ordinales))
        ? ($ordinales[$n] . ' cuota del programa')
        : ('Cuota ' . $n . ' del programa');
}
