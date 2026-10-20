<?php
/**
 * Formatos COMPLETOS de los certificados (membrete, etiquetas, cuadrícula, firmas,
 * pie...) tal como vienen pre-impresos de Posgrado Central. Solo para la demostración
 * "Documento completo": los certificados reales se imprimen sobre el papel pre-impreso.
 *
 * Las medidas salen de los escaneos de cada formato y se ubican respecto a las mismas
 * coordenadas de los datos, para que formato y contenido calcen entre sí.
 * Historial y Aprobación van en su hoja oficio boliviano real (la de los escaneos).
 */

const FORMATO_AZUL = [33, 45, 125];
const FORMATO_ROJO = [200, 32, 45];

/** Texto en Helvetica con el ancho exacto del original (se escala la letra). */
function formatoTextoAncho($pdf, $x, $yLinea, $ancho, $texto, $estilo, $alin = 'L')
{
    $pdf->SetFont('helvetica', $estilo, 10);
    $tamano = 10 * $ancho / $pdf->GetStringWidth($texto);
    $pdf->SetFont('helvetica', $estilo, $tamano);
    $alto = $tamano * 0.3528 * 1.3;
    $descenso = $pdf->getFontDescent('helvetica', $estilo, $tamano);
    $pdf->SetXY($x, $yLinea + $descenso - $alto);
    $pdf->Cell($ancho, $alto, $texto, 0, 0, $alin, false, '', 0, false, 'T', 'B');
}

/** Texto en Helvetica de un tamaño dado, apoyado en $yLinea. */
function formatoTexto($pdf, $x, $yLinea, $ancho, $texto, $estilo, $tamano, $alin = 'L')
{
    $pdf->SetFont('helvetica', $estilo, $tamano);
    $alto = $tamano * 0.3528 * 1.3;
    $descenso = $pdf->getFontDescent('helvetica', $estilo, $tamano);
    $pdf->SetXY($x, $yLinea + $descenso - $alto);
    $pdf->Cell($ancho, $alto, $texto, 0, 0, $alin, false, '', 0, false, 'T', 'B');
}

/** Línea de puntos donde se escribe un dato. */
function formatoPuntos($pdf, $x1, $x2, $y)
{
    $pdf->SetLineStyle(['width' => 0.3, 'cap' => 'butt', 'dash' => '0.35,0.6', 'color' => FORMATO_AZUL]);
    $pdf->Line($x1, $y, $x2, $y);
    $pdf->SetLineStyle(['width' => 0.3, 'cap' => 'butt', 'dash' => 0, 'color' => FORMATO_AZUL]);
}

function dibujarFormatoCalificaciones($pdf, array $c, $correlativo, $notaMinima)
{
    $escudo = __DIR__ . '/../../extensiones/imagenespdf/logouto.png';
    $pdf->SetTextColorArray(FORMATO_AZUL);

    // Fondo de seguridad: "UNIVERSIDAD TÉCNICA DE ORURO" repetido en celeste muy claro
    $pdf->SetFont('helvetica', '', 5.6);
    $pdf->SetTextColor(228, 234, 247);
    $frase = str_repeat('UNIVERSIDAD TÉCNICA DE ORURO ', 14);
    for ($fila = 0, $y = 9.5; $y < 279; $fila++, $y += 2.45) {
        // cada renglón empieza en otra letra, como en el papel de seguridad
        $pdf->Text(0, $y, mb_substr($frase, ($fila * 11) % 29));
    }

    // Escudo de marca de agua al centro, muy tenue
    $pdf->SetAlpha(0.10);
    $pdf->Image($escudo, 72, 112, 72, 0, 'PNG');
    $pdf->SetAlpha(1);

    // Membrete
    $pdf->Image($escudo, 10.6, 21, 30, 0, 'PNG');
    $pdf->SetTextColorArray(FORMATO_AZUL);
    formatoTextoAncho($pdf, 59.4, 25.7, 96.4, 'UNIVERSIDAD TÉCNICA DE ORURO', 'B', 'C');
    formatoTextoAncho($pdf, 72.6, 31.6, 70, 'DIRECCIÓN DE POSGRADO', 'B', 'C');
    $pdf->SetTextColorArray(FORMATO_ROJO);
    $pdf->setFontStretching(72);
    formatoTextoAncho($pdf, 53.5, 49.6, 109.3, 'CERTIFICADO DE CALIFICACIONES', 'B', 'C');
    $pdf->setFontStretching(100);
    $pdf->SetTextColor(25, 25, 25);
    $pdf->SetFont('times', '', 15);
    $pdf->Text(175.5, 37.8, 'N° ' . sprintf('%05d', $correlativo));

    // Etiquetas y líneas de puntos de los datos
    $pdf->SetTextColorArray(FORMATO_AZUL);
    $ajuste = 0.5; // la etiqueta y los puntos van apenas bajo el renglón del dato
    formatoTextoAncho($pdf, 10.6, $c['Y_NOMBRE'] + $ajuste, 44.2, 'NOMBRES Y APELLIDOS:', '');
    formatoPuntos($pdf, 55.4, 160.8, $c['Y_NOMBRE'] + $ajuste);
    formatoTextoAncho($pdf, 162.1, $c['Y_VAUCHER'] + $ajuste, 4.8, 'N°', '');
    formatoPuntos($pdf, 167.5, 205.8, $c['Y_VAUCHER'] + $ajuste);
    formatoTextoAncho($pdf, 10.6, $c['Y_PROGRAMA'] + $ajuste, 22.5, 'PROGRAMA:', '');
    formatoPuntos($pdf, 33.6, 205.8, $c['Y_PROGRAMA'] + $ajuste);
    formatoTextoAncho($pdf, 10.6, $c['Y_NIVEL'] + $ajuste, 11.8, 'NIVEL:', '');
    formatoPuntos($pdf, 22.9, 160.8, $c['Y_NIVEL'] + $ajuste);
    formatoTextoAncho($pdf, 162.3, $c['Y_ANIO'] + $ajuste, 8.8, 'AÑO:', '');
    formatoPuntos($pdf, 171.5, 205.8, $c['Y_ANIO'] + $ajuste);

    // Tabla: dos filas de encabezado sombreadas y el cuerpo hasta el último renglón
    $x0 = $c['X_SIGLA'];
    $x1 = $c['X_NOMBREMOD'];
    $x2 = $c['X_NUM'];
    $x3 = $c['X_LITERAL'];
    $x4 = $c['X_LITERAL'] + $c['W_LITERAL'];
    $yCab1 = $c['Y_TABLA'] - 16.9;
    $yCab2 = $yCab1 + 7.1;
    $yCuerpo = $yCab2 + 7.2;
    $yFin = $c['Y_TABLA'] + 6 * $c['ALTO_FILA'];

    $pdf->SetFillColor(233, 238, 250);
    $pdf->Rect($x0, $yCab1, $x4 - $x0, $yCuerpo - $yCab1, 'F');
    $pdf->SetLineStyle(['width' => 0.3, 'cap' => 'butt', 'dash' => 0, 'color' => FORMATO_AZUL]);
    $pdf->Rect($x0, $yCab1, $x4 - $x0, $yFin - $yCab1);
    $pdf->Line($x0, $yCab2, $x4, $yCab2);
    $pdf->Line($x0, $yCuerpo, $x4, $yCuerpo);
    $pdf->Line($x2, $yCab1, $x2, $yFin);
    $pdf->Line($x1, $yCab2, $x1, $yFin);
    $pdf->Line($x3, $yCab2, $x3, $yFin);

    formatoTexto($pdf, $x0, $yCab2 - 1.6, $x2 - $x0, 'ASIGNATURAS', '', 11.5, 'C');
    formatoTexto($pdf, $x2, $yCab2 - 1.6, $x4 - $x2, 'CALIFICACIONES', '', 11.5, 'C');
    formatoTexto($pdf, $x0, $yCuerpo - 1.6, $x1 - $x0, 'SIGLA', '', 11.5, 'C');
    formatoTexto($pdf, $x1, $yCuerpo - 1.6, $x2 - $x1, 'NOMBRE', '', 11.5, 'C');
    formatoTexto($pdf, $x2, $yCuerpo - 1.6, $x3 - $x2, 'NUM.', '', 11.5, 'C');
    formatoTexto($pdf, $x3, $yCuerpo - 1.6, $x4 - $x3, 'LITERAL', '', 11.5, 'C');

    // Nota de créditos y escala (la nota mínima es la configurada en el sistema)
    formatoTexto($pdf, $x0 + 0.4, $yFin + 2.6, 80, '· UN CRÉDITO EQUIVALE A 40 HORAS', '', 6.8);
    formatoTextoAncho($pdf, 27.6, $yFin + 11.6, 41.5, 'ESCALA DE CALIFICACIONES', '');
    $pdf->Rect(73.5, $yFin + 4.6, 39.1, 10.4);
    formatoTexto($pdf, 75.4, $yFin + 8.9, 14, '0 - ' . ($notaMinima - 1), '', 9.5);
    formatoTextoAncho($pdf, 91.1, $yFin + 8.9, 19.3, 'REPROBADO', 'B');
    formatoTexto($pdf, 75.4, $yFin + 13.2, 14, $notaMinima . ' - 100', '', 9.5);
    formatoTextoAncho($pdf, 91.1, $yFin + 13.2, 17.6, 'APROBADO', 'B');

    // Lugar y fecha
    $yFecha = $c['Y_FECHA'] + 0.4;
    formatoTexto($pdf, 125.6, $yFecha, 12, 'Oruro,', '', 11);
    formatoTexto($pdf, 147.9, $yFecha, 5, 'de', '', 11);
    formatoTexto($pdf, 188.8, $yFecha, 5, 'de', '', 11);
    formatoPuntos($pdf, $c['X_DIA'], $c['X_DIA'] + $c['ANCHO_DIA'], $yFecha + 0.3);
    formatoPuntos($pdf, $c['X_MES'], $c['X_MES'] + $c['ANCHO_MES'], $yFecha + 0.3);
    formatoPuntos($pdf, $c['X_ANIOFECHA'], $c['X_ANIOFECHA'] + $c['ANCHO_ANIOFECHA'], $yFecha + 0.3);

    // Firmas
    $firmas = [
        [25.8, 220.7, 'SECRETARIO(A) DP'], [131.3, 220.7, 'COORDINADOR(A) PROGRAMA'],
        [25.8, 244.2, 'DIRECTOR(A) DP'], [131.3, 244.2, 'VICERRECTOR(A)'],
    ];
    foreach ($firmas as [$x, $y, $cargo]) {
        formatoPuntos($pdf, $x, $x + 60.4, $y);
        formatoTexto($pdf, $x, $y + 3.6, 60.4, $cargo, '', 11, 'C');
    }

    // Pie
    formatoTexto($pdf, 11.3, 267.1, 150, 'Documento académico válido para trámites universitarios internos en la Universidad Técnica de Oruro', '', 6.8);
    formatoTexto($pdf, 11.3, 269.7, 20, 'ADVERTENCIA:', 'B', 6.8);
    formatoTexto($pdf, 11.3 + $pdf->GetStringWidth('ADVERTENCIA: ', 'helvetica', 'B', 6.8), 269.7, 130, 'Este documento queda nulo, si se advierten raspaduras, anotaciones o enmiendas.', '', 6.8);

    $pdf->SetTextColor(0, 0, 0);
}

/** Logo "Posgrado": el escudo dentro de un anillo rojo y azul, con la palabra debajo. */
function formatoLogoPosgrado($pdf, $cx, $cy, $r)
{
    $escudo = __DIR__ . '/../../extensiones/imagenespdf/logouto.png';
    $pdf->Circle($cx, $cy, $r, 35, 215, 'D', ['width' => 0.9, 'dash' => 0, 'color' => FORMATO_ROJO]);
    $pdf->Circle($cx, $cy, $r * 0.84, 0, 360, 'D', ['width' => 0.6, 'dash' => 0, 'color' => FORMATO_AZUL]);
    $pdf->Image($escudo, $cx - $r * 0.66, $cy - $r * 0.66, $r * 1.32, 0, 'PNG');
    $pdf->SetFont('helvetica', 'I', $r * 1.2);
    $x = $cx + $r * 0.15 - $pdf->GetStringWidth('Posgrado') / 2; // centrada bajo el anillo
    $y = $cy + $r * 0.72;
    $pdf->SetTextColorArray(FORMATO_ROJO);
    $pdf->Text($x, $y, 'Pos');
    $pdf->SetTextColorArray(FORMATO_AZUL);
    $pdf->Text($x + $pdf->GetStringWidth('Pos'), $y, 'grado');
}

/** Etiqueta y, a continuación, la línea de puntos hasta $xFin (o desde $xPuntos). */
function formatoEtiquetaPuntos($pdf, $x, $yLinea, $texto, $tamano, $xFin, $xPuntos = null)
{
    $pdf->SetTextColorArray(FORMATO_AZUL);
    formatoTexto($pdf, $x, $yLinea, 100, $texto, '', $tamano);
    $inicio = $xPuntos ?? $x + $pdf->GetStringWidth($texto, 'helvetica', '', $tamano) + 0.6;
    formatoPuntos($pdf, $inicio, $xFin, $yLinea + 0.3);
}

/**
 * Historial Académico: hoja oficio horizontal (330,2 x 215,9 mm), dos bloques de
 * 6 módulos. $campos, $bloques y el cuerpo de la tabla son los mismos del PDF.
 */
function dibujarFormatoHistorial($pdf, array $campos, array $bloques, $yCuerpoIni, $yCuerpoFin, $correlativo)
{
    $escudo = __DIR__ . '/../../extensiones/imagenespdf/logouto.png';

    // Membrete
    $pdf->Image($escudo, 15.6, 10.7, 26.7, 0, 'PNG');
    formatoLogoPosgrado($pdf, 303.7, 24.0, 13.0);
    $pdf->SetTextColorArray(FORMATO_AZUL);
    formatoTextoAncho($pdf, 117.9, 17.8, 96.0, 'UNIVERSIDAD TÉCNICA DE ORURO', 'B', 'C');
    formatoTextoAncho($pdf, 131.2, 24.5, 69.4, 'DIRECCIÓN DE POSGRADO', 'B', 'C');
    $pdf->SetTextColorArray(FORMATO_ROJO);
    $pdf->SetFont('times', 'B', 10);
    $pdf->SetFont('times', 'B', 10 * 110.2 / $pdf->GetStringWidth('HISTORIAL ACADÉMICO'));
    $pdf->Text(110.8, 31.0, 'HISTORIAL ACADÉMICO');
    $pdf->SetTextColor(25, 25, 25);
    $pdf->SetFont('times', '', 15);
    $pdf->Text(239.7, 25.0, 'N° ' . sprintf('%05d', $correlativo));

    // Etiquetas y líneas de puntos (sobre el mismo renglón que los datos)
    $ajuste = 0.9;
    formatoEtiquetaPuntos($pdf, 15.6, $campos['nombre'][2] + $ajuste, 'NOMBRES Y APELLIDOS:', 10, $campos['nombre'][1]);
    formatoEtiquetaPuntos($pdf, $campos['vaucher'][0] - 4.2, $campos['vaucher'][2] + $ajuste, 'N°', 10, $campos['vaucher'][1], $campos['vaucher'][0]);
    formatoEtiquetaPuntos($pdf, 15.6, $campos['programa'][2] + $ajuste, 'PROGRAMA:', 10, $campos['programa'][1]);
    formatoEtiquetaPuntos($pdf, 15.6, $campos['nivel'][2] + $ajuste, 'NIVEL:', 10, $campos['nivel'][1]);
    formatoEtiquetaPuntos($pdf, $campos['fecha'][0] - 12.4, $campos['fecha'][2] + $ajuste, 'FECHA:', 10, $campos['fecha'][1], $campos['fecha'][0]);

    // Tabla: dos bloques iguales (Módulo | Asignatura-Nombre | Calif. | Fecha de Aprob.)
    $yTabla = 64.4;
    $ySub = 70.6;
    $pdf->SetLineStyle(['width' => 0.3, 'cap' => 'butt', 'dash' => 0, 'color' => FORMATO_AZUL]);
    $pdf->SetTextColorArray(FORMATO_AZUL);
    foreach ($bloques as $b) {
        $xa = $b['sigla'][0];
        $xb = $b['fecha'][1];
        $pdf->Rect($xa, $yTabla, $xb - $xa, $yCuerpoFin - $yTabla);
        $pdf->Line($xa, $yCuerpoIni, $xb, $yCuerpoIni);
        $pdf->Line($b['nombre'][0], $ySub, $b['nombre'][1], $ySub);
        foreach (['nombre', 'nota', 'fecha'] as $col) {
            $pdf->Line($b[$col][0], $yTabla, $b[$col][0], $yCuerpoFin);
        }
        $centro = ($yTabla + $yCuerpoIni) / 2 + 1.3;
        formatoTexto($pdf, $b['sigla'][0], $centro, $b['sigla'][1] - $b['sigla'][0], 'Módulo', '', 9.5, 'C');
        formatoTexto($pdf, $b['nombre'][0], $ySub - 1.5, $b['nombre'][1] - $b['nombre'][0], 'ASIGNATURA', '', 9, 'C');
        formatoTexto($pdf, $b['nombre'][0], $yCuerpoIni - 1.6, $b['nombre'][1] - $b['nombre'][0], 'Nombre', '', 9.5, 'C');
        formatoTexto($pdf, $b['nota'][0], $centro, $b['nota'][1] - $b['nota'][0], 'Calif.', '', 9.5, 'C');
        formatoTexto($pdf, $b['fecha'][0], $centro - 2, $b['fecha'][1] - $b['fecha'][0], 'Fecha', '', 9, 'C');
        formatoTexto($pdf, $b['fecha'][0], $centro + 2, $b['fecha'][1] - $b['fecha'][0], 'de Aprob.', '', 9, 'C');
    }

    // Firmas
    $firmas = [[15.6, 92.1, 'COORDINADOR(A) PROGRAMA'], [132.1, 195.2, 'SECRETARIO(A) DP'], [246.8, 316.1, 'DIRECTOR(A) DP']];
    foreach ($firmas as [$xa, $xb, $cargo]) {
        formatoPuntos($pdf, $xa, $xb, 192.9);
        formatoTexto($pdf, $xa, 197.0, $xb - $xa, $cargo, '', 10, 'C');
    }

    // Pie
    formatoTexto($pdf, 15.6, 203.6, 200, 'Documento académico válido para trámites universitarios internos en la Universidad Técnica de Oruro', '', 5.8);
    formatoTexto($pdf, 15.6, 205.9, 20, 'ADVERTENCIA:', 'B', 5.8);
    formatoTexto($pdf, 15.6 + $pdf->GetStringWidth('ADVERTENCIA: ', 'helvetica', 'B', 5.8), 205.9, 150,
        'Este documento queda nulo, si se advierten raspaduras, anotaciones o enmiendas.', '', 5.8);

    $pdf->SetTextColor(0, 0, 0);
}

/**
 * Certificado de Aprobación Programa de Posgrado: hoja oficio vertical con ocho
 * recuadros. Los datos van en las posiciones del PDF (medidas en el Word de Posgrado).
 */
function dibujarFormatoAprobacion($pdf, $correlativo)
{
    $escudo = __DIR__ . '/../../extensiones/imagenespdf/logouto.png';
    $x0 = 13.7;
    $x1 = 204.5;
    $xTexto = 16.9;

    // Escudo de marca de agua, muy tenue
    $pdf->SetAlpha(0.06);
    $pdf->Image($escudo, 54, 125, 110, 0, 'PNG');
    $pdf->SetAlpha(1);

    // Recuadros
    $pdf->SetLineStyle(['width' => 0.35, 'cap' => 'butt', 'dash' => 0, 'color' => FORMATO_AZUL]);
    $recuadros = [[11.7, 46.6], [51.7, 81.3], [86.1, 115.7], [120.9, 150.6], [155.8, 190.4], [195.6, 230.1], [235.0, 270.3], [275.2, 309.9]];
    foreach ($recuadros as [$ya, $yb]) {
        $pdf->Rect($x0, $ya, $x1 - $x0, $yb - $ya);
    }

    // Membrete
    $pdf->Image($escudo, 16.4, 18.9, 25.8, 0, 'PNG');
    formatoLogoPosgrado($pdf, 188.8, 31.0, 11.5);
    $pdf->SetTextColorArray(FORMATO_ROJO);
    $pdf->SetFont('times', '', 13);
    $pdf->Text(178.7, 14.0, 'N° ' . sprintf('%04d', $correlativo));
    $pdf->SetTextColorArray(FORMATO_AZUL);
    formatoTextoAncho($pdf, 73.3, 22.8, 71.6, 'UNIVERSIDAD TÉCNICA DE ORURO', '', 'C');
    formatoTextoAncho($pdf, 76.1, 27.4, 66.1, 'DIRECCIÓN DE POSGRADO UTO', '', 'C');
    formatoTextoAncho($pdf, 91.3, 32.0, 35.7, 'ORURO - BOLIVIA', '', 'C');
    formatoTextoAncho($pdf, 48.0, 39.5, 122.2, 'CERTIFICADO DE APROBACIÓN PROGRAMA POSGRADO', 'B', 'C');
    formatoTextoAncho($pdf, 75.9, 44.4, 66.3, '(Monografía - Tesis de Posgrado)', 'B', 'C');

    // Rótulos de cada recuadro
    $rotulos = [
        [$xTexto, 68.5, 'Programa de Posgrado:'],
        [$xTexto, 77.5, 'Grado académico:'],
        [$xTexto, 92.1, 'Título del Trabajo de Grado:'],
        [$xTexto, 126.8, 'Miembros del Tribunal y/o normativa universitaria vinculada:'],
        [$xTexto, 161.4, 'Fecha y Hora de la sustentación oral:'],
        [109.3, 161.4, 'Calificación Numeral:'],
        [$xTexto, 175.3, 'Entrega Informe final monografía:'],
        [109.3, 175.3, 'Calificación Literal:'],
        [$xTexto, 240.4, 'El Director de la Dirección de Posgrado, expresa su total conformidad con la presente certificación.'],
        [$xTexto, 280.9, 'Téngase como documento oficial. Entréguese al interesado.'],
        [$xTexto, 307.1, 'N° de recibo de caja:'],
    ];
    foreach ($rotulos as [$x, $y, $texto]) {
        formatoTexto($pdf, $x, $y, 190, $texto, '', 11);
    }

    // Párrafo del acta, justificado (el salto final evita estirar la última línea)
    $pdf->SetFont('helvetica', '', 11);
    $pdf->setFontStretching(92); // en el original entra en dos líneas
    $pdf->setCellHeightRatio(1.05);
    $pdf->MultiCell($x1 - $xTexto - 3, 4.6,
        'La información para la presente CERTIFICACIÓN ha sido extraída del ACTA (original) que cursa en los archivos de la ' .
        'Dirección de Posgrado (o Dirección Facultativa), documento al que en caso necesario me remito.' . "\n",
        0, 'J', false, 1, $xTexto, 197.6);
    $pdf->setCellHeightRatio(1.25);
    $pdf->setFontStretching(100);

    // Firmas
    $firmas = [[64.9, 227.0, 'COORDINADOR DE POSGRADO'], [156.1, 227.0, 'SECRETARIO(A)'],
        [153.3, 267.2, 'DIRECTOR DE DP-UTO'], [153.8, 307.1, 'RECTOR DE LA UNIVERSIDAD']];
    foreach ($firmas as [$cx, $y, $cargo]) {
        formatoTexto($pdf, $cx - 40, $y, 80, $cargo, '', 11, 'C');
    }

    // Pie
    formatoTexto($pdf, $xTexto, 315.2, 190, 'Documento académico válido para trámites universitarios internos en la Universidad Técnica de Oruro', '', 7.3);
    formatoTexto($pdf, $xTexto, 318.4, 25, 'ADVERTENCIA:', 'B', 7.3);
    formatoTexto($pdf, $xTexto + $pdf->GetStringWidth('ADVERTENCIA: ', 'helvetica', 'B', 7.3), 318.4, 170,
        'Este documento queda nulo, si se advierten raspaduras, anotaciones o enmiendas.', '', 7.3);

    $pdf->SetTextColor(0, 0, 0);
}
