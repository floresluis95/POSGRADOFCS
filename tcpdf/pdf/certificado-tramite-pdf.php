<?php
/**
 * Trámite A/B - Formulario de Solicitud para Trámite Universitario (PDF)
 * El formulario es un valorado PRE-IMPRESO de la UTO (hoja carta): aquí se imprimen
 * SOLO los datos, sobre sus líneas de puntos. Cada dato se achica (hasta un mínimo)
 * para no salirse de su línea; si aun así no entra, se avisa y no se genera.
 *
 * El N° de voucher y el monto validan el pago (se guardan en documentoemitido) pero
 * NO se imprimen en el formulario. Pedido de Luis (2026-11-12).
 *
 * En la vista previa se dibuja debajo el escaneo del formulario (con marca de agua)
 * para ver cómo calza cada dato; el documento real sale sin fondo.
 * Coordenadas medidas sobre el escaneo; si en la impresora algo se corre, ajustar
 * el bloque "COORDENADAS" (o AJUSTE_X / AJUSTE_Y para mover todo junto).
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start(); // un aviso de PHP impreso antes del PDF impide enviarlo

session_start();
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    die('Acceso no autorizado. Sesión inválida.');
}
// Los certificados los emite solo Administración (igual que los demás)
if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC'], true)) {
    die('Acceso no autorizado.');
}

require_once '../../vendor/autoload.php';
require_once '../../modelos/certificado.modelo.php';

date_default_timezone_set('America/La_Paz');

$limpiar = function ($v) {
    return trim(preg_replace('/\s+/u', ' ', (string)$v));
};
$mayus = function ($v) use ($limpiar) {
    return mb_strtoupper($limpiar($v), 'UTF-8');
};

$estudianteID = intval($_POST['estudianteID'] ?? 0);
$programaID = intval($_POST['programaID'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$dirigido = $limpiar($_POST['dirigido'] ?? ''); // como se escribe: "MSc. Ing. ..."
$cargo = $mayus($_POST['cargo'] ?? '');
$nombre = $mayus($_POST['nombre'] ?? '');
$ci = $mayus($_POST['ci'] ?? '');
$telefono = $limpiar($_POST['telefono'] ?? '');
$descripcion = $limpiar($_POST['descripcion'] ?? '');
$vaucher = CertificadoModelo::NormalizarVoucher($_POST['vaucher'] ?? '');

// Hoja de prueba (botón "Ajuste de impresión"): datos de ejemplo en todas las líneas, para
// imprimirla sobre un formulario (o encimarla a uno a contraluz) y ver cuánto se corre.
// No pide voucher, no lleva fondo ni marca de agua y no registra nada.
$prueba = !empty($_POST['prueba']);
if ($prueba) {
    $fecha = date('Y-m-d');
    $dirigido = 'MSc. Ing. NOMBRE DE PRUEBA DEL DESTINATARIO';
    $cargo = 'CARGO DE PRUEBA';
    $nombre = 'NOMBRE Y APELLIDOS DE PRUEBA';
    $ci = '1234567 OR';
    $telefono = '71234567';
    $descripcion = 'Línea de prueba de la descripción de la solicitud. Línea de prueba de la descripción de la solicitud. '
        . 'Línea de prueba de la descripción de la solicitud. Línea de prueba de la descripción de la solicitud. '
        . 'Línea de prueba de la descripción de la solicitud. Línea de prueba de la descripción de la solicitud. '
        . 'Línea de prueba de la descripción de la solicitud. Fin.';
    $vaucher = '';
}

$falla = function ($mensaje) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: text/html; charset=utf-8');
    die('<div style="font-family:Arial,sans-serif;max-width:600px;margin:40px auto;padding:16px;border:1px solid #ccc">'
        . '<b>No se generó el formulario.</b><br><br>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</div>');
};

if ($vaucher === false) {
    $falla('El N° de voucher solo puede tener letras y números (y un guion entre las dos partes).');
}
if ($nombre === '' || $dirigido === '' || $cargo === '' || $descripcion === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $falla('Faltan datos: fecha, dirigido a, cargo, nombre y descripción son obligatorios.');
}
// El pago se valida con el voucher (no se imprime). En la vista previa puede faltar.
if ($vaucher === '' && !CertificadoModelo::EsVistaPrevia() && !$prueba) {
    $falla('Falta el N° de voucher del pago del trámite.');
}
$voucherRepetido = $vaucher !== '' ? CertificadoModelo::VoucherRepetidoMensaje($vaucher, intval($_POST['idDocumento'] ?? 0)) : null;
if ($voucherRepetido) {
    $falla($voucherRepetido);
}

// ===================================
// COORDENADAS (mm) - hoja carta vertical (215.9 x 279.4 mm)
// La UTO tiene DOS impresiones del formulario con medidas distintas:
//  - Formato 1: impreso en celeste, líneas de puntos (escaneo img20261112_17484858).
//  - Formato 2: impreso en negro, líneas continuas (escaneo img20261112_18384352).
// y = altura de la línea; el texto se apoya un poco encima. Cada dato empieza 1,5 mm
// después de los dos puntos de su etiqueta y termina antes de la siguiente.
// ===================================
$FORMATOS = [
    '1' => [
        'nombre' => 'Formato 1 (celeste, líneas de puntos)',
        // Medido de nuevo sobre el escaneo del formulario en blanco que mandó Luis
        // (img20261113_08070353, 200 dpi, 2026-11-13). Cada tramo = donde empiezan y terminan
        // los puntos, con 1,5 mm de margen. Los datos van CENTRADOS en su tramo, menos la
        // descripción, que empieza 5 mm después de donde empiezan los puntos.
        'fecha' => ['y' => 77.6, 'dia' => [46.1, 61.2], 'mes' => [62.3, 78.8], 'anio' => [79.7, 95.5]],
        'dirigido' => ['y' => 94.4, 'x1' => 62.6, 'x2' => 179.7],
        'cargo' => ['y' => 101.6, 'x1' => 54.7, 'x2' => 179.7],
        'nombre1' => ['y' => 125.7, 'x1' => 84.5, 'x2' => 179.6],
        'nombre2' => ['y' => 132.6, 'x1' => 38.6, 'x2' => 179.6],
        'ci' => ['y' => 139.8, 'x1' => 86.1, 'x2' => 114.3],
        'telefono' => ['y' => 139.8, 'x1' => 146.6, 'x2' => 179.6],
        // Descripción medio centímetro más a la derecha (pedido de Luis, 2026-11-13)
        'descripcion' => [163.6, 170.9, 178.1, 185.4], 'descX1' => 43.0, 'descX2' => 179.7,
        'claveAjuste' => 'TRAMITE_AJUSTE',
        'corrimiento' => [0.0, 0.0],
        // Letra algo más chica y un poco más separada de los puntos, para que no los toque
        'centrar' => true,
        'letraMax' => 9.0,
        'letraMaxDescripcion' => 8.5,
        'sobreLinea' => 1.3,
    ],
    '2' => [
        'nombre' => 'Formato 2 (negro, líneas continuas)',
        // Corregido tras imprimir (Luis, 2026-11-12): fecha, dirigido y cargo 4 mm a la derecha;
        // nombre 5 mm a la derecha; descripción 3 mm a la derecha y 4 mm más arriba
        'fecha' => ['y' => 75.2, 'dia' => [47.0, 58.9], 'mes' => [61.0, 72.9], 'anio' => [75.2, 87.3]],
        'dirigido' => ['y' => 91.5, 'x1' => 65.0, 'x2' => 180.0],
        'cargo' => ['y' => 98.6, 'x1' => 57.2, 'x2' => 180.0],
        'nombre1' => ['y' => 122.9, 'x1' => 84.5, 'x2' => 181.0],
        'nombre2' => ['y' => 129.9, 'x1' => 42.6, 'x2' => 181.0],
        'ci' => ['y' => 137.2, 'x1' => 81.4, 'x2' => 111.6],
        'telefono' => ['y' => 137.2, 'x1' => 139.8, 'x2' => 176.0],
        'descripcion' => [157.1, 164.1, 171.0, 178.2], 'descX1' => 40.6, 'descX2' => 179.0,
        'claveAjuste' => 'TRAMITE2_AJUSTE',
        // Impreso sobre el formulario real (foto de Luis, 2026-11-12) todo salía ~7,5 mm más
        // ARRIBA de sus líneas (parejo en todos los datos): el escaneo medido está corrido
        // respecto al papel. Se baja todo; el ajuste de impresión queda para afinar.
        'corrimiento' => [0.0, 0.0], // fue 7,5 mm; Luis pidió subir 2,5 y luego 5 mm más (2026-11-12)
    ],
];
$formato = (string)($_POST['formato'] ?? '1');
if (!isset($FORMATOS[$formato])) {
    $formato = '1'; // documentos emitidos antes de que hubiera dos formatos
}
$F = $FORMATOS[$formato];

// Ajuste de impresión (mm) de cada formato, guardado desde la pantalla del trámite: corrige
// lo que la impresora corre al imprimir sobre el papel (+X = derecha, +Y = abajo).
// La hoja de prueba puede traer valores aún sin guardar, para probarlos antes.
$ajusteImpresion = function ($eje) use ($prueba, $F) {
    $campo = 'ajuste' . $eje;
    $valor = ($prueba && isset($_POST[$campo]) && $_POST[$campo] !== '')
        ? $_POST[$campo] : ConfiguracionModelo::Obtener($F['claveAjuste'] . '_' . $eje, '0');
    $v = (float)str_replace(',', '.', (string)$valor);
    return max(-25.0, min(25.0, $v));
};
define('AJUSTE_X', $ajusteImpresion('X') + $F['corrimiento'][0]);
define('AJUSTE_Y', $ajusteImpresion('Y') + $F['corrimiento'][1]);
$SOBRE_LINEA = $F['sobreLinea'] ?? 0.9;   // mm que el texto queda encima de la línea
$LETRA_MAX = $F['letraMax'] ?? 10;        // pt
$LETRA_MIN = 6.5;     // pt: más chico ya no se lee bien
$LETRA_MAX_DESCRIPCION = $F['letraMaxDescripcion'] ?? 9.5; // pt (formato 2: Luis la pidió de 8,5 a más grande)
$ALINEAR = !empty($F['centrar']) ? 'C' : 'L'; // alineación de los datos de una línea (no la descripción)

$FECHA = $F['fecha'];
$DIRIGIDO = $F['dirigido'];
$CARGO = $F['cargo'];
$NOMBRE = [$F['nombre1'], $F['nombre2']];
$CI = $F['ci'];
$TELEFONO = $F['telefono'];
$DESCRIPCION = array_map(function ($y) use ($F) {
    return ['y' => $y, 'x1' => $F['descX1'], 'x2' => $F['descX2']];
}, $F['descripcion']);

// ===================================
// PDF
// ===================================
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - Posgrado FCS');
$pdf->SetTitle('Trámite A/B - ' . $nombre);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->setCellPaddings(0, 0, 0, 0);
$pdf->setCellMargins(0, 0, 0, 0);
$pdf->AddPage();

/**
 * Formato 2 dibujado (solo vista previa): su escaneo tiene datos de una persona, así que no
 * se usa de fondo; se dibujan sus etiquetas, recuadros y líneas con las medidas del escaneo.
 */
$dibujarFormato2 = function ($dx, $dy) use ($pdf) {
    $gris = [90, 90, 90];
    $pdf->SetTextColorArray($gris);
    $pdf->SetDrawColorArray($gris);
    $pdf->SetLineStyle(['width' => 0.3, 'dash' => 0, 'color' => $gris]);
    $texto = function ($x, $yBase, $t, $estilo, $tam, $ancho = 0, $alin = 'L', $estirar = 0) use ($pdf, $dx, $dy) {
        $pdf->SetFont('helvetica', $estilo, $tam);
        $alto = $tam * 0.3528 * 1.25;
        $pdf->SetXY($x + $dx, $yBase + $dy - $alto);
        $pdf->Cell($ancho, $alto, $t, 0, 0, $alin, false, '', $estirar, false, 'T', 'B');
    };
    $linea = function ($x1, $x2, $y) use ($pdf, $dx, $dy) { $pdf->Line($x1 + $dx, $y + $dy, $x2 + $dx, $y + $dy); };
    $caja = function ($x1, $y1, $x2, $y2) use ($pdf, $dx, $dy) {
        $pdf->RoundedRect($x1 + $dx, $y1 + $dy, $x2 - $x1, $y2 - $y1, 3, '1111', 'D');
    };

    $texto(0, 32.5, 'UNIVERSIDAD TÉCNICA DE ORURO', 'B', 21, 215.9, 'C');
    $texto(0, 39.2, 'FORMULARIO DE SOLICITUD PARA TRÁMITE UNIVERSITARIO', 'B', 13.5, 215.9, 'C');
    $texto(29.3, 75.2, 'FECHA:', '', 10.5, 12.6, 'L', 2);
    // Fecha y descripción corridas igual que sus datos (así cayeron en el papel real)
    $linea(47.0, 58.9, 75.2); $texto(59.3, 75.2, '/', '', 10.5);
    $linea(61.0, 72.9, 75.2); $texto(73.3, 75.2, '/', '', 10.5);
    $linea(75.2, 87.3, 75.2);

    $caja(29.1, 83.4, 184.4, 102.7);
    $texto(37.0, 91.5, 'DIRIGIDO A:', '', 10.5, 21.0, 'L', 2); $linea(59.4, 176.3, 91.5);
    $texto(37.0, 98.6, 'CARGO:', '', 10.5, 14.0, 'L', 2);      $linea(51.7, 176.3, 98.6);

    $texto(37.0, 113.0, 'DATOS DEL SOLICITANTE', 'B', 10.5);
    $caja(29.1, 114.8, 184.4, 140.7);
    $texto(37.0, 122.9, 'NOMBRE Y APELLIDOS:', '', 10.5, 40.1, 'L', 2); $linea(78.0, 176.3, 122.9);
    $linea(37.4, 176.3, 129.9);
    $texto(37.0, 137.2, 'CÉDULA DE IDENTIDAD:', '', 10.5, 42.1, 'L', 2); $linea(79.9, 111.8, 137.2);
    $texto(112.9, 137.2, 'Nº TELÉFONO:', '', 10.5, 24.7, 'L', 2);         $linea(138.3, 176.3, 137.2);

    $texto(37.0, 147.4, 'DESCRIPCIÓN DE LA SOLICITUD', 'B', 10.5);
    $caja(29.0, 149.3, 184.2, 182.4);
    foreach ([157.1, 164.1, 171.0, 178.2] as $y) { $linea(40.4, 179.1, $y); }

    $linea(29.3, 88.3, 241.5);
    $texto(36.0, 246.0, 'FIRMA DEL INTERESADO', 'B', 10.5);
    $pdf->SetTextColor(0, 0, 0);
};

// Vista previa: el formulario de fondo, para ver cómo calzan los datos. Se corre igual
// que los datos (el ajuste corrige la impresora), así sigue mostrando dónde cae cada uno.
$fondo = __DIR__ . '/../../extensiones/imagenespdf/formato-tramite.jpg';
if (CertificadoModelo::EsVistaPrevia() && !$prueba) {
    if ($formato === '2') {
        $dibujarFormato2(AJUSTE_X, AJUSTE_Y);
    } elseif (is_file($fondo)) {
        $pdf->Image($fondo, AJUSTE_X, AJUSTE_Y, 215.9, 279.4, 'JPG', '', '', false, 150);
    }
    // Marca de agua tenue y DEBAJO de los datos (para poder leerlos)
    $pdf->SetAlpha(0.35);
    $pdf->SetTextColor(200, 205, 215);
    $pdf->SetFont('helvetica', 'B', 60);
    $pdf->StartTransform();
    $pdf->Rotate(35, 107.95, 139.7);
    $pdf->Text(107.95 - 75, 139.7 - 12, 'VISTA PREVIA');
    $pdf->StopTransform();
    $pdf->SetAlpha(1);
}
$pdf->SetTextColor(0, 0, 0);
if ($prueba) {
    // Al pie, fuera de las casillas del formulario: qué ajuste se usó
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Text(15, 268, 'HOJA DE PRUEBA (no es un trámite) · ' . $F['nombre'] . ' · ajuste: '
        . number_format(AJUSTE_X - $F['corrimiento'][0], 1, ',', '') . ' mm a la derecha, ' . number_format(AJUSTE_Y - $F['corrimiento'][1], 1, ',', '') . ' mm abajo');
}

/** Texto apoyado en la línea Y, entre x1 y x2 */
$escribir = function ($texto, $x1, $x2, $y, $tamano, $alin = 'L') use ($pdf, $SOBRE_LINEA) {
    $pdf->SetFont('helvetica', '', $tamano);
    $alto = $tamano * 0.3528 * 1.25;
    $pdf->SetXY($x1 + AJUSTE_X, $y + AJUSTE_Y - $SOBRE_LINEA - $alto);
    $pdf->Cell($x2 - $x1, $alto, $texto, 0, 0, $alin, false, '', 0, false, 'T', 'B');
};

/**
 * Reparte el texto en las líneas dadas (cada una con su ancho), con la letra más
 * grande que haga entrar todo. Devuelve [tamaño, [texto por línea]] o null si no entra.
 */
$repartir = function ($texto, array $lineas, $letraMax = null) use ($pdf, $LETRA_MAX, $LETRA_MIN) {
    $palabras = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
    for ($tamano = $letraMax ?: $LETRA_MAX;$tamano >= $LETRA_MIN - 0.001; $tamano -= 0.25) {
        $pdf->SetFont('helvetica', '', $tamano);
        $resultado = [];
        $i = 0;
        foreach ($lineas as $linea) {
            $ancho = $linea['x2'] - $linea['x1'];
            $actual = '';
            while ($i < count($palabras)) {
                $prueba = $actual === '' ? $palabras[$i] : $actual . ' ' . $palabras[$i];
                if ($pdf->GetStringWidth($prueba) <= $ancho) {
                    $actual = $prueba;
                    $i++;
                } elseif ($actual === '') {
                    break; // una palabra sola más larga que la línea: probar con letra más chica
                } else {
                    break;
                }
            }
            $resultado[] = $actual;
        }
        if ($i >= count($palabras)) {
            return [$tamano, $resultado];
        }
    }
    return null;
};

/** Un dato de una sola línea, achicando la letra si hace falta */
$dato = function ($texto, array $linea, $nombreCampo, $alin = 'L') use ($repartir, $escribir, $falla) {
    if ($texto === '') return;
    $r = $repartir($texto, [$linea]);
    if (!$r) {
        $falla('"' . $nombreCampo . '" es demasiado largo para su línea del formulario. Acórtelo.');
    }
    $escribir($r[1][0], $linea['x1'], $linea['x2'], $linea['y'], $r[0], $alin);
};

// Fecha: día / mes / año centrados en sus tramos
$t = strtotime($fecha);
$escribir(date('d', $t), $FECHA['dia'][0], $FECHA['dia'][1], $FECHA['y'], $LETRA_MAX, 'C');
$escribir(date('m', $t), $FECHA['mes'][0], $FECHA['mes'][1], $FECHA['y'], $LETRA_MAX, 'C');
$escribir(date('Y', $t), $FECHA['anio'][0], $FECHA['anio'][1], $FECHA['y'], $LETRA_MAX, 'C');

$dato($dirigido, $DIRIGIDO, 'Dirigido a', $ALINEAR);
$dato($cargo, $CARGO, 'Cargo', $ALINEAR);
$dato($ci, $CI, 'Cédula de identidad', $ALINEAR);
$dato($telefono, $TELEFONO, 'N° de teléfono', $ALINEAR);

// Nombre: primero en su línea; si no entra, sigue en la línea de abajo
foreach ([[$NOMBRE[0]], $NOMBRE] as $lineasNombre) {
    $r = $repartir($nombre, $lineasNombre);
    if ($r && ($r[0] >= 8 || count($lineasNombre) === 2)) break;
}
if (!$r) {
    $falla('El nombre es demasiado largo para las líneas del formulario.');
}
foreach ($r[1] as $i => $texto) {
    $escribir($texto, $NOMBRE[$i]['x1'], $NOMBRE[$i]['x2'], $NOMBRE[$i]['y'], $r[0], $ALINEAR);
}

// Descripción: hasta 4 líneas, todas con el mismo tamaño de letra
$r = $repartir($descripcion, $DESCRIPCION, $LETRA_MAX_DESCRIPCION);
if (!$r) {
    $falla('La descripción de la solicitud no entra en las 4 líneas del formulario. Acórtela.');
}
foreach ($r[1] as $i => $texto) {
    $escribir($texto, $DESCRIPCION[$i]['x1'], $DESCRIPCION[$i]['x2'], $DESCRIPCION[$i]['y'], $r[0]);
}

// Se registra (y se guardan los datos para reimprimirlo desde Registro de documentos)
// solo cuando todo entró; la vista previa no registra nada.
if (!CertificadoModelo::EsVistaPrevia() && !$prueba) {
    CertificadoModelo::RegistrarEmisionModelo(
        'TRAMITE', $estudianteID, $programaID, $nombre, $vaucher, $_SESSION['IdUsuario'] ?? null,
        $_POST, intval($_POST['idDocumento'] ?? 0)
    );
}

while (ob_get_level() > 0) {
    ob_end_clean();
}
$nombreArchivo = 'Tramite_AB_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombre) . '_' . date('Ymd') . '.pdf';
$pdf->Output($nombreArchivo, 'I');
exit;
