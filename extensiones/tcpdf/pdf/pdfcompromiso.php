<?php
/**
 * Documento Privado de Compromiso - PDF (hoja oficio)
 * Se imprime por inscripción con los datos del posgraduante y los montos del
 * programa (matrícula, colegiatura y cuotas). La fecha de firma
 * es la fecha de inscripción (validación del voucher de matrícula).
 */

date_default_timezone_set('America/La_Paz');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Mismo acceso que la ficha de inscripción: tiene datos personales.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['Validar']) || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
    http_response_code(403);
    die('Acceso denegado. Inicie sesión en el sistema.');
}

$baseDir = realpath(__DIR__ . '/../../../');
require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/modelos/inscripcionmodulo.modelo.php';
require_once $baseDir . '/modelos/autoridad.modelo.php';
require_once $baseDir . '/modelos/configuracion.modelo.php'; // nota mínima de aprobación
// Solo por numeroALetrasOrdenPagoOficial() (montos en letras).
require_once $baseDir . '/vistas/componentes/orden-pago-pdf-render.php';

// Autoridades que firman: se cambian desde la pantalla "Autoridades" (ADM).
$autoridades = AutoridadModelo::ListarModelo();
$nombreDirector    = AutoridadModelo::NombreParaDocumento($autoridades, 'DIRECTOR');
$nombreCoordinador = AutoridadModelo::NombreParaDocumento($autoridades, 'COORDINADOR');

if (empty($_GET['idinscripcion'])) {
    die('Error: ID de inscripción no especificado');
}
$idInscripcion = intval($_GET['idinscripcion']);

$datos = InscripcionModuloModelos::ObtenerDatosCompletosEstudianteModelo($idInscripcion);
if (!$datos) {
    die('Error: No se encontró información del estudiante');
}

// ========================================
// DATOS DEL DOCUMENTO
// ========================================
$e = function ($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
};

// "2.000" si es entero, "2.000,50" si tiene centavos
function montoCompromiso($monto)
{
    $monto = (float)$monto;
    $decimales = (floor($monto) == $monto) ? 0 : 2;
    return number_format($monto, $decimales, ',', '.');
}

// "DOS MIL 00/100 BOLIVIANOS" -> "Dos mil 00/100 bolivianos"
function letrasCompromiso($monto)
{
    $letras = mb_strtolower(numeroALetrasOrdenPagoOficial((float)$monto), 'UTF-8');
    return mb_strtoupper(mb_substr($letras, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($letras, 1, null, 'UTF-8');
}

$nombreCompleto = mb_strtoupper(trim($datos['Nombre'] . ' ' . $datos['Apaterno'] . ' ' . $datos['Amaterno']), 'UTF-8');
$carnet = trim($datos['Ci'] . (!empty($datos['Complemento']) ? '-' . $datos['Complemento'] : '') . ' ' . ($datos['Exp'] ?? ''));
$programa = mb_strtoupper($datos['NombrePrograma'], 'UTF-8');

// Montos que paga ESTE estudiante (no los de lista del programa):
// - Matrícula: lo que pagó al inscribirse (ya trae el recargo de universidad
//   privada o el descuento que tuvo). Bs. 0 si se matriculó sin pago de matrícula.
// - Colegiatura: el monto final de su plan de pago (Regular / Contado / Grupal),
//   con el descuento ya aplicado. Sin plan todavía (p. ej. grupo aún no armado),
//   se usa el costo del programa en cuotas por módulo.
$matricula   = !empty($datos['pagoCompleto']) ? 0 : (float)$datos['montoPagado'];
$costoBase   = (float)$datos['Costo'];
$tipoPlan    = $datos['TipoPlan'] ?? null;
$colegiatura = $tipoPlan ? (float)$datos['PlanMontoTotal'] : $costoBase;
$descuento   = (float)($datos['PlanPorcentajeDescuento'] ?? 0);
$total       = $matricula + $colegiatura;
$numCuotas   = max(1, (int)($datos['NumeroCuotas'] ?: $datos['Modulos']));
$cuota       = round($colegiatura / $numCuotas, 2);
// Fecha de firma = fecha de inscripción (se registra al validar el voucher de matrícula).
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
    'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaFirma = strtotime($datos['FechaInscripcion']);
$diaFirma  = (int)date('j', $fechaFirma);
$mesFirma  = $meses[(int)date('n', $fechaFirma) - 1];
$anioFirma = date('Y', $fechaFirma);

// ========================================
// PDF
// ========================================
// Oficio boliviano: 216 x 330 mm
$pdf = new TCPDF('P', 'mm', [216, 330], true, 'UTF-8', false);
$pdf->SetCreator('Sistema Posgrado FCS');
$pdf->SetAuthor('Posgrado FCS');
$pdf->SetTitle('Documento Privado de Compromiso - ' . $nombreCompleto);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 12, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Encabezado
$pdf->Image(__DIR__ . '/../../imagenespdf/logouto.png', 15, 10, 24);
$pdf->Image(__DIR__ . '/../../imagenespdf/logofcs.png', 179, 10, 22);

// El texto va entre los logos (x 42..176) para no montarse sobre ellos.
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetXY(42, 15);
$pdf->Cell(134, 6, 'UNIVERSIDAD TÉCNICA DE ORURO', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 9.5);
$pdf->SetX(42);
$pdf->Cell(134, 6, 'COORDINACIÓN DE POSGRADO ÁREA DE ODONTOLOGÍA F.C.S.-U.T.O.', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetXY(15, 36);
$pdf->Cell(0, 8, 'DOCUMENTO PRIVADO DE COMPROMISO', 0, 1, 'C');
$pdf->Ln(3);

// Cuerpo
$director    = $e($nombreDirector);
$coordinador = $e($nombreCoordinador);
$nombreHtml  = '<b>' . $e($nombreCompleto) . '</b>';
$programaHtml = '<b>' . $e($programa) . '</b>';

$intro = 'Documento privado de compromiso que suscriben por una parte el(la) señor(a) ' . $nombreHtml
    . ' con carnet de identidad <b>' . $e($carnet) . '</b> y por otra parte el ' . $director
    . ' como Director de Posgrado, ' . $coordinador . ' como Coordinador de Posgrado en el Área de Odontología. '
    . 'Documento que con sola una firma y rúbrica del interesado surtirá efectos legales al tenor de las siguientes cláusulas:';

// Cláusula SEGUNDA: una línea para la matrícula y otra para la colegiatura,
// redactadas según lo que paga el estudiante.
// Matrícula: se parte de la matrícula regular del programa y se detalla la
// diferencia con lo que paga el estudiante (incremento por universidad privada,
// otro incremento o un descuento), en porcentaje y en bolivianos.
$matriculaRegular = (float)$datos['CostoMatricula'];
$textoRegular = 'Matrícula regular del programa: ' . montoCompromiso($matriculaRegular) . ' Bs.';

if ($matricula <= 0) {
    $lineaMatricula = $textoRegular . ' El participante no realiza pago por concepto de matrícula.';
} else {
    $diferencia = round($matricula - $matriculaRegular, 2);
    $ajuste = '';
    if ($matriculaRegular > 0 && abs($diferencia) >= 0.01) {
        $porcentaje = round(abs($diferencia) / $matriculaRegular * 100, 2);
        $detalle = montoCompromiso($porcentaje) . '% (' . montoCompromiso(abs($diferencia)) . ' Bs.)';
        if ($diferencia > 0) {
            $ajuste = ($datos['TipoUniversidad'] ?? '') === 'PRIVADA'
                ? ', con un incremento del ' . $detalle . ' por provenir de universidad privada'
                : ', con un incremento del ' . $detalle;
        } else {
            $ajuste = ', con un descuento del ' . $detalle;
        }
    }
    $lineaMatricula = $textoRegular . $ajuste . ($ajuste ? '.' : '')
        . ' Pago de <b>' . montoCompromiso($matricula) . ' Bs.</b> (' . letrasCompromiso($matricula)
        . ') por concepto de matrícula.';
}

$lineaColegiatura = 'Pago de <b>' . montoCompromiso($colegiatura) . ' Bs.</b> (' . letrasCompromiso($colegiatura)
    . ') por concepto de colegiatura';
// Todos los planes pagan la colegiatura en cuotas; el plan y su descuento se detallan
// si los hay. Programa completo pagado al matricularse: un solo pago.
$nombresPlanCompromiso = ['REGULAR' => 'plan regular', 'DESCUENTO' => 'plan al contado', 'GRUPAL' => 'plan grupal'];
$detallePlan = '';
if ($tipoPlan && $descuento > 0) {
    $detallePlan = ' (' . ($nombresPlanCompromiso[$tipoPlan] ?? 'plan') . ' con ' . montoCompromiso($descuento)
        . '% de descuento sobre el costo de ' . montoCompromiso($costoBase) . ' Bs.)';
} elseif (in_array($tipoPlan, ['DESCUENTO', 'GRUPAL'], true)) {
    $detallePlan = ' (' . $nombresPlanCompromiso[$tipoPlan] . ')';
}
if (!empty($datos['pagoCompleto'])) {
    $lineaColegiatura .= ', en un solo pago' . $detallePlan . '.';
} else {
    $lineaColegiatura .= $detallePlan . ', pago que deberá efectuarse al inicio de cada módulo, <b>' . $numCuotas
        . ' cuotas de ' . montoCompromiso($cuota) . ' Bs.</b>';
}

$clausulas = [
    'PRIMERA:' => '<b>Antecedentes.-</b> La Coordinación de Posgrado de la F.C.S. Área de Odontología U.T.O., se ha permitido '
        . 'desarrollar el programa de ' . $programaHtml . ', mismo que se desarrolla en las fechas y horario según convocatoria.',

    'SEGUNDA:' => '<b>Aceptación y modalidades de pago.-</b> El participante, una vez aceptado en el programa y ser alumno regular, '
        . 'se compromete a realizar el pago del monto total de <b>' . montoCompromiso($total) . ' Bs.</b> ('
        . letrasCompromiso($total) . ') por concepto del pago del programa de ' . $programaHtml
        . ' con la modalidad de pago siguiente:<br>'
        . $lineaMatricula . '<br>'
        . $lineaColegiatura,

    'TERCERA:' => '<b>Compromiso de pago.-</b> Con el propósito de lograr los objetivos y cumplir con las exigencias administrativas y '
        . 'académicas de la Dirección de Posgrado y la Universidad Técnica de Oruro, el postulante al firmar el presente '
        . 'documento se compromete a pagar el monto del curso según se describe en la cláusula segunda.',

    'CUARTA:' => '<b>Compromiso académico.-</b> Se informa que la calificación mínima de aprobación es de ' . ConfiguracionModelo::NotaAprobacion() . ' pts ('
        . preg_replace(['/ 00\/100 BOLIVIANOS$/', '/UNO$/'], ['', 'UN'], numeroALetrasOrdenPagoOficial(ConfiguracionModelo::NotaAprobacion())) . ' PUNTOS). '
        . 'El participante se compromete a participar en cada una de las actividades programadas de acuerdo al cronograma, '
        . 'así mismo a desarrollar y presentar puntualmente las actividades en cada módulo; en caso de incumplimiento el '
        . 'participante será declarado alumno en abandono y no tendrá derecho a la calificación del módulo, quedando como '
        . '<u>reprobado</u>.<br><br>'
        . 'El participante se compromete a acogerse a la planificación de actividades académicas presentadas por medio de la '
        . 'Coordinación del Programa y en correspondencia al proyecto de programa aprobado por las instancias universitarias superiores.<br>'
        . 'Adicionalmente, el participante se compromete a realizar todos los trabajos establecidos, de manera puntual, de los '
        . 'módulos. No se aceptarán licencias a medio módulo, considerándose tal módulo como reprobado si lo abandona.<br><br>'
        . 'Tal como establece el artículo No. 115 del Reglamento de Posgrado de la Universidad Técnica de Oruro, se informa que '
        . 'el posgraduante tiene la posibilidad excepcional de reprobación de un módulo durante todo el proceso posgradual, '
        . 'mismo que podrá ser regularizado conforme la normativa vigente en la Universidad Técnica de Oruro.',

    'QUINTA:' => '<b>Conformidad.-</b> Por parte de la Universidad Técnica de Oruro, el ' . $director . ', Director de Posgrado, '
        . 'y por otra el(la) señor(a) ' . $nombreHtml . ', participante del programa, todos declaran su plena y absoluta '
        . 'conformidad con las cláusulas del presente documento y en señal, firman el presente documento en la ciudad de '
        . 'Oruro a los ' . $diaFirma . ($diaFirma == 1 ? ' día' : ' días') . ' del mes de ' . $mesFirma . ' del año ' . $anioFirma . '.',
];

// Cada cláusula: título en una columna fija a la izquierda y el texto justificado
// a su derecha (con celdas sueltas en vez de una tabla HTML, que en TCPDF deja
// mal calculada la altura usada y empujaba las firmas a otra hoja).
function cuerpoCompromiso($pdf, $intro, array $clausulas, $tamano)
{
    $pdf->setCellHeightRatio(1.25);
    $pdf->SetFont('helvetica', '', $tamano);
    $pdf->writeHTMLCell(0, 0, 15, $pdf->GetY(), $intro, 0, 1, false, true, 'J');
    $pdf->Ln(3);

    foreach ($clausulas as $titulo => $texto) {
        $y = $pdf->GetY();
        $pdf->SetFont('helvetica', 'B', $tamano);
        $pdf->SetXY(15, $y);
        $pdf->Cell(23, 5, $titulo, 0, 0, 'L');
        $pdf->SetFont('helvetica', '', $tamano);
        $pdf->writeHTMLCell(163, 0, 38, $y, $texto, 0, 1, false, true, 'J');
        $pdf->Ln(2);
    }
}

// El bloque de firmas ocupa 62 mm y debe quedar en la misma hoja que el texto.
// Un nombre o programa largo agrega renglones: si con letra 10 las firmas no
// entran, se deshace (transacción de TCPDF) y se vuelve a escribir más pequeño.
$limiteTexto = 330 - 15 - 62;
foreach ([10, 9.5, 9] as $tamano) {
    $pdf->startTransaction();
    cuerpoCompromiso($pdf, $intro, $clausulas, $tamano);
    if ($pdf->GetY() <= $limiteTexto || $tamano == 9) {
        $pdf->commitTransaction();
        break;
    }
    $pdf = $pdf->rollbackTransaction();
}

// ========================================
// FIRMAS
// ========================================
// Último recurso (texto muy largo aun con letra 9): firmas completas en hoja nueva.
if ($pdf->GetY() > $limiteTexto) {
    $pdf->AddPage();
}
$y = $pdf->GetY() + 18;

function firmaCompromiso($pdf, $x, $y, $ancho, array $lineas)
{
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.2);
    $pdf->Line($x, $y, $x + $ancho, $y);
    $pdf->SetXY($x, $y + 1);
    $pdf->SetFont('helvetica', '', 10);
    foreach ($lineas as $linea) {
        $pdf->SetX($x);
        $pdf->Cell($ancho, 5, $linea, 0, 1, 'C');
    }
}

firmaCompromiso($pdf, 18, $y, 75, [$nombreCoordinador, 'COORDINADOR']);
firmaCompromiso($pdf, 123, $y, 75, [$nombreCompleto, 'C.I. ' . $carnet, '(POSTULANTE)']);
firmaCompromiso($pdf, 70.5, $y + 28, 75, [$nombreDirector, 'DIRECTOR', 'DIRECCIÓN DE POSGRADO UTO']);

$nombreArchivo = 'Compromiso_' . preg_replace('/[^A-Za-z0-9]+/', '_', $datos['Apaterno'] . '_' . $datos['Nombre']) . '.pdf';
$pdf->Output($nombreArchivo, 'I');
