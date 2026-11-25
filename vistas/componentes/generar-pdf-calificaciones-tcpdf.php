<?php
/**
 * Generador de PDF de Calificaciones con TCPDF
 * Genera el PDF y lo guarda automáticamente
 */

// El servidor tiene otra zona horaria configurada en php.ini; sin esto, las
// fechas impresas en el PDF no corresponden a la hora de Bolivia.
date_default_timezone_set('America/La_Paz');

// display_errors está activo en este servidor: cualquier warning/notice suelto
// (ej. del mkdir de más abajo, o de TCPDF) se imprimía ANTES de los bytes del
// PDF y lo corrompía para el navegador (aunque el archivo guardado en disco sí
// quedaba bien, porque ese se escribe aparte). Con ob_start() se atrapa
// cualquier salida accidental y se descarta justo antes de enviar el PDF real,
// igual que ya hacen generar-lista-asistencia-pdf.php y pdfestudiante.php.
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['Validar']) || $_SESSION['Validar'] !== true) {
    ob_end_clean();
    die(json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida'
    ]));
}
// Reporte de calificaciones: solo Administración, Secretaría y Docentes (no Estudiantes)
if (!in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX', 'DOC'], true)) {
    ob_end_clean();
    die(json_encode([
        'status' => 'error',
        'message' => 'No tiene permisos para esta acción'
    ]));
}

// Obtener parámetros
$moduloID = isset($_GET['moduloID']) ? intval($_GET['moduloID']) : 0;
$programaID = isset($_GET['programaID']) ? intval($_GET['programaID']) : 0;
$moduloNombre = isset($_GET['moduloNombre']) ? $_GET['moduloNombre'] : '';
$moduloCodigo = isset($_GET['moduloCodigo']) ? $_GET['moduloCodigo'] : '';
$programaNombre = isset($_GET['programaNombre']) ? $_GET['programaNombre'] : '';
$gradoAcademico = isset($_GET['gradoAcademico']) ? $_GET['gradoAcademico'] : '';
$docenteNombre = isset($_GET['docenteNombre']) ? $_GET['docenteNombre'] : '';
// Sigla del PROGRAMA (no del módulo): antes esta columna de la tabla se
// llenaba con "siglaModulo", un parámetro que ningún llamador enviaba nunca,
// así que la columna "SIGLA" siempre salía vacía.
$sigla = isset($_GET['sigla']) ? $_GET['sigla'] : '';
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';
// Fecha de "Oruro, [fecha]" junto a las firmas: es la fecha en que se firma
// la planilla, editable por separado de la fecha de inicio/fin del módulo.
// Si no se envía (compatibilidad), se usa la fecha de hoy.
$fechaGeneracion = !empty($_GET['fechaGeneracion']) ? $_GET['fechaGeneracion'] : date('Y-m-d');

// Formatear fechas
$fechaInicioFormateada = '';
$fechaFinFormateada = '';

if ($fechaInicio) {
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaInicio);
    if ($fecha) {
        $fechaInicioFormateada = $fecha->format('d/m/Y');
    }
}

if ($fechaFin) {
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaFin);
    if ($fecha) {
        $fechaFinFormateada = $fecha->format('d/m/Y');
    }
}

// Incluir TCPDF
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';
// modelos/conexion.modelo.php no existe en este servidor (lo eliminó el
// antivirus del equipo; ver el workaround documentado en el .htaccess de la
// raíz), así que hay que caer a la copia de respaldo en core/. Sin este
// fallback, este archivo fallaba con un error fatal en TODAS las peticiones
// (por eso nunca se veía el PDF).
require_once file_exists(__DIR__ . '/../../modelos/conexion.modelo.php')
    ? __DIR__ . '/../../modelos/conexion.modelo.php'
    : __DIR__ . '/../../core/conexion.modelo.php';
require_once __DIR__ . '/../../modelos/calificacion.modelo.php';
require_once __DIR__ . '/../../modelos/institucion.modelo.php'; // datos y logos de la institución (encabezado)
require_once __DIR__ . '/../../modelos/configuracion.modelo.php'; // nota mínima de aprobación (escala)
require_once __DIR__ . '/../../modelos/autoridad.modelo.php'; // nombre del coordinador (firma)
require_once __DIR__ . '/../../modelos/planillaguardada.modelo.php'; // historial de planillas generadas

// El docente solo puede generar la planilla de los módulos que tiene asignados
// (antes bastaba con cambiar moduloID en la dirección para ver otro módulo).
if (!PlanillaGuardadaModelo::PuedeVerModulo($programaID, $moduloID)) {
    ob_end_clean();
    http_response_code(403);
    die('No tiene permisos para generar la planilla de este módulo.');
}

// Obtener estudiantes y calificaciones
$estudiantes = CalificacionModelo::ObtenerEstudiantesPorModuloModelo($moduloID, $programaID);

// Función para convertir números a letras
function numeroALetras($numero) {
    $entero = intval($numero);

    $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $veintenas = ['VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];

    if ($entero == 0) {
        $literal = 'CERO';
    } elseif ($entero == 100) {
        $literal = 'CIEN';
    } elseif ($entero > 100) {
        $literal = 'CIENTO ';
        $resto = $entero - 100;

        if ($resto >= 10 && $resto < 20) {
            $literal .= $especiales[$resto - 10];
        } elseif ($resto >= 20 && $resto < 30) {
            $literal .= $veintenas[$resto - 20];
        } elseif ($resto >= 30) {
            $dec = floor($resto / 10);
            $uni = $resto % 10;
            $literal .= $decenas[$dec];
            if ($uni > 0) {
                $literal .= ' Y ' . $unidades[$uni];
            }
        } else {
            $literal .= $unidades[$resto];
        }
    } elseif ($entero >= 10 && $entero < 20) {
        $literal = $especiales[$entero - 10];
    } elseif ($entero >= 20 && $entero < 30) {
        $literal = $veintenas[$entero - 20];
    } else {
        $dec = floor($entero / 10);
        $uni = $entero % 10;
        $literal = $decenas[$dec];
        if ($uni > 0) {
            if ($dec > 0) {
                $literal .= ' Y ';
            }
            $literal .= $unidades[$uni];
        }
    }

    return trim($literal);
}

/**
 * Formatea una fecha "Y-m-d" como "D de MES del AAAA" (para la línea de
 * lugar/fecha de firma de la planilla).
 */
function fechaLarga($fechaYmd) {
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $ts = strtotime($fechaYmd);
    if (!$ts) {
        return '__________';
    }
    return date('j', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' del ' . date('Y', $ts);
}

// ================================================================
// DISEÑO DE LA PLANILLA (2026-11-25): mismo formato que la planilla oficial en
// Word (LISTA_1.docx de Cirugía Bucal): encabezado con logos y doble línea,
// cuadro PROGRAMA / MÓDULO / DOCENTE / FECHA, título, tabla POSGRADUANTES /
// CALIFICACIÓN, escala en recuadro, lugar y fecha, y firmas con línea punteada.
// Versión formal: solo negro y gris claro (sin colores).
// ================================================================
const PL_ETIQUETA = [232, 232, 232]; // fondo gris de etiquetas y cabeceras
const PL_GRIS = [70, 70, 70];

$notaMinima = ConfiguracionModelo::NotaAprobacion();
$autoridades = AutoridadModelo::ListarModelo();
$coordinadorNombre = AutoridadModelo::NombreParaDocumento($autoridades, 'COORDINADOR');

$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema de Gestión Académica - ' . InstitucionModelo::NombreSistema());
$pdf->SetAuthor(InstitucionModelo::Universidad());
$pdf->SetTitle('Planilla de Calificaciones - ' . $moduloCodigo);
$pdf->SetSubject('Planilla de Calificaciones');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$margen = 22;
$pdf->SetMargins($margen, 10, $margen);
$pdf->SetAutoPageBreak(false); // los saltos de página se controlan a mano (repite la cabecera de la tabla)
$pdf->AddPage();

$anchoUtil = $pdf->getPageWidth() - 2 * $margen;
$xDer = $margen + $anchoUtil;

// ---------- Encabezado institucional ----------
$logoIzq = InstitucionModelo::LogoPdf('LOGO_IZQUIERDO');
$logoDer = InstitucionModelo::LogoPdf('LOGO_DERECHO');
// Misma caja de 21x21 mm para los dos logos ($fitbox 'CM': se escala sin
// deformar y se centra), porque tienen proporciones muy distintas.
if ($logoIzq) {
    $pdf->Image($logoIzq, $margen, 10, 21, 21, '', '', '', true, 300, '', false, false, 0, 'CM');
}
if ($logoDer) {
    $pdf->Image($logoDer, $xDer - 21, 10, 21, 21, '', '', '', true, 300, '', false, false, 0, 'CM');
}
$pdf->SetXY($margen + 23, 10);
$anchoCentro = $anchoUtil - 46;
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('times', 'B', 16);
$pdf->Cell($anchoCentro, 7, InstitucionModelo::Universidad(), 0, 2, 'C');
$pdf->SetFont('times', 'B', 12);
$pdf->Cell($anchoCentro, 5.5, InstitucionModelo::Facultad(), 0, 2, 'C');
$pdf->Cell($anchoCentro, 5.5, InstitucionModelo::Unidad(), 0, 2, 'C');
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColorArray(PL_GRIS);
$pdf->Cell($anchoCentro, 3.5, InstitucionModelo::DireccionCompleta(' - ', false), 0, 2, 'C', false, '', 1);
$pdf->Cell($anchoCentro, 3.5, InstitucionModelo::Ciudad(), 0, 2, 'C');
$pdf->SetTextColor(0, 0, 0);

// Doble línea bajo el encabezado
$y = max($pdf->GetY(), 31) + 2;
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line($margen, $y, $xDer, $y);
$pdf->SetLineWidth(0.2);
$pdf->Line($margen, $y + 0.9, $xDer, $y + 0.9);

// ---------- Cuadro de datos del módulo ----------
$pdf->SetXY($margen, $y + 6);
$pdf->SetLineWidth(0.2);
$wEtiqueta = round($anchoUtil * 0.35, 1);
$wValor = $anchoUtil - $wEtiqueta;

$filaDato = function ($etiqueta, $valor) use ($pdf, $wEtiqueta, $wValor) {
    $pdf->SetFont('helvetica', '', 9);
    $alto = max(6, $pdf->getNumLines($valor, $wValor) * 4.2 + 1.5);
    $pdf->SetFillColorArray(PL_ETIQUETA);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell($wEtiqueta, $alto, $etiqueta, 1, 'C', true, 0, '', '', true, 0, false, true, $alto, 'M');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell($wValor, $alto, $valor, 1, 'C', false, 1, '', '', true, 0, false, true, $alto, 'M');
};

$moduloTexto = trim(($moduloCodigo !== '' ? $moduloCodigo . ' – ' : '') . $moduloNombre);
$filaDato('PROGRAMA:', $programaNombre . ($sigla !== '' ? ' (' . $sigla . ')' : ''));
$filaDato('MÓDULO:', $moduloTexto);
$filaDato('DOCENTE:', $docenteNombre !== '' ? $docenteNombre : '—');

// FECHA: inicio y finalización en dos mitades, como en el formato
$pdf->SetFillColorArray(PL_ETIQUETA);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell($wEtiqueta, 6, 'FECHA:', 1, 0, 'C', true);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($wValor / 2, 6, $fechaInicio ? fechaLarga($fechaInicio) : '__________', 1, 0, 'C');
$pdf->Cell($wValor / 2, 6, $fechaFin ? fechaLarga($fechaFin) : '__________', 1, 1, 'C');

// ---------- Título ----------
$pdf->Ln(2);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell($anchoUtil, 6, 'PLANILLA DE CALIFICACIONES', 'B', 1, 'C');
$pdf->Ln(5);

// ---------- Tabla de calificaciones ----------
// N° | NOMBRE COMPLETO | SIGLA | MÓDULO | NUM. | LITERAL (proporciones del formato)
$proporcion = [36, 265, 97, 85, 58, 146];
$total = array_sum($proporcion);
$w = array_map(function ($p) use ($anchoUtil, $total) { return round($anchoUtil * $p / $total, 2); }, $proporcion);
$w[1] += $anchoUtil - array_sum($w); // ajuste de redondeo

$cabeceraTabla = function () use ($pdf, $w) {
    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFillColorArray(PL_ETIQUETA);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->Cell($w[0] + $w[1] + $w[2] + $w[3], 5.5, 'POSGRADUANTES', 1, 0, 'C', true);
    $pdf->Cell($w[4] + $w[5], 5.5, 'CALIFICACIÓN', 1, 1, 'C', true);
    foreach (['N°', 'NOMBRE COMPLETO', 'SIGLA', 'MÓDULO', 'NUM.', 'LITERAL'] as $i => $titulo) {
        $pdf->Cell($w[$i], 5.5, $titulo, 1, $i === 5 ? 1 : 0, 'C', true);
    }
};

$altoFila = 7;
$limiteHoja = $pdf->getPageHeight() - 12;
$cabeceraTabla();

if (empty($estudiantes)) {
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->Cell(array_sum($w), 10, 'No hay estudiantes inscritos en este módulo', 1, 1, 'C');
} else {
    foreach (array_values($estudiantes) as $index => $estudiante) {
        if ($pdf->GetY() + $altoFila > $limiteHoja) {
            $pdf->AddPage();
            $cabeceraTabla(); // la cabecera se repite en cada hoja
        }
        $nombreCompleto = trim(($estudiante['Apaterno'] ?? '') . ' ' . ($estudiante['Amaterno'] ?? '') . ' ' . ($estudiante['Nombre'] ?? ''));
        $nota = $estudiante['Nota'] ?? null;
        if ($nota === null || $nota === '') {
            $notaMostrar = '-';
            $notaLiteral = '-';
        } else {
            $notaMostrar = (string)intval($nota);
            $notaLiteral = numeroALetras(intval($nota));
        }

        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->Cell($w[0], $altoFila, $index + 1, 1, 0, 'C');
        // stretch=1: si un texto es más largo que la celda se comprime en vez de salirse
        $pdf->Cell($w[1], $altoFila, $nombreCompleto, 1, 0, 'L', false, '', 1);
        $pdf->Cell($w[2], $altoFila, $sigla, 1, 0, 'C', false, '', 1);
        $pdf->Cell($w[3], $altoFila, $moduloCodigo, 1, 0, 'C', false, '', 1);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->Cell($w[4], $altoFila, $notaMostrar, 1, 0, 'C');
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->Cell($w[5], $altoFila, $notaLiteral, 1, 1, 'C', false, '', 1);
    }
}

// ---------- Pie: escala + lugar/fecha + firmas, fijo al fondo de la hoja ----------
// Si ya no entra en la hoja actual (tabla muy larga), pasa a una nueva.
$alturaPie = 58;
if ($pdf->GetY() > $pdf->getPageHeight() - 12 - $alturaPie) {
    $pdf->AddPage();
}
$yPie = $pdf->getPageHeight() - 12 - $alturaPie + 4;

// Escala en recuadro, con la nota mínima de Configuración (nunca 75/76 a mano)
$pdf->SetXY($margen, $yPie);
$pdf->SetFont('helvetica', '', 7.5);
$pdf->Cell(36, 11, 'ESCALA DE CALIFICACIÓN', 0, 0, 'L');
$xCaja = $pdf->GetX();
$pdf->SetDrawColor(0, 0, 0);
$pdf->Rect($xCaja, $yPie, 46, 11);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetXY($xCaja + 3, $yPie + 0.8);
$pdf->Cell(17, 4.7, '0  –  ' . ($notaMinima - 1), 0, 0, 'L');
$pdf->Cell(24, 4.7, 'REPROBADO', 0, 1, 'L');
$pdf->SetX($xCaja + 3);
$pdf->Cell(17, 4.7, $notaMinima . '  –  100', 0, 0, 'L');
$pdf->Cell(24, 4.7, 'APROBADO', 0, 1, 'L');

// Lugar y fecha de firma (editable, independiente de inicio/fin del módulo)
$pdf->SetXY($margen, $yPie + 15);
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell($anchoUtil, 5, 'Oruro, ' . fechaLarga($fechaGeneracion), 0, 1, 'R');

// Firmas: línea punteada, nombre y cargo debajo
$anchoFirma = ($anchoUtil - 16) / 2;
$yFirma = $pdf->getPageHeight() - 12 - 16;
$pdf->SetLineStyle(['width' => 0.25, 'dash' => '1,1', 'color' => [0, 0, 0]]);
$pdf->Line($margen, $yFirma, $margen + $anchoFirma, $yFirma);
$pdf->Line($xDer - $anchoFirma, $yFirma, $xDer, $yFirma);
$pdf->SetLineStyle(['width' => 0.2, 'dash' => 0, 'color' => [0, 0, 0]]);
foreach ([[$margen, $docenteNombre, 'DOCENTE'], [$xDer - $anchoFirma, $coordinadorNombre, InstitucionModelo::CargoFirma()]] as $firma) {
    $pdf->SetXY($firma[0], $yFirma + 1.5);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->Cell($anchoFirma, 4.5, $firma[1], 0, 2, 'C', false, '', 1);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell($anchoFirma, 4, $firma[2], 0, 0, 'C', false, '', 1);
}

// ---------- Guardar copia (historial "Planillas generadas") y mostrar ----------
$pdfContenido = $pdf->Output('', 'S');
$nombreArchivo = PlanillaGuardadaModelo::Guardar($pdfContenido, [
    'programaID' => $programaID,
    'moduloID' => $moduloID,
    'programaNombre' => $programaNombre,
    'moduloCodigo' => $moduloCodigo,
    'moduloNombre' => $moduloNombre,
    'docenteNombre' => $docenteNombre,
    'fechaInicio' => $fechaInicio,
    'fechaFin' => $fechaFin,
    'fechaGeneracion' => $fechaGeneracion,
    'totalEstudiantes' => count($estudiantes ?: []),
    'usuario' => trim(($_SESSION['Nombres'] ?? '') . ' ' . ($_SESSION['ApellidoPaterno'] ?? '')) ?: ($_SESSION['Usuario'] ?? ''),
    'tipoUsuario' => $_SESSION['Tipo'] ?? '',
]);
if ($nombreArchivo === '') {
    $nombreArchivo = 'calificaciones_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $moduloCodigo) . '.pdf';
}

// Si se requiere JSON response (para AJAX), no se envía el PDF inline
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'message' => 'PDF generado correctamente',
        'archivo' => $nombreArchivo,
        'totalEstudiantes' => count($estudiantes ?: [])
    ]);
    exit;
}

// Descartar cualquier salida accidental (warnings/notices) acumulada antes de
// enviar el PDF real, y mostrarlo inline en el navegador.
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
header('Content-Length: ' . strlen($pdfContenido));
header('Cache-Control: private, max-age=0, must-revalidate');
echo $pdfContenido;
exit;
