<?php
/**
 * Comprobante de Pago Completo del Programa - PDF (hoja carta)
 * Mismo formato que los Reportes PDF: encabezado institucional con logos (ReportesControladores),
 * tablas con cabecera azul, monto en letras y firma del Coordinador.
 */

// Hora de Bolivia (el php.ini del servidor puede traer otra zona)
date_default_timezone_set('America/La_Paz');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Tiene datos personales y de pagos: solo para el personal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['Validar']) || !in_array($_SESSION['Tipo'] ?? '', ['ADM', 'SEC', 'AUX'], true)) {
    http_response_code(403);
    die('Acceso denegado. Inicie sesión en el sistema.');
}

$baseDir = realpath(__DIR__ . '/../../../');
require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/core/conexion.modelo.php';
require_once $baseDir . '/modelos/voucher.modelo.php';
require_once $baseDir . '/modelos/autoridad.modelo.php';
require_once $baseDir . '/modelos/pagoclinica.modelo.php';
require_once $baseDir . '/modelos/institucion.modelo.php';
// Encabezado y pie de página de los Reportes PDF (logos, universidad, facultad, fecha)
require_once $baseDir . '/controladores/reportes.controlador.php';
// Monto en letras, con la misma redacción que la Orden de Pago
require_once $baseDir . '/vistas/componentes/orden-pago-pdf-render.php';

$idInscripcion = intval($_GET['idinscripcion'] ?? 0);
if ($idInscripcion <= 0) {
    die('Error: ID de inscripción no especificado');
}

try {
    $pdo = Conexion::Conectar();
    $stmt = $pdo->prepare("
        SELECT e.Nombre, e.Apaterno, e.Amaterno, e.Ci, e.Complemento, e.Exp,
               ep.idInscripcion, ep.FechaInscripcion, ep.costomatricula, ep.montoPagado,
               ep.montoDescuento, ep.porcentajeDescuento, ep.pagoCompleto, ep.nvauchermatricula,
               " . VoucherModelo::SqlFechaOrdenMatricula('ep.idInscripcion') . " AS FechaOrdenMatricula,
               (SELECT opm.NumeroOrden FROM ordenpago opm
                 WHERE opm.idinscripcion = ep.idInscripcion AND " . VoucherModelo::CONDICION_ORDEN_MATRICULA . "
                 ORDER BY opm.IdOrdenPago ASC LIMIT 1) AS NumeroOrden,
               p.NombrePrograma, p.Codigo AS CodigoPrograma, p.GradoAcademico, p.Sede, p.Version,
               p.Costo AS CostoPrograma,
               pp.CostoTotalPrograma AS PlanCosto, pp.PorcentajeDescuento AS PlanPorcentaje,
               pp.MontoDescuento AS PlanDescuento, pp.MontoTotalPagar AS PlanTotal
        FROM estudianteprograma ep
        INNER JOIN estudiante e ON ep.EstudianteID = e.EstudianteID
        INNER JOIN programa p ON ep.ProgramaID = p.ProgramaID
        LEFT JOIN pagoprograma pp ON pp.idInscripcion = ep.idInscripcion
        WHERE ep.idInscripcion = :idinscripcion
    ");
    $stmt->bindParam(':idinscripcion', $idInscripcion, PDO::PARAM_INT);
    $stmt->execute();
    $est = $stmt->fetch(PDO::FETCH_ASSOC);

    // Pagos del programa: al validar el voucher del pago completo, el sistema lo guarda como
    // la cuota 1 PAGADA en pagomodulo (en estudianteprograma queda montoPagado = 0 y
    // nvauchermatricula = 'SIN MATRÍCULA'), así que el monto y el voucher reales están aquí
    $stmtCuotas = $pdo->prepare("
        SELECT pm.NumeroCuota, pm.montocuota, pm.fechapago, pm.nvaucher, op.NumeroOrden
        FROM pagomodulo pm
        LEFT JOIN ordenpago op ON op.IdOrdenPago = pm.IdOrdenPago
        WHERE pm.idinscripcion = :idinscripcion AND pm.Estado = 'PAGADO'
        ORDER BY pm.NumeroCuota, pm.Idpagomodulo
    ");
    $stmtCuotas->execute([':idinscripcion' => $idInscripcion]);
    $cuotas = $stmtCuotas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('pdfpagocompletoprograma: ' . $e->getMessage());
    die('Error al obtener los datos del pago.');
}

if (!$est) {
    die('Error: No se encontró información del estudiante');
}
if ((int)$est['pagoCompleto'] !== 1) {
    die('Error: Este estudiante no tiene pago completo registrado');
}

// ---------------------------------------------------------------------
// Montos
// Programa: costo, descuento y total del plan pactado (pagoprograma), igual que el
// saldo de Gestión de Pagos. Sin plan: costo del programa sin descuento.
// ---------------------------------------------------------------------
$bs = fn($monto) => number_format((float)$monto, 2);
$fecha = fn($f) => $f ? date('d/m/Y', strtotime($f)) : '-';

if ($est['PlanTotal'] !== null) {
    $costoPrograma = (float)$est['PlanCosto'];
    $descuento     = (float)$est['PlanDescuento'];
    $porcentaje    = (float)$est['PlanPorcentaje'];
    $totalPrograma = (float)$est['PlanTotal'];
} else {
    $costoPrograma = (float)$est['CostoPrograma'];
    $descuento     = 0.0;
    $porcentaje    = 0.0;
    $totalPrograma = $costoPrograma;
}
if ($porcentaje <= 0 && $descuento > 0 && $costoPrograma > 0) {
    $porcentaje = round($descuento / $costoPrograma * 100, 2);
}

// Pagos realizados: matrícula (si la hubo), cuotas del programa y clínicas pagadas
$pagos = [];
$matricula = (float)$est['montoPagado'];
$voucherMatricula = trim((string)$est['nvauchermatricula']);
if ($matricula > 0 && $voucherMatricula !== '' && mb_strtoupper($voucherMatricula, 'UTF-8') !== 'SIN MATRÍCULA') {
    $pagos[] = ['Matrícula', $est['NumeroOrden'], $est['FechaOrdenMatricula'], $voucherMatricula, $matricula];
}
$pagadoPrograma = 0.0;
foreach ($cuotas as $c) {
    $pagadoPrograma += (float)$c['montocuota'];
    $concepto = count($cuotas) === 1 ? 'Pago del programa (al contado)' : 'Programa - cuota N° ' . (int)$c['NumeroCuota'];
    $pagos[] = [$concepto, $c['NumeroOrden'], $c['fechapago'], $c['nvaucher'], (float)$c['montocuota']];
}
$pagadoClinicas = 0.0;
$clinicasPendientes = 0;
foreach (array_reverse(PagoClinicaModelo::ListarPorInscripcionModelo($idInscripcion)) as $cl) {
    if ($cl['Estado'] !== 'CONFIRMADO') {
        $clinicasPendientes++;
        continue;
    }
    $pagadoClinicas += (float)$cl['MontoTotal'];
    $pagos[] = ['Clínica', $cl['NumeroOrden'], $cl['FechaPago'], $cl['NumeroVoucher'], (float)$cl['MontoTotal']];
}
$totalPagado = array_sum(array_column($pagos, 4));
$pagoMatricula = ($pagos && $pagos[0][0] === 'Matrícula') ? $matricula : 0.0;
$saldoPrograma = max(0, round($totalPrograma - $pagadoPrograma, 2));
$porcentajeTexto = rtrim(rtrim(number_format($porcentaje, 2), '0'), '.');

$nombreCompleto = mb_strtoupper(trim($est['Apaterno'] . ' ' . $est['Amaterno'] . ' ' . $est['Nombre']), 'UTF-8');
$ci = trim($est['Ci'] . (!empty($est['Complemento']) ? '-' . $est['Complemento'] : '') . ' ' . ($est['Exp'] ?? ''));
$numeroComprobante = 'PC-' . str_pad($idInscripcion, 6, '0', STR_PAD_LEFT);

// ---------------------------------------------------------------------
// Documento
// ---------------------------------------------------------------------
$pdf = new ReportesControladores('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Sistema Posgrado FCS');
$pdf->SetAuthor(InstitucionModelo::Unidad());
$pdf->SetTitle('Comprobante de Pago Completo - ' . $nombreCompleto);
$pdf->SetMargins(15, 40, 15);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();
$ancho = $pdf->getPageWidth() - 30;

$azul   = [30, 60, 114];
$texto  = [51, 51, 51];
$suave  = [234, 239, 247];
$borde  = [200, 200, 200];

// Título
$pdf->SetTextColor(...$texto);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 8, 'COMPROBANTE DE PAGO COMPLETO DEL PROGRAMA', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(0, 5, 'N° ' . $numeroComprobante, 0, 1, 'C');
$pdf->Ln(3);

// Título de sección (barra azul, igual que los programas del Estado de Cuenta)
$seccion = function ($titulo) use ($pdf, $azul, $texto) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(...$azul);
    $pdf->SetTextColor(255);
    $pdf->Cell(0, 6, $titulo, 0, 1, 'L', true);
    $pdf->SetTextColor(...$texto);
};

// Celda etiqueta + valor (etiqueta con fondo celeste, valor en blanco)
$dato = function ($etiqueta, $valor, $anchoEtiqueta, $anchoValor, $fin = false) use ($pdf, $suave, $borde) {
    $pdf->SetDrawColor(...$borde);
    $pdf->SetFillColor(...$suave);
    $pdf->SetFont('helvetica', 'B', 8.5);
    $pdf->Cell($anchoEtiqueta, 6, $etiqueta, 1, 0, 'L', true);
    $pdf->SetFont('helvetica', '', 8.5);
    $pdf->Cell($anchoValor, 6, $valor, 1, $fin ? 1 : 0, 'L', false, '', 1);
};

// 1. Datos del estudiante
$seccion('DATOS DEL ESTUDIANTE');
$dato('Estudiante:', $nombreCompleto, 32, $ancho - 32, true);
$dato('C.I.:', $ci, 32, 58);
$dato('Fecha de inscripción:', $fecha($est['FechaInscripcion']), 36, $ancho - 126, true);
$pdf->Ln(3);

// 2. Datos del programa
$seccion('DATOS DEL PROGRAMA');
$pdf->SetDrawColor(...$borde);
$pdf->SetFillColor(...$suave);
// Nombre largo: la fila crece y la etiqueta toma la misma altura
$nombrePrograma = mb_strtoupper($est['NombrePrograma'], 'UTF-8');
$pdf->SetFont('helvetica', '', 8.5);
$altoPrograma = max(6, $pdf->getStringHeight($ancho - 32, $nombrePrograma) + 2);
$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->MultiCell(32, $altoPrograma, 'Programa:', 1, 'L', true, 0, '', '', true, 0, false, true, $altoPrograma, 'M');
$pdf->SetFont('helvetica', '', 8.5);
$pdf->MultiCell($ancho - 32, $altoPrograma, $nombrePrograma, 1, 'L', false, 1, '', '', true, 0, false, true, $altoPrograma, 'M');
$dato('Código:', $est['CodigoPrograma'] ?: '-', 32, 58);
$dato('Grado académico:', mb_strtoupper($est['GradoAcademico'] ?? '-', 'UTF-8'), 36, $ancho - 126, true);
$dato('Sede:', mb_strtoupper($est['Sede'] ?: '-', 'UTF-8'), 32, 58);
$dato('Versión:', $est['Version'] ?: '-', 36, $ancho - 126, true);
$pdf->Ln(3);

// Cabecera de tabla azul: [[ancho, texto], ...]
$cabecera = function (array $cols) use ($pdf, $azul, $texto, $borde) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(42, 82, 152);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(...$azul);
    foreach ($cols as $i => $c) {
        $pdf->Cell($c[0], 6, $c[1], 1, $i === count($cols) - 1 ? 1 : 0, 'C', true, '', 1);
    }
    $pdf->SetFont('helvetica', '', 8.5);
    $pdf->SetTextColor(...$texto);
    $pdf->SetDrawColor(...$borde);
};
// Fila de total con fondo azul
$filaTotal = function ($etiqueta, $monto, $anchoEtiqueta, $anchoMonto) use ($pdf, $azul, $texto, $borde) {
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor(...$azul);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(...$azul);
    $pdf->Cell($anchoEtiqueta, 7, $etiqueta, 1, 0, 'R', true);
    $pdf->Cell($anchoMonto, 7, 'Bs. ' . number_format((float)$monto, 2), 1, 1, 'R', true);
    $pdf->SetFont('helvetica', '', 8.5);
    $pdf->SetTextColor(...$texto);
    $pdf->SetDrawColor(...$borde);
};
$rayado = function ($i) use ($pdf) {
    $v = $i % 2 === 1 ? 245 : 255;
    $pdf->SetFillColor($v, $v, $v);
};

// 3. Costo del programa (plan pactado)
$seccion('COSTO DEL PROGRAMA');
$wN = 12; $wM = 40; $wC = $ancho - $wN - $wM;
$cabecera([[$wN, 'N°'], [$wC, 'CONCEPTO'], [$wM, 'MONTO (Bs.)']]);
$filas = [['Costo del programa', $bs($costoPrograma)]];
if ($descuento > 0) {
    $filas[] = ['Descuento por pago al contado' . ($porcentaje > 0 ? ' (' . $porcentajeTexto . '%)' : ''), '- ' . $bs($descuento)];
}
foreach ($filas as $i => $f) {
    $rayado($i);
    $pdf->Cell($wN, 6, $i + 1, 1, 0, 'C', true);
    $pdf->Cell($wC, 6, $f[0], 1, 0, 'L', true);
    $pdf->Cell($wM, 6, $f[1], 1, 1, 'R', true);
}
$filaTotal('TOTAL DEL PROGRAMA', $totalPrograma, $wN + $wC, $wM);
$pdf->Ln(3);

// 4. Pagos realizados: programa, matrícula y clínicas, con su orden, fecha y voucher
$seccion('PAGOS REALIZADOS');
$wN = 9; $wO = 44; $wF = 22; $wV = 32; $wM = 28; $wC = $ancho - $wN - $wO - $wF - $wV - $wM;
$cabecera([[$wN, 'N°'], [$wC, 'CONCEPTO'], [$wO, 'N° DE ORDEN'], [$wF, 'FECHA PAGO'], [$wV, 'N° VOUCHER'], [$wM, 'MONTO (Bs.)']]);
if (!$pagos) {
    $pdf->Cell($ancho, 6, 'No hay pagos registrados', 1, 1, 'C');
}
foreach ($pagos as $i => $p) {
    $rayado($i);
    $pdf->Cell($wN, 6, $i + 1, 1, 0, 'C', true);
    $pdf->Cell($wC, 6, $p[0], 1, 0, 'L', true, '', 1);
    $pdf->Cell($wO, 6, $p[1] ?: '-', 1, 0, 'C', true, '', 1);
    $pdf->Cell($wF, 6, $fecha($p[2]), 1, 0, 'C', true);
    $pdf->Cell($wV, 6, $p[3] ?: '-', 1, 0, 'C', true, '', 1);
    $pdf->Cell($wM, 6, $bs($p[4]), 1, 1, 'R', true);
}
$filaTotal('TOTAL PAGADO', $totalPagado, $ancho - $wM, $wM);

// Monto en letras
$pdf->SetFillColor(...$suave);
$pdf->SetFont('helvetica', 'B', 8.5);
$pdf->Cell(20, 6, 'Son:', 1, 0, 'L', true);
$pdf->SetFont('helvetica', '', 8.5);
$pdf->Cell($ancho - 20, 6, numeroALetrasOrdenPagoOficial($totalPagado), 1, 1, 'L', false, '', 1);

// Resumen: lo pagado del programa, de clínicas y el estado
$estadoPrograma = $saldoPrograma <= 0.009 ? 'PROGRAMA PAGADO EN SU TOTALIDAD' : 'SALDO DEL PROGRAMA: Bs. ' . $bs($saldoPrograma);
$dato('Pagado programa:', 'Bs. ' . $bs($pagadoPrograma + $pagoMatricula), 32, 58);
$dato('Pagado clínicas:', 'Bs. ' . $bs($pagadoClinicas) . ($clinicasPendientes > 0 ? '  (' . $clinicasPendientes . ' pendiente' . ($clinicasPendientes === 1 ? '' : 's') . ')' : ''), 36, $ancho - 126, true);
$dato('Estado:', $estadoPrograma, 32, $ancho - 32, true);
$pdf->Ln(4);

// Constancia
$textoClinicas = $pagadoClinicas > 0
    ? ' Adicionalmente, registra el pago de ' . count(array_filter($pagos, fn($p) => $p[0] === 'Clínica')) . ' clínica(s) por Bs. ' . $bs($pagadoClinicas) . '.'
    : '';
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTMLCell(0, 0, '', '',
    '<p style="text-align:justify; line-height:1.5">Se deja constancia de que el(la) estudiante <b>' . htmlspecialchars($nombreCompleto)
    . '</b>, con C.I. ' . htmlspecialchars($ci) . ', '
    . ($saldoPrograma <= 0.009 ? 'canceló la totalidad del costo' : 'realizó pagos a cuenta del costo')
    . ' del programa <b>' . htmlspecialchars($nombrePrograma) . '</b>, que asciende a <b>Bs. ' . $bs($totalPrograma) . '</b> ('
    . numeroALetrasOrdenPagoOficial($totalPrograma) . ')'
    . ($descuento > 0 ? ', con el descuento por pago al contado ya aplicado' : '') . '.'
    . htmlspecialchars($textoClinicas)
    . ' El presente comprobante se emite para los fines que al interesado convengan.</p>',
    0, 1);
$pdf->Ln(3);
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$pdf->Cell(0, 5, InstitucionModelo::Ciudad() . ', ' . date('j') . ' de ' . $meses[date('n') - 1] . ' de ' . date('Y'), 0, 1, 'R');

// Firma del Coordinador (se edita en Configuración > Autoridades)
// Bloque de firma: 14 mm de espacio + línea + 3 renglones ≈ 29 mm
if ($pdf->GetY() + 29 > $pdf->getPageHeight() - 25) {
    $pdf->AddPage();
}
$pdf->Ln(14);
$autoridades = AutoridadModelo::ListarModelo();
$xFirma = ($pdf->getPageWidth() - 80) / 2;
$pdf->SetDrawColor(80, 80, 80);
$pdf->SetLineWidth(0.3);
$pdf->Line($xFirma, $pdf->GetY(), $xFirma + 80, $pdf->GetY());
$pdf->Ln(1);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(0, 5, AutoridadModelo::NombreParaDocumento($autoridades, 'COORDINADOR'), 0, 1, 'C');
$pdf->SetFont('helvetica', '', 8.5);
$pdf->Cell(0, 4, InstitucionModelo::CargoFirma(), 0, 1, 'C');
$pdf->Cell(0, 4, InstitucionModelo::Facultad(), 0, 1, 'C');

$pdf->Output('Comprobante_Pago_Completo_' . $idInscripcion . '_' . date('Ymd') . '.pdf', 'I');
