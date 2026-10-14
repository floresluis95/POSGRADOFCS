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
function dibujarOrdenPago($pdf, $yInicio, $esCopia, $datos, $colorEtiqueta, $colorTexto, $colorTitulo, $colorTituloTexto)
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
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($page_width - $margin - $width_img - 27, $yInicio + 1);
        $pdf->Cell(25, 3, '** COPIA **', 0, 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY($y_actual);
    }

    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->Line($x_left, $pdf->GetY() + 0.8, $page_width - $margin, $pdf->GetY() + 0.8);
    $pdf->Ln(1.8);
    $pdf->SetLineWidth(0.2);

    // ========================================
    // TABLA: un solo marco exterior; las barras grises separan las secciones y
    // dentro solo hay líneas claras entre columnas. Cada etiqueta (pequeña, gris)
    // va pegada a su dato, sin línea entre ambos. Las posiciones y tamaños de
    // cada recuadro son los mismos de antes.
    // ========================================
    $separador = function ($x1, $y1, $x2, $y2) use ($pdf) {
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetLineWidth(0.2);
        $pdf->Line($x1, $y1, $x2, $y2);
    };
    $barra = function ($texto, $alto, $tamano) use ($pdf, $x_left, $content_width, $colorTitulo, $colorTituloTexto) {
        $pdf->SetX($x_left);
        $pdf->SetFont('helvetica', 'B', $tamano);
        $pdf->SetFillColor($colorTitulo[0], $colorTitulo[1], $colorTitulo[2]);
        $pdf->SetTextColor($colorTituloTexto[0], $colorTituloTexto[1], $colorTituloTexto[2]);
        $pdf->Cell($content_width, $alto, $texto, 0, 1, 'C', true);
    };
    $etiqueta = function ($tamano = 6.5) use ($pdf, $colorEtiqueta) {
        $pdf->SetFont('helvetica', 'B', $tamano);
        $pdf->SetTextColor($colorEtiqueta[0], $colorEtiqueta[1], $colorEtiqueta[2]);
    };
    $dato = function ($tamano, $estilo = 'B') use ($pdf, $colorTexto) {
        $pdf->SetFont('helvetica', $estilo, $tamano);
        $pdf->SetTextColor($colorTexto[0], $colorTexto[1], $colorTexto[2]);
    };

    $y_tabla = $pdf->GetY();

    // TÍTULO
    $barra('ORDEN DE PAGO EN CAJA DE POSGRADO', 7.5, 11);

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

    $etiqueta(7);
    $pdf->SetXY($x_left, $y_bloque);
    $pdf->MultiCell($W_ENCABEZADO, $H_TOTAL, 'NOMBRE COMPLETO DEL POSGRADUANTE', 0, 'L', false, 0, '', '', true, 0, false, true, $H_TOTAL, 'M');

    $x_tabla = $x_left + $W_ENCABEZADO;

    // Nombre: lo más visible del bloque
    $etiqueta();
    $pdf->SetXY($x_tabla, $y_bloque);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO PATERNO', 0, 0, 'L', false, '', 0, false, 'T', 'B');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'APELLIDO MATERNO', 0, 0, 'L', false, '', 0, false, 'T', 'B');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'NOMBRE(S)', 0, 1, 'L', false, '', 0, false, 'T', 'B');

    $pdf->SetX($x_tabla);
    $dato(10.5);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $apaterno, 0, 0, 'L', false, '', 1);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $amaterno, 0, 0, 'L', false, '', 1);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $nombres, 0, 1, 'L', false, '', 1);

    $etiqueta();
    $pdf->SetX($x_tabla);
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'CORREO ELECTRÓNICO', 0, 0, 'L', false, '', 0, false, 'T', 'B');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'C.I.', 0, 0, 'L', false, '', 0, false, 'T', 'B');
    $pdf->Cell($W_CELDA, $H_FILA_TITULO, 'N° DE CELULAR', 0, 1, 'L', false, '', 0, false, 'T', 'B');

    $pdf->SetX($x_tabla);
    $dato(8.5, '');
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $correo, 0, 0, 'L', false, '', 1);
    $dato(9);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $ci, 0, 0, 'L', false, '', 1);
    $pdf->Cell($W_CELDA, $H_FILA_VALOR, $celular, 0, 1, 'L', false, '', 1);

    $separador($x_tabla, $y_bloque + 1, $x_tabla, $y_bloque + $H_TOTAL - 1);
    $separador($x_tabla + $W_CELDA, $y_bloque + 1, $x_tabla + $W_CELDA, $y_bloque + $H_TOTAL - 1);
    $separador($x_tabla + 2 * $W_CELDA, $y_bloque + 1, $x_tabla + 2 * $W_CELDA, $y_bloque + $H_TOTAL - 1);
    $separador($x_tabla, $y_bloque + $H_FILA_TITULO + $H_FILA_VALOR, $x_left + $content_width, $y_bloque + $H_FILA_TITULO + $H_FILA_VALOR);

    $pdf->SetY($y_bloque + $H_TOTAL);

    // ========================================
    // DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO
    // ========================================
    $barra('DATOS PARA LA EMISIÓN DE COMPROBANTE DE PAGO', 6.5, 10);

    $W_PROGRAMA = $content_width * 0.62;
    $W_DERECHA = $content_width - $W_PROGRAMA;
    $H_ROW_A = 5;
    $H_ROW_B = 8.5;
    $H_PROGRAMA = $H_ROW_A + $H_ROW_B;

    $y_prog = $pdf->GetY();

    // Tipo de pago (MATRÍCULA, CUOTA o PROGRAMA COMPLETO): se detecta dentro del
    // texto ya armado de $programa. Como ya se muestra en TIPO DE PAGO, se quita
    // del nombre del programa para no repetirlo.
    $tipoPago = '';
    if (preg_match('/\s+PROGRAMA COMPLETO\s*$/iu', $programa, $m)) {
        $tipoPago = 'PROG. COMPLETO';
        $programa = mb_substr($programa, 0, -mb_strlen($m[0]));
    } elseif (preg_match('/\s+MATR[IÍ]CULA\s*$/iu', $programa, $m)) {
        $tipoPago = 'MATRÍCULA';
        $programa = mb_substr($programa, 0, -mb_strlen($m[0]));
    } elseif (preg_match('/\n\s*(?:(\S+) cuota|Cuota (\d+)) del programa(?:\s*\((\d+\/\d+)\))?\s*$/iu', $programa, $m)) {
        // "Primera cuota del programa (1/12)" → PRIMERA CUOTA 1/12;
        // "Cuota 12 del programa (12/12)" → CUOTA 12/12; "Cuota 12 del programa" → CUOTA 12
        $tipoPago = !empty($m[1])
            ? mb_strtoupper($m[1], 'UTF-8') . ' CUOTA' . (!empty($m[3]) ? ' ' . $m[3] : '')
            : 'CUOTA ' . (!empty($m[3]) ? $m[3] : $m[2]);
        $programa = mb_substr($programa, 0, -mb_strlen($m[0]));
    } elseif (stripos($programa, 'MATRICULA') !== false || stripos($programa, 'MATRÍCULA') !== false) {
        $tipoPago = 'MATRÍCULA';
    } elseif (stripos($programa, 'cuota') !== false) {
        $tipoPago = 'CUOTA';
    }

    // Izquierda: programa (etiqueta arriba, nombre destacado abajo)
    $etiqueta();
    $pdf->SetXY($x_left, $y_prog);
    $pdf->Cell($W_PROGRAMA, $H_ROW_A, 'PROGRAMA', 0, 0, 'L');

    // Derecha, fila A: tipo de pago
    $pdf->Cell($W_DERECHA * 0.42, $H_ROW_A, 'TIPO DE PAGO', 0, 0, 'L');
    $dato(9);
    $pdf->Cell($W_DERECHA * 0.58, $H_ROW_A, $tipoPago, 0, 1, 'L', false, '', 1);

    $pdf->SetXY($x_left, $y_prog + $H_ROW_A);
    $dato(9);
    $pdf->MultiCell($W_PROGRAMA, $H_ROW_B, $programa, 0, 'L', false, 0, '', '', true, 0, false, true, $H_ROW_B, 'T', true);

    // Derecha, fila B: cuenta auxiliar destacada
    $pdf->SetXY($x_left + $W_PROGRAMA, $y_prog + $H_ROW_A);
    $etiqueta();
    $pdf->Cell($W_DERECHA * 0.42, $H_ROW_B, 'CUENTA AUXILIAR', 0, 0, 'L');
    $dato(13);
    $pdf->Cell($W_DERECHA * 0.58, $H_ROW_B, $numeroTramite, 0, 1, 'L');

    $separador($x_left + $W_PROGRAMA, $y_prog + 1, $x_left + $W_PROGRAMA, $y_prog + $H_PROGRAMA - 1);
    $separador($x_left + $W_PROGRAMA, $y_prog + $H_ROW_A, $x_left + $content_width, $y_prog + $H_ROW_A);
    $separador($x_left, $y_prog + $H_PROGRAMA, $x_left + $content_width, $y_prog + $H_PROGRAMA);

    $pdf->SetY($y_prog + $H_PROGRAMA);

    // Fila MONTO / LITERAL
    $y_monto = $pdf->GetY();
    $pdf->SetX($x_left);
    $etiqueta();
    $pdf->Cell(20, 6.5, 'MONTO', 0, 0, 'L');
    $dato(10.5);
    $pdf->Cell(32, 6.5, $montoNumeral, 0, 0, 'L');
    $etiqueta();
    $pdf->Cell(20, 6.5, 'LITERAL', 0, 0, 'L');
    $dato(8);
    $pdf->Cell($content_width - 20 - 32 - 20, 6.5, $montoLiteral, 0, 1, 'L', false, '', 1);
    $separador($x_left + 52, $y_monto + 1, $x_left + 52, $y_monto + 5.5);

    // ========================================
    // DATOS PARA LA EMISIÓN DE FACTURA
    // ========================================
    $barra('DATOS PARA LA EMISIÓN DE FACTURA', 6.5, 10);

    $y_factura = $pdf->GetY();
    $pdf->SetX($x_left);
    $etiqueta();
    $pdf->Cell(42, 6.5, 'NOMBRE DE LA FACTURA', 0, 0, 'L');
    $dato(9);
    $pdf->Cell($content_width * 0.58 - 42, 6.5, $nombreFactura, 0, 0, 'L', false, '', 1);
    $etiqueta();
    $pdf->Cell(22, 6.5, 'NIT O C.I.', 0, 0, 'L');
    $dato(9);
    $pdf->Cell($content_width - ($content_width * 0.58) - 22, 6.5, $nitCiFactura, 0, 1, 'L', false, '', 1);
    $separador($x_left + $content_width * 0.58, $y_factura + 1, $x_left + $content_width * 0.58, $y_factura + 5.5);

    // ========================================
    // DATOS PARA DEPÓSITO EN BANCO UNIÓN
    // ========================================
    $barra('DATOS PARA DEPÓSITO EN BANCO UNIÓN', 6.5, 10);

    $W_BANCO_IZQ = $content_width * 0.55;
    $W_BANCO_DER = $content_width - $W_BANCO_IZQ;

    $y_banco = $pdf->GetY();
    $pdf->SetX($x_left);
    $etiqueta();
    $pdf->Cell($W_BANCO_IZQ, 5.5, 'DENOMINACIÓN DE LA CUENTA', 0, 0, 'L', false, '', 0, false, 'T', 'B');
    $pdf->Cell($W_BANCO_DER, 5.5, 'NÚMERO DE CUENTA', 0, 1, 'L', false, '', 0, false, 'T', 'B');

    $y_cuenta = $pdf->GetY();
    $dato(9);
    $pdf->SetXY($x_left, $y_cuenta);
    $pdf->MultiCell($W_BANCO_IZQ, 9.5, "UTO\nAPORTES EXTRAORDINARIOS", 0, 'L', false, 0, '', '', true, 0, false, true, 9.5, 'M');
    $pdf->SetXY($x_left + $W_BANCO_IZQ, $y_cuenta);
    $dato(12);
    $pdf->Cell($W_BANCO_DER, 9.5, $numeroCuentaDeposito, 0, 0, 'L');
    $pdf->Ln(9.5);

    $separador($x_left + $W_BANCO_IZQ, $y_banco + 1, $x_left + $W_BANCO_IZQ, $y_cuenta + 9.5 - 1);
    $separador($x_left, $pdf->GetY(), $x_left + $content_width, $pdf->GetY());

    $pdf->SetX($x_left);
    $etiqueta();
    $pdf->Cell(10, 5.5, 'NIT', 0, 0, 'L');
    $dato(8.5);
    $pdf->Cell($content_width - 10, 5.5, '120129022', 0, 1, 'L');

    // Marco exterior de toda la tabla
    $pdf->SetDrawColor(110, 110, 110);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect($x_left, $y_tabla, $content_width, $pdf->GetY() - $y_tabla);
    $pdf->SetDrawColor(0, 0, 0);

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

    // Código de la orden (generado por el sistema): solo en la copia, pequeño y gris
    // en la esquina inferior derecha, para control interno.
    if ($esCopia && !empty($numeroOrden)) {
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY($x_left, $y_linea + 1.5);
        $pdf->Cell($content_width, 3, 'Ctrl. ' . $numeroOrden, 0, 0, 'R');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY($y_linea + 5);
    }

    return $pdf->GetY() - $yInicio;
}

/**
 * Arma el PDF completo (original + copia) y lo envía al navegador.
 * @param array $datos apaterno, amaterno, nombres, correo, ci, celular, programa,
 *        montoNumeral, montoLiteral, version, numeroTramite, nombreFactura,
 *        nitCiFactura, numeroCuentaDeposito, firma, numeroOrden (opcional; va al pie de la copia)
 */
function generarPdfOrdenPagoOficial(array $datos)
{
    // Paleta para imprimir con poca tinta: barras de sección en gris muy claro con
    // texto negro, etiquetas en gris oscuro y datos en negro (antes las barras eran
    // azul relleno). La distribución del documento no cambia.
    $colorTitulo = [235, 235, 235];   // gris muy claro (fondo de las barras de sección)
    $colorTituloTexto = [0, 0, 0];
    $colorEtiqueta = [80, 80, 80];    // gris oscuro (etiquetas de cada campo)
    $colorTexto = [0, 0, 0];

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
    $alturaBloque = dibujarOrdenPago($pdf, 8, false, $datos, $colorEtiqueta, $colorTexto, $colorTitulo, $colorTituloTexto);

    // Línea divisoria punteada entre original y copia
    $y_divisoria = 8 + $alturaBloque + 6;
    $pdf->SetLineStyle(['width' => 0.2, 'dash' => 2, 'color' => [150, 150, 150]]);
    $pdf->Line(10, $y_divisoria, $pdf->GetPageWidth() - 10, $y_divisoria);
    $pdf->SetLineStyle(['dash' => 0]);

    // COPIA (mitad inferior de la hoja)
    dibujarOrdenPago($pdf, $y_divisoria + 6, true, $datos, $colorEtiqueta, $colorTexto, $colorTitulo, $colorTituloTexto);

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
            // Delante de "MIL" el "UNO" se apocopa: "TREINTA Y UN MIL", "VEINTIUN MIL"
            $resultado .= ($miles == 1) ? 'MIL ' : preg_replace('/UNO$/', 'UN', $convertirEntero($miles)) . ' MIL ';
            $num %= 1000;
        }

        if ($num >= 100) {
            // Un cien exacto tras los miles: "DOS MIL CIEN", no "DOS MIL CIENTO"
            $resultado .= ($num == 100 ? 'CIEN' : $centenas[(int)floor($num / 100)]) . ' ';
            $num %= 100;
        }

        if ($num >= 10 && $num < 20) {
            $resultado .= $especiales[$num - 10] . ' ';
        } elseif ($num > 20 && $num < 30) {
            // 21-29 se escriben en una palabra: VEINTIUNO, VEINTIDOS...
            $resultado .= 'VEINTI' . $unidades[$num - 20] . ' ';
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
 * Etiqueta ordinal de una cuota del programa ("Primera cuota del programa (1/12)"...),
 * igual que la que arma el administrador al generar la orden de la cuota. El "(1/12)"
 * solo va si el plan tiene definido su número de cuotas.
 */
function etiquetaCuotaOrdenPago($numeroCuota, $totalCuotas = null)
{
    $ordinales = ['', 'Primera', 'Segunda', 'Tercera', 'Cuarta', 'Quinta',
        'Sexta', 'Séptima', 'Octava', 'Novena', 'Décima'];
    $n = (int)$numeroCuota ?: 1;
    $total = (int)$totalCuotas;

    return (($n > 0 && $n < count($ordinales))
            ? ($ordinales[$n] . ' cuota del programa')
            : ('Cuota ' . $n . ' del programa'))
        . ($total > 0 ? ' (' . $n . '/' . $total . ')' : '');
}
